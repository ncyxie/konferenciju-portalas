<?php
// Sesija – kas prisijungęs.
session_start();
require "db.php";

if (isset($_SESSION["naudotojo_id"]))
{
    header("Location: index.php");
    exit;
}

$elPastas = "";
$slaptazodis = "";
$klaida = "";

// Forma išsiųsta.
if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    // Tik tekstas.
    if (isset($_POST["el_pastas"]) && is_string($_POST["el_pastas"]))
    {
        $elPastas = trim($_POST["el_pastas"]);
    }

    if (isset($_POST["slaptazodis"]) && is_string($_POST["slaptazodis"]))
    {
        $slaptazodis = $_POST["slaptazodis"];
    }

    if ($elPastas === "" || $slaptazodis === "")
    {
        $klaida = "Užpildykite abu laukus.";
    }
    else if (filter_var($elPastas, FILTER_VALIDATE_EMAIL) === false)
    {
        $klaida = "Neteisingas el. pašto formatas.";
    }
    else
    {
        // Reikšmė perduodama per execute (apsauga nuo SQL injekcijos).
        $sql = "SELECT id, vardas, role, slaptazodzio_maisa
                FROM naudotojas
                WHERE el_pastas = :el_pastas
                AND aktyvus = 1";

        $uzklausa = $pdo->prepare($sql);
        $uzklausa->execute([":el_pastas" => $elPastas]);

        // false – nerasta.
        $naudotojas = $uzklausa->fetch();

        // Tikrina slaptažodį su maiša.
        if ($naudotojas !== false && password_verify($slaptazodis, $naudotojas["slaptazodzio_maisa"]))
        {
            // Naujas sesijos ID.
            session_regenerate_id(true);

            // Įsimena prisijungusį naudotoją.
            $_SESSION["naudotojo_id"] = $naudotojas["id"];
            $_SESSION["vardas"] = $naudotojas["vardas"];
            $_SESSION["role"] = $naudotojas["role"];

            header("Location: index.php");
            exit;
        }
        else
        {
            $klaida = "Neteisingas el. paštas arba slaptažodis.";
        }
    }
}

$puslapioPavadinimas = "Prisijungimas";
require "antraste.php";
?>

    <h2>Prisijungimas</h2>

    <?php
    if ($klaida !== "")
    {
        echo '<p class="klaida">' . htmlspecialchars($klaida) . '</p>';
    }
    ?>

    <form method="post" action="prisijungti.php">
        <p>
            <label for="el_pastas">El. paštas:</label>
            <input type="email" id="el_pastas" name="el_pastas" required
                   value="<?php echo htmlspecialchars($elPastas); ?>">
        </p>

        <p>
            <label for="slaptazodis">Slaptažodis:</label>
            <input type="password" id="slaptazodis" name="slaptazodis" required>
        </p>

        <button type="submit">Prisijungti</button>
    </form>
</body>
</html>
