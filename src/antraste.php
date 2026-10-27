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
    <h1>Konferencijų organizavimo portalas</h1>
    <p>Autorius: Mantas Gaižutis</p>

    <nav>
        <a href="index.php">Pradžia</a>
        <?php
        // Meniu pagal rolę.
        if (isset($_SESSION["naudotojo_id"]))
        {
            $role = $_SESSION["role"];

            if ($role === "organizatorius" || $role === "administratorius")
            {
                echo '<a href="patalpos.php">Patalpos</a>';
                echo '<a href="uzimtumas.php">Užimtumas</a>';
            }

            if ($role === "administratorius")
            {
                echo '<a href="rezervacijos.php">Prašymai</a>';
            }

            echo '<span class="prisijunges">' . htmlspecialchars($_SESSION["vardas"]) . ' (' . $role . ')</span>';
            echo '<a href="atsijungti.php">Atsijungti</a>';
        }
        else
        {
            echo '<a href="prisijungti.php">Prisijungti</a>';
        }
        ?>
    </nav>
