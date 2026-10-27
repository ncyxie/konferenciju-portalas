<?php
session_start();
require "db.php";
require "funkcijos.php";

// Tik organizatoriui.
if (!isset($_SESSION["naudotojo_id"]))
{
    header("Location: prisijungti.php");
    exit;
}

if ($_SESSION["role"] !== "organizatorius")
{
    echo "Neturite teisės pateikti rezervacijos prašymo.";
    exit;
}

$klaida = "";

// Paspaustas „Pateikti prašymą“.
if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $patalposId = 0;
    $data = "";
    $pradzia = "";
    $pabaiga = "";
    $kiekis = "";
    $irangosId = 0;   // 0 – įranga nesvarbi

    if (isset($_POST["patalpos_id"]))
    {
        $patalposId = (int) $_POST["patalpos_id"];
    }

    // Tik tekstas.
    if (isset($_POST["data"]) && is_string($_POST["data"]))
    {
        $data = $_POST["data"];
    }

    if (isset($_POST["pradzia"]) && is_string($_POST["pradzia"]))
    {
        $pradzia = $_POST["pradzia"];
    }

    if (isset($_POST["pabaiga"]) && is_string($_POST["pabaiga"]))
    {
        $pabaiga = $_POST["pabaiga"];
    }

    if (isset($_POST["kiekis"]) && is_string($_POST["kiekis"]))
    {
        $kiekis = $_POST["kiekis"];
    }

    if (isset($_POST["iranga"]))
    {
        $irangosId = (int) $_POST["iranga"];
    }

    // Tikrina iš naujo.
    $klaida = tikrintiPaieskosDuomenis($data, $pradzia, $pabaiga, $kiekis);

    if ($klaida === "")
    {
        $sql = "SELECT talpa FROM patalpa WHERE id = :id AND aktyvi = 1";

        $uzklausa = $pdo->prepare($sql);
        $uzklausa->execute([":id" => $patalposId]);

        // false – nerasta.
        $patalpa = $uzklausa->fetch();

        if ($patalpa === false)
        {
            $klaida = "Patalpa nerasta.";
        }
        else if ($patalpa["talpa"] < $kiekis)
        {
            $klaida = "Patalpa per maža nurodytam klausytojų skaičiui.";
        }
        else if ($irangosId !== 0 && !patalpaTuriIranga($pdo, $patalposId, $irangosId))
        {
            $klaida = "Patalpa neturi reikalingos įrangos.";
        }
        else if (patalpaUzimta($pdo, $patalposId, $data, $pradzia, $pabaiga))
        {
            $klaida = "Patalpa šiuo laiku jau užimta.";
        }
    }

    if ($klaida === "")
    {
        $sql = "INSERT INTO rezervacija
                (data, pradzios_laikas, pabaigos_laikas, klausytoju_kiekis, busena,
                 fk_rezervacija_naudotojas, fk_rezervacija_patalpa)
                VALUES
                (:data, :pradzia, :pabaiga, :kiekis, 'laukiama', :naudotojas, :patalpa)";

        $uzklausa = $pdo->prepare($sql);
        $uzklausa->execute([
            ":data" => $data,
            ":pradzia" => $pradzia,
            ":pabaiga" => $pabaiga,
            ":kiekis" => $kiekis,
            ":naudotojas" => $_SESSION["naudotojo_id"],
            ":patalpa" => $patalposId
        ]);

        // Žymė sėkmės pranešimui.
        $_SESSION["prasymas_pateiktas"] = true;

        // Nukreipia, kad F5 nepakartotų veiksmo.
        header("Location: rezervuoti.php");
        exit;
    }
}
else
{
    // Be žymės – atgal į paiešką.
    if (!isset($_SESSION["prasymas_pateiktas"]))
    {
        header("Location: patalpos.php");
        exit;
    }

    // Parodo vieną kartą.
    unset($_SESSION["prasymas_pateiktas"]);
}

$puslapioPavadinimas = "Rezervacijos prašymas";
require "antraste.php";
?>

    <h2>Rezervacijos prašymas</h2>

    <?php
    if ($klaida !== "")
    {
        echo '<p class="klaida">' . htmlspecialchars($klaida) . '</p>';
    }
    else
    {
        echo '<p class="sekme">Rezervacijos prašymas pateiktas. Laukiama administratoriaus patvirtinimo.</p>';
    }
    ?>

    <p><a href="patalpos.php">Grįžti į patalpų paiešką</a></p>
</body>
</html>
