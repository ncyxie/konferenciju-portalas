<?php
session_start();
require "db.php";

// Tik organizatoriui.
if (!isset($_SESSION["naudotojo_id"]))
{
    header("Location: prisijungti.php");
    exit;
}

if ($_SESSION["role"] !== "organizatorius")
{
    echo "Neturite teisės atidaryti šio puslapio.";
    exit;
}

// Tik prisijungusio naudotojo pranešimai, naujausi viršuje.
$sql = "SELECT tekstas, sukurimo_data, laikas
        FROM pranesimas
        WHERE fk_pranesimas_naudotojas = :naudotojas
        ORDER BY id DESC";

$uzklausa = $pdo->prepare($sql);
$uzklausa->execute([":naudotojas" => $_SESSION["naudotojo_id"]]);
$pranesimai = $uzklausa->fetchAll();

$puslapioPavadinimas = "Pranešimai";
require "antraste.php";
?>

    <h2>Pranešimai</h2>

    <?php
    if (count($pranesimai) === 0)
    {
        echo "<p>Pranešimų nėra.</p>";
    }
    else
    {
        echo '<div class="lentele">';
        echo "<table>";
        echo "<tr>";
        echo "<th>Data</th>";
        echo "<th>Laikas</th>";
        echo "<th>Pranešimas</th>";
        echo "</tr>";

        foreach ($pranesimai as $pranesimas)
        {
            echo "<tr>";
            echo "<td>" . $pranesimas["sukurimo_data"] . "</td>";
            echo "<td>" . substr($pranesimas["laikas"], 0, 5) . "</td>";
            echo "<td>" . htmlspecialchars($pranesimas["tekstas"]) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
        echo "</div>";
    }
    ?>
</body>
</html>
