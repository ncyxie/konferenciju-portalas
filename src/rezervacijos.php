<?php
session_start();
require "db.php";
require "funkcijos.php";

// Tik administratoriui.
if (!isset($_SESSION["naudotojo_id"]))
{
    header("Location: prisijungti.php");
    exit;
}

if ($_SESSION["role"] !== "administratorius")
{
    echo "Neturite teisės atidaryti šio puslapio.";
    exit;
}

$pranesimas = "";
$klaida = "";

// Paspaustas „Patvirtinti“ arba „Atmesti“.
if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $rezervacijosId = 0;
    $veiksmas = "";

    if (isset($_POST["rezervacijos_id"]))
    {
        $rezervacijosId = (int) $_POST["rezervacijos_id"];
    }

    if (isset($_POST["veiksmas"]))
    {
        $veiksmas = $_POST["veiksmas"];
    }

    // Transakcija: commit() išsaugo, rollBack() atšaukia.
    $pdo->beginTransaction();

    // Užrakina prašymą iki transakcijos pabaigos.
    $sql = "SELECT fk_rezervacija_patalpa, data, pradzios_laikas, pabaigos_laikas, klausytoju_kiekis
            FROM rezervacija
            WHERE id = :id
            AND busena = 'laukiama'
            FOR UPDATE";

    $uzklausa = $pdo->prepare($sql);
    $uzklausa->execute([":id" => $rezervacijosId]);

    // false – nerasta.
    $rezervacija = $uzklausa->fetch();

    if ($rezervacija === false)
    {
        $pdo->rollBack();

        $klaida = "Prašymas nerastas arba jau išnagrinėtas.";
    }
    else if ($veiksmas === "atmesti")
    {
        $sql = "UPDATE rezervacija SET busena = 'atmesta' WHERE id = :id";

        $uzklausa = $pdo->prepare($sql);
        $uzklausa->execute([":id" => $rezervacijosId]);

        $pdo->commit();

        $pranesimas = "Rezervacijos prašymas atmestas.";
    }
    else if ($veiksmas === "patvirtinti")
    {
        // Užrakina ir patalpą.
        $sql = "SELECT talpa, aktyvi FROM patalpa WHERE id = :id FOR UPDATE";

        $uzklausa = $pdo->prepare($sql);
        $uzklausa->execute([":id" => $rezervacija["fk_rezervacija_patalpa"]]);
        $patalpa = $uzklausa->fetch();

        $uzimta = patalpaUzimta(
            $pdo,
            $rezervacija["fk_rezervacija_patalpa"],
            $rezervacija["data"],
            $rezervacija["pradzios_laikas"],
            $rezervacija["pabaigos_laikas"]
        );

        $prasymoPradzia = strtotime($rezervacija["data"] . " " . $rezervacija["pradzios_laikas"]);

        // Tikrina iš naujo.
        if ($prasymoPradzia <= time())
        {
            $pdo->rollBack();

            $klaida = "Prašymo laikas jau praėjo. Tokį prašymą galima tik atmesti.";
        }
        else if ($patalpa["aktyvi"] == 0)
        {
            $pdo->rollBack();

            $klaida = "Patalpa išjungta. Prašymo patvirtinti negalima.";
        }
        else if ($patalpa["talpa"] < $rezervacija["klausytoju_kiekis"])
        {
            $pdo->rollBack();

            $klaida = "Patalpa per maža prašomam klausytojų skaičiui. Prašymo patvirtinti negalima.";
        }
        else if ($uzimta)
        {
            $pdo->rollBack();

            $klaida = "Patalpa šiuo laiku jau užimta. Prašymo patvirtinti negalima.";
        }
        else
        {
            $sql = "UPDATE rezervacija SET busena = 'patvirtinta' WHERE id = :id";

            $uzklausa = $pdo->prepare($sql);
            $uzklausa->execute([":id" => $rezervacijosId]);

            $pdo->commit();

            $pranesimas = "Rezervacijos prašymas patvirtintas.";
        }
    }
    else
    {
        $pdo->rollBack();

        $klaida = "Neteisingas veiksmas.";
    }

    // Pranešimai – per sesiją.
    $_SESSION["pranesimas"] = $pranesimas;
    $_SESSION["klaida"] = $klaida;

    // Nukreipia, kad F5 nepakartotų veiksmo.
    header("Location: rezervacijos.php");
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

// Su patalpos pavadinimu ir organizatoriaus vardu.
$sql = "SELECT rezervacija.id, rezervacija.data, rezervacija.pradzios_laikas,
               rezervacija.pabaigos_laikas, rezervacija.klausytoju_kiekis,
               patalpa.pavadinimas, naudotojas.vardas, naudotojas.pavarde
        FROM rezervacija
        JOIN patalpa ON patalpa.id = rezervacija.fk_rezervacija_patalpa
        JOIN naudotojas ON naudotojas.id = rezervacija.fk_rezervacija_naudotojas
        WHERE rezervacija.busena = 'laukiama'
        ORDER BY rezervacija.data, rezervacija.pradzios_laikas";

$uzklausa = $pdo->query($sql);
$prasymai = $uzklausa->fetchAll();

$puslapioPavadinimas = "Rezervacijų prašymai";
require "antraste.php";
?>

    <h2>Laukiantys rezervacijų prašymai</h2>

    <?php
    if ($pranesimas !== "")
    {
        echo '<p class="sekme">' . htmlspecialchars($pranesimas) . '</p>';
    }

    if ($klaida !== "")
    {
        echo '<p class="klaida">' . htmlspecialchars($klaida) . '</p>';
    }

    if (count($prasymai) === 0)
    {
        echo "<p>Laukiančių prašymų nėra.</p>";
    }
    else
    {
        echo "<table>";
        echo "<tr>";
        echo "<th>Organizatorius</th>";
        echo "<th>Patalpa</th>";
        echo "<th>Data</th>";
        echo "<th>Laikas</th>";
        echo "<th>Klausytojų</th>";
        echo "<th>Veiksmai</th>";
        echo "</tr>";

        foreach ($prasymai as $prasymas)
        {
            $organizatorius = $prasymas["vardas"] . " " . $prasymas["pavarde"];

            // 10:00:00 → 10:00
            $pradziosLaikas = substr($prasymas["pradzios_laikas"], 0, 5);
            $pabaigosLaikas = substr($prasymas["pabaigos_laikas"], 0, 5);

            echo "<tr>";
            echo "<td>" . htmlspecialchars($organizatorius) . "</td>";
            echo "<td>" . htmlspecialchars($prasymas["pavadinimas"]) . "</td>";
            echo "<td>" . $prasymas["data"] . "</td>";
            echo "<td>" . $pradziosLaikas . "–" . $pabaigosLaikas . "</td>";
            echo "<td>" . $prasymas["klausytoju_kiekis"] . "</td>";
            echo "<td>";
            echo '<form method="post" action="rezervacijos.php">';
            echo '<input type="hidden" name="rezervacijos_id" value="' . $prasymas["id"] . '">';
            echo '<button type="submit" name="veiksmas" value="patvirtinti">Patvirtinti</button> ';
            echo '<button type="submit" name="veiksmas" value="atmesti">Atmesti</button>';
            echo '</form>';
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }
    ?>
</body>
</html>
