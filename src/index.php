<?php
session_start();
require "db.php";

$sql = "SELECT id, pavadinimas, aprasymas
        FROM konferencija
        ORDER BY id DESC";

$uzklausa = $pdo->query($sql);
$konferencijos = $uzklausa->fetchAll();

$puslapioPavadinimas = "Konferencijų organizavimo portalas";
require "antraste.php";
?>

    <h2>Konferencijos</h2>

    <?php
    if (count($konferencijos) === 0)
    {
        echo "<p>Konferencijų kol kas nėra.</p>";
    }

    foreach ($konferencijos as $konferencija)
    {
        echo "<article>";
        echo "<h3>" . htmlspecialchars($konferencija["pavadinimas"]) . "</h3>";

        // Aprašymas neprivalomas.
        if ($konferencija["aprasymas"] !== null && $konferencija["aprasymas"] !== "")
        {
            echo "<p>" . htmlspecialchars($konferencija["aprasymas"]) . "</p>";
        }

        // Konferencijos sekcijos.
        $sql = "SELECT pavadinimas, aprasymas
                FROM sekcija
                WHERE fk_sekcija_konferencija = :konferencija
                ORDER BY id";

        $uzklausa = $pdo->prepare($sql);
        $uzklausa->execute([":konferencija" => $konferencija["id"]]);
        $sekcijos = $uzklausa->fetchAll();

        echo "<h4>Sekcijos</h4>";

        if (count($sekcijos) === 0)
        {
            echo "<p>Sekcijų kol kas nėra.</p>";
        }
        else
        {
            echo "<ul>";

            foreach ($sekcijos as $sekcija)
            {
                echo "<li>";
                echo "<strong>" . htmlspecialchars($sekcija["pavadinimas"]) . "</strong>";

                if ($sekcija["aprasymas"] !== null && $sekcija["aprasymas"] !== "")
                {
                    echo " – " . htmlspecialchars($sekcija["aprasymas"]);
                }

                echo "</li>";
            }

            echo "</ul>";
        }

        echo "</article>";
    }
    ?>
</body>
</html>
