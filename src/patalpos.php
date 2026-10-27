<?php
session_start();
require "db.php";
require "funkcijos.php";

// Tik organizatoriui ir administratoriui.
if (!isset($_SESSION["naudotojo_id"]))
{
    header("Location: prisijungti.php");
    exit;
}

if ($_SESSION["role"] !== "organizatorius" && $_SESSION["role"] !== "administratorius")
{
    echo "Neturite teisės atidaryti šio puslapio.";
    exit;
}

$sql = "SELECT id, pavadinimas FROM iranga ORDER BY pavadinimas";
$uzklausa = $pdo->query($sql);
$irangos = $uzklausa->fetchAll();

$sql = "SELECT id, pavadinimas, talpa FROM patalpa WHERE aktyvi = 1 ORDER BY pavadinimas";
$uzklausa = $pdo->query($sql);
$visosPatalpos = $uzklausa->fetchAll();

$data = "";
$pradzia = "";
$pabaiga = "";
$kiekis = "";
$irangosId = 0;          // 0 – įranga nesvarbi
$pradinePatalpaId = 0;   // 0 – artumas nesvarbus

// Tik tekstas.
if (isset($_GET["data"]) && is_string($_GET["data"]))
{
    $data = $_GET["data"];
}

if (isset($_GET["pradzia"]) && is_string($_GET["pradzia"]))
{
    $pradzia = $_GET["pradzia"];
}

if (isset($_GET["pabaiga"]) && is_string($_GET["pabaiga"]))
{
    $pabaiga = $_GET["pabaiga"];
}

if (isset($_GET["kiekis"]) && is_string($_GET["kiekis"]))
{
    $kiekis = $_GET["kiekis"];
}

if (isset($_GET["iranga"]))
{
    $irangosId = (int) $_GET["iranga"];
}

if (isset($_GET["pradine_patalpa"]))
{
    $pradinePatalpaId = (int) $_GET["pradine_patalpa"];
}

$ieskota = isset($_GET["ieskoti"]);
$klaida = "";

// Prieš paiešką – visos patalpos.
$patalpos = $visosPatalpos;

if ($ieskota)
{
    $klaida = tikrintiPaieskosDuomenis($data, $pradzia, $pabaiga, $kiekis);
}

if ($ieskota && $klaida === "")
{
    // 1. Patalpos pagal talpą.
    if ($pradinePatalpaId === 0)
    {
        $sql = "SELECT id, pavadinimas, talpa
                FROM patalpa
                WHERE aktyvi = 1
                AND talpa >= :kiekis
                ORDER BY talpa, pavadinimas";

        $uzklausa = $pdo->prepare($sql);
        $uzklausa->execute([":kiekis" => $kiekis]);
    }
    else
    {
        // Pagal atstumą nuo pradinės patalpos.
        // Pora saugoma vieną kartą – tikrinamos abi kryptys.
        $sql = "SELECT patalpa.id, patalpa.pavadinimas, patalpa.talpa, patalpu_atstumas.atstumas
                FROM patalpu_atstumas
                JOIN patalpa ON
                    (patalpu_atstumas.fk_atstumas_patalpa1 = :pradine1 AND patalpu_atstumas.fk_atstumas_patalpa2 = patalpa.id)
                    OR
                    (patalpu_atstumas.fk_atstumas_patalpa2 = :pradine2 AND patalpu_atstumas.fk_atstumas_patalpa1 = patalpa.id)
                WHERE patalpa.aktyvi = 1
                AND patalpa.talpa >= :kiekis
                ORDER BY patalpu_atstumas.atstumas";

        $uzklausa = $pdo->prepare($sql);
        $uzklausa->execute([
            ":pradine1" => $pradinePatalpaId,
            ":pradine2" => $pradinePatalpaId,
            ":kiekis" => $kiekis
        ]);
    }

    $patalposPagalTalpa = $uzklausa->fetchAll();

    // 2. Atmeta užimtas ir be reikiamos įrangos.
    $patalpos = [];

    foreach ($patalposPagalTalpa as $patalpa)
    {
        $tinka = true;

        if (patalpaUzimta($pdo, $patalpa["id"], $data, $pradzia, $pabaiga))
        {
            $tinka = false;
        }

        if ($irangosId !== 0 && !patalpaTuriIranga($pdo, $patalpa["id"], $irangosId))
        {
            $tinka = false;
        }

        if ($tinka)
        {
            array_push($patalpos, $patalpa);
        }
    }
}

$rodytiAtstuma = false;

if ($ieskota && $pradinePatalpaId !== 0)
{
    $rodytiAtstuma = true;
}

// Rezervuoja tik organizatorius.
$galiRezervuoti = false;

