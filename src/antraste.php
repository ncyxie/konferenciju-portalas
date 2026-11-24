<?php
// Bendra puslapių antraštė ir meniu.
// Reikia $puslapioPavadinimas.
?>
<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($puslapioPavadinimas); ?></title>
    <link rel="stylesheet" href="stilius.css">
</head>
<body>
    <header>
        <h1>Konferencijų organizavimo portalas</h1>
        <p>Autorius: Mantas Gaižutis</p>
    </header>

    <nav>
        <a href="index.php">Pradžia</a>
        <?php
        // Meniu pagal rolę.
        if (isset($_SESSION["naudotojo_id"]))
        {
            $role = $_SESSION["role"];

            if ($role === "registruotas")
            {
                echo '<a href="sekcijos.php">Sekcijos</a>';
            }

            if ($role === "organizatorius")
            {
                echo '<a href="patalpos.php">Patalpos</a>';
                echo '<a href="uzimtumas.php">Užimtumas</a>';
                echo '<a href="pranesimai.php">Pranešimai</a>';
            }

            if ($role === "administratorius")
            {
                echo '<a href="uzimtumas.php">Užimtumas</a>';
                echo '<a href="rezervacijos.php">Prašymai</a>';
                echo '<a href="naudotojai.php">Naudotojai</a>';
            }

            // Vardas ir „Atsijungti“ – kartu, dešinėje.
            echo '<span class="prisijunges">';
            echo htmlspecialchars($_SESSION["vardas"]) . ' (' . $role . ')';
            echo '<a href="atsijungti.php">Atsijungti</a>';
            echo '</span>';
        }
        else
        {
            echo '<a href="prisijungti.php">Prisijungti</a>';
        }
        ?>
    </nav>
