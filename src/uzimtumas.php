<?php
session_start();
require "db.php";

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

// Patvirtintos šiandienos ir vėlesnės rezervacijos.
$sql = "SELECT patalpa.pavadinimas, rezervacija.data,
               rezervacija.pradzios_laikas, rezervacija.pabaigos_laikas
        FROM rezervacija
        JOIN patalpa ON patalpa.id = rezervacija.fk_rezervacija_patalpa
        WHERE rezervacija.busena = 'patvirtinta'
        AND rezervacija.data >= :siandien
        ORDER BY rezervacija.data, rezervacija.pradzios_laikas, patalpa.pavadinimas";

// Šiandiena pagal PHP laiką.
$uzklausa = $pdo->prepare($sql);
$uzklausa->execute([":siandien" => date("Y-m-d")]);
$rezervacijos = $uzklausa->fetchAll();

$puslapioPavadinimas = "Patalpų užimtumas";
require "antraste.php";
?>

    <h2>Patalpų užimtumo tvarkaraštis</h2>

    <?php
    if (count($rezervacijos) === 0)
    {
        echo "<p>Patvirtintų rezervacijų nėra.</p>";
    }
    else
    {
        echo "<table>";
        echo "<tr>";
        echo "<th>Patalpa</th>";
        echo "<th>Data</th>";
        echo "<th>Pradžia</th>";
        echo "<th>Pabaiga</th>";
        echo "</tr>";

        foreach ($rezervacijos as $rezervacija)
        {
            // 10:00:00 → 10:00
            $pradziosLaikas = substr($rezervacija["pradzios_laikas"], 0, 5);
            $pabaigosLaikas = substr($rezervacija["pabaigos_laikas"], 0, 5);

            echo "<tr>";
            echo "<td>" . htmlspecialchars($rezervacija["pavadinimas"]) . "</td>";
            echo "<td>" . $rezervacija["data"] . "</td>";
            echo "<td>" . $pradziosLaikas . "</td>";
            echo "<td>" . $pabaigosLaikas . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }
    ?>
</body>
</html>
