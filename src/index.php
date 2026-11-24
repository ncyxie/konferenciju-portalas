<?php
session_start();

$puslapioPavadinimas = "Konferencijų organizavimo portalas";
require "antraste.php";
?>

    <h2>Pradžia</h2>

    <p>Portale organizatoriai rezervuoja patalpas konferencijoms, o dalyviai registruojasi į konferencijų sekcijas.</p>

    <?php
    if (isset($_SESSION["naudotojo_id"]))
    {
        echo "<p>Sveiki, " . htmlspecialchars($_SESSION["vardas"]) . ". Pasirinkite norimą veiksmą meniu.</p>";
    }
    else
    {
        echo '<p>Norėdami naudotis sistema, <a href="prisijungti.php">prisijunkite</a>.</p>';
    }
    ?>
</body>
</html>
