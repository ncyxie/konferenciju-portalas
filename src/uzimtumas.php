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

$sql = "SELECT id, pavadinimas FROM patalpa ORDER BY pavadinimas";
$uzklausa = $pdo->query($sql);
$visosPatalpos = $uzklausa->fetchAll();

// Numatytosios reikšmės: visos patalpos, šiandienos savaitė.
$patalposId = 0;
$data = date("Y-m-d");

if (isset($_GET["patalpa"]))
{
    $patalposId = (int) $_GET["patalpa"];
}

// Tik tekstas, kuris yra tinkama data.
if (isset($_GET["data"]) && is_string($_GET["data"]) && strtotime($_GET["data"]) !== false)
{
    $data = $_GET["data"];
}

// Savaitės pirmadienis ir sekmadienis.
$pirmadienis = date("Y-m-d", strtotime("monday this week", strtotime($data)));
$sekmadienis = date("Y-m-d", strtotime($pirmadienis . " +6 days"));

// Ankstesnės ir kitos savaitės pirmadieniai nuorodoms.
$ankstesnePirmadienis = date("Y-m-d", strtotime($pirmadienis . " -7 days"));
$kitaPirmadienis = date("Y-m-d", strtotime($pirmadienis . " +7 days"));

// Septynios savaitės dienos.
$dienos = [];

for ($i = 0; $i < 7; $i = $i + 1)
{
    array_push($dienos, date("Y-m-d", strtotime($pirmadienis . " +" . $i . " days")));
}

$dienuPavadinimai = ["Pr", "An", "Tr", "Kt", "Pn", "Š", "S"];

// Patvirtintos tos savaitės rezervacijos.
if ($patalposId === 0)
{
    $sql = "SELECT patalpa.pavadinimas, rezervacija.data,
                   rezervacija.pradzios_laikas, rezervacija.pabaigos_laikas
            FROM rezervacija
            JOIN patalpa ON patalpa.id = rezervacija.fk_rezervacija_patalpa
            WHERE rezervacija.busena = 'patvirtinta'
            AND rezervacija.data >= :nuo
            AND rezervacija.data <= :iki
            ORDER BY rezervacija.pradzios_laikas, patalpa.pavadinimas";

    $uzklausa = $pdo->prepare($sql);
    $uzklausa->execute([
        ":nuo" => $pirmadienis,
        ":iki" => $sekmadienis
    ]);
}
else
{
    $sql = "SELECT patalpa.pavadinimas, rezervacija.data,
                   rezervacija.pradzios_laikas, rezervacija.pabaigos_laikas
            FROM rezervacija
            JOIN patalpa ON patalpa.id = rezervacija.fk_rezervacija_patalpa
            WHERE rezervacija.busena = 'patvirtinta'
            AND rezervacija.data >= :nuo
            AND rezervacija.data <= :iki
            AND rezervacija.fk_rezervacija_patalpa = :patalpa
            ORDER BY rezervacija.pradzios_laikas";

    $uzklausa = $pdo->prepare($sql);
    $uzklausa->execute([
        ":nuo" => $pirmadienis,
        ":iki" => $sekmadienis,
        ":patalpa" => $patalposId
    ]);
}

$rezervacijos = $uzklausa->fetchAll();

$puslapioPavadinimas = "Patalpų užimtumas";
require "antraste.php";
?>

    <h2>Patalpų užimtumo tvarkaraštis</h2>

    <form method="get" action="uzimtumas.php" class="paieskos-forma">
        <p>
            <label for="patalpa">Patalpa:</label>
            <select id="patalpa" name="patalpa">
                <option value="0">Visos patalpos</option>
                <?php
                foreach ($visosPatalpos as $patalpa)
                {
                    // Pažymi pasirinktą.
                    $pazymeta = "";

                    if ($patalpa["id"] == $patalposId)
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

        <p>
            <label for="data">Savaitė (bet kuri jos diena):</label>
            <input type="date" id="data" name="data" required
                   value="<?php echo htmlspecialchars($data); ?>">
        </p>

        <div class="formos-veiksmai">
            <button type="submit">Rodyti</button>
            <a href="uzimtumas.php?patalpa=<?php echo $patalposId; ?>&data=<?php echo $ankstesnePirmadienis; ?>">← Ankstesnė savaitė</a>
            <a href="uzimtumas.php?patalpa=<?php echo $patalposId; ?>&data=<?php echo $kitaPirmadienis; ?>">Kita savaitė →</a>
        </div>
    </form>

    <h3><?php echo $pirmadienis . " – " . $sekmadienis; ?></h3>

    <?php
    if (count($rezervacijos) === 0)
    {
        echo "<p>Šią savaitę patvirtintų rezervacijų nėra.</p>";
    }

    echo '<div class="lentele">';
    echo '<table class="tvarkarastis">';

    // Antraštė: dienos su datomis.
    echo "<tr>";
    echo "<th>Laikas</th>";

    for ($i = 0; $i < 7; $i = $i + 1)
    {
        // Rodo tik mėnesį ir dieną.
        echo "<th>" . $dienuPavadinimai[$i] . "<br>" . substr($dienos[$i], 5) . "</th>";
    }

    echo "</tr>";

    // Viena eilutė kiekvienai valandai.
    for ($valanda = 0; $valanda < 24; $valanda = $valanda + 1)
    {
        // Valandos pradžia ir pabaiga palyginimui su rezervacijų laikais.
        $valandosPradzia = dviSkaitmenys($valanda) . ":00:00";
        $valandosPabaiga = dviSkaitmenys($valanda + 1) . ":00:00";

        echo "<tr>";
        echo "<th>" . dviSkaitmenys($valanda) . ":00</th>";

        foreach ($dienos as $diena)
        {
            // Surenka rezervacijas, kurios persidengia su šia valanda.
            $langelioTekstas = "";

            foreach ($rezervacijos as $rezervacija)
            {
                if ($rezervacija["data"] === $diena
                    && $rezervacija["pradzios_laikas"] < $valandosPabaiga
                    && $rezervacija["pabaigos_laikas"] > $valandosPradzia)
                {
                    $pradziosLaikas = substr($rezervacija["pradzios_laikas"], 0, 5);
                    $pabaigosLaikas = substr($rezervacija["pabaigos_laikas"], 0, 5);

                    // Kiekviena rezervacija – atskirame bloke.
                    $langelioTekstas = $langelioTekstas . "<div>";

                    // Kai rodomos visos patalpos, viršuje rašo pavadinimą.
                    if ($patalposId === 0)
                    {
                        $langelioTekstas = $langelioTekstas . "<strong>" . htmlspecialchars($rezervacija["pavadinimas"]) . "</strong><br>";
                    }

                    $langelioTekstas = $langelioTekstas . $pradziosLaikas . "–" . $pabaigosLaikas . "</div>";
                }
            }

            if ($langelioTekstas === "")
            {
                echo "<td></td>";
            }
            else
            {
                echo '<td class="uzimta">' . $langelioTekstas . '</td>';
            }
        }

        echo "</tr>";
    }

    echo "</table>";
    echo "</div>";
    ?>
</body>
</html>
