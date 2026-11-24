<?php
session_start();
require "db.php";
require "funkcijos.php";

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

$roles = ["registruotas", "organizatorius", "administratorius"];

// 0 – kuriamas naujas naudotojas, kitaip – redaguojamas esamas.
$naudotojoId = 0;

if (isset($_GET["id"]))
{
    $naudotojoId = (int) $_GET["id"];
}

if (isset($_POST["naudotojo_id"]))
{
    $naudotojoId = (int) $_POST["naudotojo_id"];
}

$vardas = "";
$pavarde = "";
$elPastas = "";
$naudotojoRole = "registruotas";
$slaptazodis = "";
$klaida = "";

// Redaguojant užpildo formą esamais duomenimis.
if ($naudotojoId > 0)
{
    $sql = "SELECT vardas, pavarde, el_pastas, role FROM naudotojas WHERE id = :id";

    $uzklausa = $pdo->prepare($sql);
    $uzklausa->execute([":id" => $naudotojoId]);

    // false – nerasta.
    $naudotojas = $uzklausa->fetch();

    if ($naudotojas === false)
    {
        $_SESSION["klaida"] = "Naudotojas nerastas.";
        header("Location: naudotojai.php");
        exit;
    }

    $vardas = $naudotojas["vardas"];
    $pavarde = $naudotojas["pavarde"];
    $elPastas = $naudotojas["el_pastas"];
    $naudotojoRole = $naudotojas["role"];
}

// Paspaustas „Išsaugoti“.
if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    // Tik tekstas.
    if (isset($_POST["vardas"]) && is_string($_POST["vardas"]))
    {
        $vardas = trim($_POST["vardas"]);
    }

    if (isset($_POST["pavarde"]) && is_string($_POST["pavarde"]))
    {
        $pavarde = trim($_POST["pavarde"]);
    }

    if (isset($_POST["el_pastas"]) && is_string($_POST["el_pastas"]))
    {
        $elPastas = trim($_POST["el_pastas"]);
    }

    if (isset($_POST["role"]) && is_string($_POST["role"]))
    {
        $naudotojoRole = $_POST["role"];
    }

    if (isset($_POST["slaptazodis"]) && is_string($_POST["slaptazodis"]))
    {
        $slaptazodis = $_POST["slaptazodis"];
    }

    if ($vardas === "" || $pavarde === "" || $elPastas === "")
    {
        $klaida = "Užpildykite vardą, pavardę ir el. paštą.";
    }
    else if (mb_strlen($vardas) > 50 || mb_strlen($pavarde) > 100)
    {
        $klaida = "Vardas gali būti iki 50, pavardė – iki 100 simbolių.";
    }
    else if (filter_var($elPastas, FILTER_VALIDATE_EMAIL) === false || mb_strlen($elPastas) > 254)
    {
        $klaida = "Neteisingas el. pašto formatas.";
    }
    else if (elPastasUzimtas($pdo, $elPastas, $naudotojoId))
    {
        $klaida = "Toks el. paštas jau naudojamas.";
    }
    else if (!in_array($naudotojoRole, $roles, true))
    {
        $klaida = "Neteisinga rolė.";
    }
    else if ($naudotojoId == $_SESSION["naudotojo_id"] && $naudotojoRole !== "administratorius")
    {
        // Kad sistema neliktų be administratoriaus.
        $klaida = "Savo rolės pakeisti negalima.";
    }
    else if ($naudotojoId === 0 && $slaptazodis === "")
    {
        $klaida = "Naujam naudotojui slaptažodis privalomas.";
    }
    else if ($slaptazodis !== "" && mb_strlen($slaptazodis) < 8)
    {
        $klaida = "Slaptažodis turi būti bent 8 simbolių.";
    }

    // Klaidų nėra – išsaugo.
    if ($klaida === "")
    {
        if ($naudotojoId === 0)
        {
            // Slaptažodis saugomas tik maišos pavidalu.
            $sql = "INSERT INTO naudotojas (vardas, pavarde, el_pastas, slaptazodzio_maisa, aktyvus, role)
                    VALUES (:vardas, :pavarde, :el_pastas, :maisa, 1, :role)";

            $uzklausa = $pdo->prepare($sql);
            $uzklausa->execute([
                ":vardas" => $vardas,
                ":pavarde" => $pavarde,
                ":el_pastas" => $elPastas,
                ":maisa" => password_hash($slaptazodis, PASSWORD_DEFAULT),
                ":role" => $naudotojoRole
            ]);

            $_SESSION["pranesimas"] = "Naudotojas sukurtas.";
        }
        else
        {
            $sql = "UPDATE naudotojas
                    SET vardas = :vardas, pavarde = :pavarde, el_pastas = :el_pastas, role = :role
                    WHERE id = :id";

            $uzklausa = $pdo->prepare($sql);
            $uzklausa->execute([
                ":vardas" => $vardas,
                ":pavarde" => $pavarde,
                ":el_pastas" => $elPastas,
                ":role" => $naudotojoRole,
                ":id" => $naudotojoId
            ]);

            // Tuščias laukas – slaptažodis nekeičiamas.
            if ($slaptazodis !== "")
            {
                $sql = "UPDATE naudotojas SET slaptazodzio_maisa = :maisa WHERE id = :id";

                $uzklausa = $pdo->prepare($sql);
                $uzklausa->execute([
                    ":maisa" => password_hash($slaptazodis, PASSWORD_DEFAULT),
                    ":id" => $naudotojoId
                ]);
            }

            // Atnaujina vardą meniu, jei redaguota sava paskyra.
            if ($naudotojoId == $_SESSION["naudotojo_id"])
            {
                $_SESSION["vardas"] = $vardas;
            }

            $_SESSION["pranesimas"] = "Naudotojo duomenys išsaugoti.";
        }

        header("Location: naudotojai.php");
        exit;
    }
}