if ($ieskota && $_SESSION["role"] === "organizatorius")
{
    $galiRezervuoti = true;
}

$puslapioPavadinimas = "Patalpos";
require "antraste.php";
?>

    <h2>Patalpų paieška</h2>

    <form method="get" action="patalpos.php" class="paieskos-forma">
        <p>
            <label for="data">Data:</label>
            <input type="date" id="data" name="data" required
                   value="<?php echo htmlspecialchars($data); ?>">
        </p>

        <p>
            <label for="kiekis">Klausytojų skaičius:</label>
            <input type="number" id="kiekis" name="kiekis" min="1" required
                   value="<?php echo htmlspecialchars($kiekis); ?>">
        </p>

        <p>
            <label for="pradzia">Pradžios laikas:</label>
            <input type="time" id="pradzia" name="pradzia" required
                   value="<?php echo htmlspecialchars($pradzia); ?>">
        </p>

        <p>
            <label for="pabaiga">Pabaigos laikas:</label>
            <input type="time" id="pabaiga" name="pabaiga" required
                   value="<?php echo htmlspecialchars($pabaiga); ?>">
        </p>

        <p>
            <label for="iranga">Reikalinga įranga:</label>
            <select id="iranga" name="iranga">
                <option value="0">Nesvarbu</option>
                <?php
                foreach ($irangos as $iranga)
                {
                    // Pažymi pasirinktą.
                    $pazymeta = "";

                    if ($iranga["id"] == $irangosId)
                    {
                        $pazymeta = "selected";
                    }

                    echo '<option value="' . $iranga["id"] . '" ' . $pazymeta . '>';
                    echo htmlspecialchars($iranga["pavadinimas"]);
                    echo '</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <label for="pradine_patalpa">Artimiausios prie patalpos (neprivaloma):</label>
            <select id="pradine_patalpa" name="pradine_patalpa">
                <option value="0">Nesvarbu</option>
                <?php
                foreach ($visosPatalpos as $patalpa)
                {
                    $pazymeta = "";

                    if ($patalpa["id"] == $pradinePatalpaId)
                    {
                        $pazymeta = "selected";
                    }

                    echo '<option value="' . $patalpa["id"] . '" ' . $pazymeta . '>';
                    echo htmlspecialchars($patalpa["pavadinimas"]);
                    echo '</option>';
                }
                ?>
            </select>
        </p>

        <div class="formos-veiksmai">
            <button type="submit" name="ieskoti" value="1">Ieškoti</button>
            <a href="patalpos.php">Išvalyti paiešką</a>
        </div>
    </form>

    <?php
    if ($ieskota)
    {
        echo "<h2>Paieškos rezultatai</h2>";
    }
    else
    {
        echo "<h2>Patalpų sąrašas</h2>";
    }

    if ($klaida !== "")
    {
        echo '<p class="klaida">' . htmlspecialchars($klaida) . '</p>';
    }
    else if (count($patalpos) === 0)
    {
        echo "<p>Tinkamų patalpų nerasta.</p>";
    }
    else
    {
        echo "<table>";
        echo "<tr>";
        echo "<th>Patalpa</th>";
        echo "<th>Talpa</th>";

        if ($rodytiAtstuma)
        {
            echo "<th>Sąlyginis atstumas</th>";
        }

        if ($galiRezervuoti)
        {
            echo "<th>Veiksmas</th>";
        }

        echo "</tr>";

        foreach ($patalpos as $patalpa)
        {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($patalpa["pavadinimas"]) . "</td>";
            echo "<td>" . $patalpa["talpa"] . "</td>";

            if ($rodytiAtstuma)
            {
                echo "<td>" . $patalpa["atstumas"] . "</td>";
            }

            if ($galiRezervuoti)
            {
                // Paieškos duomenys – paslėptuose laukuose.
                echo "<td>";
                echo '<form method="post" action="rezervuoti.php">';
                echo '<input type="hidden" name="patalpos_id" value="' . $patalpa["id"] . '">';
                echo '<input type="hidden" name="data" value="' . htmlspecialchars($data) . '">';
                echo '<input type="hidden" name="pradzia" value="' . htmlspecialchars($pradzia) . '">';
                echo '<input type="hidden" name="pabaiga" value="' . htmlspecialchars($pabaiga) . '">';
                echo '<input type="hidden" name="kiekis" value="' . htmlspecialchars($kiekis) . '">';
                echo '<input type="hidden" name="iranga" value="' . $irangosId . '">';
                echo '<button type="submit">Pateikti prašymą</button>';
                echo '</form>';
                echo "</td>";
            }

            echo "</tr>";
        }

        echo "</table>";
    }
    ?>
</body>
</html>
