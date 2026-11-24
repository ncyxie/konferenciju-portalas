<?php
session_start();
require "db.php";
require "funkcijos.php";

// Tik registruotam naudotojui (dalyviui).
if (!isset($_SESSION["naudotojo_id"]))
{
    header("Location: prisijungti.php");
    exit;
}

if ($_SESSION["role"] !== "registruotas")
{
    echo "Neturite teisės atidaryti šio puslapio.";
    exit;
}

$pranesimas = "";
$klaida = "";
$siandien = date("Y-m-d");

// Paspaustas „Registruotis“.
if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $sekcijosId = 0;

    if (isset($_POST["sekcijos_id"]))
    {
        $sekcijosId = (int) $_POST["sekcijos_id"];
    }

    $pdo->beginTransaction();

    // Užrakina sekciją, kad du dalyviai vienu metu neužimtų paskutinės vietos.
    $sql = "SELECT patalpa.talpa
            FROM sekcija
            JOIN rezervacija ON rezervacija.fk_rezervacija_sekcija = sekcija.id
            JOIN patalpa ON patalpa.id = rezervacija.fk_rezervacija_patalpa
            WHERE sekcija.id = :id
            AND rezervacija.busena = 'patvirtinta'
            AND rezervacija.data >= :siandien
            FOR UPDATE";

    $uzklausa = $pdo->prepare($sql);
    $uzklausa->execute([
        ":id" => $sekcijosId,
        ":siandien" => $siandien
    ]);

    // false – nerasta.
    $sekcija = $uzklausa->fetch();

    if ($sekcija === false)
    {
        $pdo->rollBack();

        $klaida = "Sekcija nerasta arba registracija į ją nebevyksta.";
    }
    else if (naudotojasUzsiregistraves($pdo, $_SESSION["naudotojo_id"], $sekcijosId))
    {
        $pdo->rollBack();

        $klaida = "Į šią sekciją jau esate užsiregistravę.";
    }
    else if (uzregistruotuSkaicius($pdo, $sekcijosId) >= $sekcija["talpa"])
    {
        $pdo->rollBack();

        $klaida = "Sekcijoje nebėra laisvų vietų.";
    }
    else
    {
        $sql = "INSERT INTO dalyvio_registracija (registracijos_laikas, fk_registracija_naudotojas, fk_registracija_sekcija)
                VALUES (:laikas, :naudotojas, :sekcija)";

        $uzklausa = $pdo->prepare($sql);
        $uzklausa->execute([
            ":laikas" => date("Y-m-d H:i:s"),
            ":naudotojas" => $_SESSION["naudotojo_id"],
            ":sekcija" => $sekcijosId
        ]);

        $pdo->commit();

        $pranesimas = "Sėkmingai užsiregistravote į sekciją.";
    }

    // Pranešimai – per sesiją.
    $_SESSION["pranesimas"] = $pranesimas;
    $_SESSION["klaida"] = $klaida;

    // Nukreipia, kad F5 nepakartotų veiksmo.
    header("Location: sekcijos.php");
    exit;
}

// Parodo pranešimą vieną kartą.
if (isset($_SESSION["pranesimas"]))
{
    $pranesimas = $_SESSION["pranesimas"];
    unset($_SESSION["pranesimas"]);
}

if (isset($_SESSION["klaida"]))
{
    $klaida = $_SESSION["klaida"];
    unset($_SESSION["klaida"]);
}

// Tik sekcijos su patvirtinta, dar nepraėjusia patalpos rezervacija.
$sql = "SELECT sekcija.id, sekcija.pavadinimas, konferencija.pavadinimas AS konferencijos_pavadinimas,
               patalpa.pavadinimas AS patalpos_pavadinimas, patalpa.talpa,
               rezervacija.data, rezervacija.pradzios_laikas, rezervacija.pabaigos_laikas
        FROM sekcija
        JOIN konferencija ON konferencija.id = sekcija.fk_sekcija_konferencija
        JOIN rezervacija ON rezervacija.fk_rezervacija_sekcija = sekcija.id
        JOIN patalpa ON patalpa.id = rezervacija.fk_rezervacija_patalpa
        WHERE rezervacija.busena = 'patvirtinta'
        AND rezervacija.data >= :siandien
        ORDER BY rezervacija.data, rezervacija.pradzios_laikas";

$uzklausa = $pdo->prepare($sql);
$uzklausa->execute([":siandien" => $siandien]);
$sekcijos = $uzklausa->fetchAll();

$puslapioPavadinimas = "Konferencijų sekcijos";
require "antraste.php";
?>

    <h2>Konferencijų sekcijos</h2>

    <?php
    if ($pranesimas !== "")
    {
        echo '<p class="sekme">' . htmlspecialchars($pranesimas) . '</p>';
    }

    if ($klaida !== "")
    {
        echo '<p class="klaida">' . htmlspecialchars($klaida) . '</p>';
    }

    if (count($sekcijos) === 0)
    {
        echo "<p>Sekcijų, į kurias galima registruotis, nėra.</p>";
    }
    else
    {
        echo "<table>";
        echo "<tr>";
        echo "<th>Konferencija</th>";
        echo "<th>Sekcija</th>";
        echo "<th>Patalpa</th>";
        echo "<th>Data</th>";
        echo "<th>Laikas</th>";
        echo "<th>Laisvų vietų</th>";
        echo "<th>Registracija</th>";
        echo "</tr>";

        foreach ($sekcijos as $sekcija)
        {
            $laisvos = $sekcija["talpa"] - uzregistruotuSkaicius($pdo, $sekcija["id"]);

            // 10:00:00 → 10:00
            $pradziosLaikas = substr($sekcija["pradzios_laikas"], 0, 5);
            $pabaigosLaikas = substr($sekcija["pabaigos_laikas"], 0, 5);

            echo "<tr>";
            echo "<td>" . htmlspecialchars($sekcija["konferencijos_pavadinimas"]) . "</td>";
            echo "<td>" . htmlspecialchars($sekcija["pavadinimas"]) . "</td>";
            echo "<td>" . htmlspecialchars($sekcija["patalpos_pavadinimas"]) . "</td>";
            echo '<td class="nelauzyti">' . $sekcija["data"] . "</td>";
            echo '<td class="nelauzyti">' . $pradziosLaikas . "–" . $pabaigosLaikas . "</td>";
            echo '<td class="nelauzyti">' . $laisvos . " iš " . $sekcija["talpa"] . "</td>";
            echo "<td>";

            // Mygtukas rodomas tik tada, kai registruotis galima.
            if (naudotojasUzsiregistraves($pdo, $_SESSION["naudotojo_id"], $sekcija["id"]))
            {
                echo "Užsiregistravote";
            }
            else if ($laisvos <= 0)
            {
                echo "Vietų nėra";
            }
            else
            {
                echo '<form method="post" action="sekcijos.php">';
                echo '<input type="hidden" name="sekcijos_id" value="' . $sekcija["id"] . '">';
                echo '<button type="submit">Registruotis</button>';
                echo '</form>';
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }
    ?>
</body>
</html>