if ($naudotojoId === 0)
{
    $puslapioPavadinimas = "Naujas naudotojas";
}
else
{
    $puslapioPavadinimas = "Naudotojo redagavimas";
}

require "antraste.php";
?>

    <h2><?php echo htmlspecialchars($puslapioPavadinimas); ?></h2>

    <?php
    if ($klaida !== "")
    {
        echo '<p class="klaida">' . htmlspecialchars($klaida) . '</p>';
    }
    ?>

    <form method="post" action="naudotojas.php">
        <input type="hidden" name="naudotojo_id" value="<?php echo $naudotojoId; ?>">

        <p>
            <label for="vardas">Vardas:</label>
            <input type="text" id="vardas" name="vardas" required
                   value="<?php echo htmlspecialchars($vardas); ?>">
        </p>

        <p>
            <label for="pavarde">Pavardė:</label>
            <input type="text" id="pavarde" name="pavarde" required
                   value="<?php echo htmlspecialchars($pavarde); ?>">
        </p>

        <p>
            <label for="el_pastas">El. paštas:</label>
            <input type="email" id="el_pastas" name="el_pastas" required
                   value="<?php echo htmlspecialchars($elPastas); ?>">
        </p>

        <p>
            <label for="role">Rolė:</label>
            <select id="role" name="role">
                <?php
                foreach ($roles as $galimaRole)
                {
                    $pazymeta = "";

                    if ($galimaRole === $naudotojoRole)
                    {
                        $pazymeta = "selected";
                    }

                    echo '<option value="' . $galimaRole . '" ' . $pazymeta . '>' . $galimaRole . '</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <?php
            if ($naudotojoId === 0)
            {
                echo '<label for="slaptazodis">Slaptažodis:</label>';
            }
            else
            {
                echo '<label for="slaptazodis">Naujas slaptažodis (neprivaloma):</label>';
            }
            ?>
            <input type="password" id="slaptazodis" name="slaptazodis">
        </p>

        <button type="submit">Išsaugoti</button>
    </form>

    <p><a href="naudotojai.php">Atgal į sąrašą</a></p>
</body>
</html>
