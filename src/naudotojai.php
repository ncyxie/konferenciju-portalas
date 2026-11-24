<?php
session_start();
require "db.php";

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

// Paspaustas „Išjungti“ arba „Įjungti“.
if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $naudotojoId = 0;
    $veiksmas = "";

    if (isset($_POST["naudotojo_id"]))
    {
        $naudotojoId = (int) $_POST["naudotojo_id"];
    }

    if (isset($_POST["veiksmas"]))
    {
        $veiksmas = $_POST["veiksmas"];
    }

    // Kad sistema neliktų be administratoriaus.
    if ($naudotojoId == $_SESSION["naudotojo_id"])
    {
        $klaida = "Savo paskyros išjungti negalima.";
    }
    else if ($veiksmas === "isjungti")
    {
        $sql = "UPDATE naudotojas SET aktyvus = 0 WHERE id = :id";

        $uzklausa = $pdo->prepare($sql);
        $uzklausa->execute([":id" => $naudotojoId]);

        $pranesimas = "Paskyra išjungta.";
    }
    else if ($veiksmas === "ijungti")
    {
        $sql = "UPDATE naudotojas SET aktyvus = 1 WHERE id = :id";

        $uzklausa = $pdo->prepare($sql);
        $uzklausa->execute([":id" => $naudotojoId]);

        $pranesimas = "Paskyra įjungta.";
    }
    else
    {
        $klaida = "Neteisingas veiksmas.";
    }

    // Pranešimai – per sesiją.
    $_SESSION["pranesimas"] = $pranesimas;
    $_SESSION["klaida"] = $klaida;

    // Nukreipia, kad F5 nepakartotų veiksmo.
    header("Location: naudotojai.php");
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

$sql = "SELECT id, vardas, pavarde, el_pastas, role, aktyvus
        FROM naudotojas
        ORDER BY pavarde, vardas";

$uzklausa = $pdo->query($sql);
$naudotojai = $uzklausa->fetchAll();

$puslapioPavadinimas = "Naudotojai";
require "antraste.php";
?>

    <h2>Naudotojai</h2>

    <?php
    if ($pranesimas !== "")
    {
        echo '<p class="sekme">' . htmlspecialchars($pranesimas) . '</p>';
    }

    if ($klaida !== "")
    {
        echo '<p class="klaida">' . htmlspecialchars($klaida) . '</p>';
    }
    ?>

    <p><a href="naudotojas.php">Sukurti naują naudotoją</a></p>

    <div class="lentele">
        <table>
            <tr>
                <th>Vardas ir pavardė</th>
                <th>El. paštas</th>
                <th>Rolė</th>
                <th>Būsena</th>
                <th>Veiksmai</th>
            </tr>

            <?php
            foreach ($naudotojai as $naudotojas)
            {
                $vardasPavarde = $naudotojas["vardas"] . " " . $naudotojas["pavarde"];

                echo "<tr>";
                echo "<td>" . htmlspecialchars($vardasPavarde) . "</td>";
                echo "<td>" . htmlspecialchars($naudotojas["el_pastas"]) . "</td>";
                echo "<td>" . htmlspecialchars($naudotojas["role"]) . "</td>";

                if ($naudotojas["aktyvus"] == 1)
                {
                    echo "<td>Aktyvus</td>";
                }
                else
                {
                    echo "<td>Išjungtas</td>";
                }

                echo '<td class="nelauzyti">';
                echo '<a href="naudotojas.php?id=' . $naudotojas["id"] . '">Redaguoti</a> ';

                // Savo paskyrai mygtukas nerodomas.
                if ($naudotojas["id"] != $_SESSION["naudotojo_id"])
                {
                    echo '<form method="post" action="naudotojai.php" class="eilutes-forma">';
                    echo '<input type="hidden" name="naudotojo_id" value="' . $naudotojas["id"] . '">';

                    if ($naudotojas["aktyvus"] == 1)
                    {
                        echo '<button type="submit" name="veiksmas" value="isjungti">Išjungti</button>';
                    }
                    else
                    {
                        echo '<button type="submit" name="veiksmas" value="ijungti">Įjungti</button>';
                    }

                    echo '</form>';
                }

                echo "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
