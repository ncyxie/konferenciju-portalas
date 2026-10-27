<?php
// Bendros funkcijos.

// Tikrina laiką (VV:MM).
function laikasTeisingas($laikas)
{
    if (strlen($laikas) !== 5)
    {
        return false;
    }

    // Padalina per dvitaškį.
    $dalys = explode(":", $laikas);

    if (count($dalys) !== 2)
    {
        return false;
    }

    if (strlen($dalys[0]) !== 2 || strlen($dalys[1]) !== 2)
    {
        return false;
    }

    $valandos = $dalys[0];
    $minutes = $dalys[1];

    // Tik skaitmenys.
    if (!ctype_digit($valandos) || !ctype_digit($minutes))
    {
        return false;
    }

    if ($valandos > 23 || $minutes > 59)
    {
        return false;
    }

    return true;
}

// Tikrina paieškos ir rezervacijos laukus.
// Grąžina klaidą arba "".
function tikrintiPaieskosDuomenis($data, $pradzia, $pabaiga, $kiekis)
{
    if ($data === "" || $pradzia === "" || $pabaiga === "" || $kiekis === "")
    {
        return "Užpildykite visus privalomus laukus.";
    }

    // Formatas MMMM-MM-DD.
    if (strlen($data) !== 10)
    {
        return "Neteisinga data.";
    }

    // Padalina per brūkšnelį.
    $datosDalys = explode("-", $data);

    if (count($datosDalys) !== 3)
    {
        return "Neteisinga data.";
    }

    if (strlen($datosDalys[0]) !== 4 || strlen($datosDalys[1]) !== 2 || strlen($datosDalys[2]) !== 2)
    {
        return "Neteisinga data.";
    }

    if (!ctype_digit($datosDalys[0]) || !ctype_digit($datosDalys[1]) || !ctype_digit($datosDalys[2]))
    {
        return "Neteisinga data.";
    }

    $metai = (int) $datosDalys[0];
    $menuo = (int) $datosDalys[1];
    $diena = (int) $datosDalys[2];

    // Ar tokia diena egzistuoja.
    if (!checkdate($menuo, $diena, $metai))
    {
        return "Neteisinga data.";
    }

    if (!laikasTeisingas($pradzia))
    {
        return "Neteisingas pradžios laikas.";
    }

    if (!laikasTeisingas($pabaiga))
    {
        return "Neteisingas pabaigos laikas.";
    }

    if (strtotime($pabaiga) <= strtotime($pradzia))
    {
        return "Pabaigos laikas turi būti vėlesnis už pradžios laiką.";
    }

    if (!ctype_digit($kiekis) || $kiekis <= 0)
    {
        return "Klausytojų skaičius turi būti teigiamas skaičius.";
    }

    if (strtotime($data . " " . $pradzia) <= time())
    {
        return "Negalima pasirinkti praėjusio laiko.";
    }

    return "";
}

// Tikrina, ar patalpa tuo metu užimta.
function patalpaUzimta($pdo, $patalposId, $data, $pradzia, $pabaiga)
{
    // Laikai persidengia.
    $sql = "SELECT COUNT(*)
            FROM rezervacija
            WHERE fk_rezervacija_patalpa = :patalpa
            AND data = :data
            AND busena = 'patvirtinta'
            AND pradzios_laikas < :pabaiga
            AND pabaigos_laikas > :pradzia";

    $uzklausa = $pdo->prepare($sql);
    $uzklausa->execute([
        ":patalpa" => $patalposId,
        ":data" => $data,
        ":pradzia" => $pradzia,
        ":pabaiga" => $pabaiga
    ]);

    // Rastų įrašų skaičius.
    $kiekis = $uzklausa->fetchColumn();

    if ($kiekis > 0)
    {
        return true;
    }

    return false;
}

// Tikrina, ar patalpa turi nurodytą įrangą.
function patalpaTuriIranga($pdo, $patalposId, $irangosId)
{
    $sql = "SELECT COUNT(*)
            FROM patalpos_iranga
            WHERE fk_patalpos_iranga_patalpa = :patalpa
            AND fk_patalpos_iranga_iranga = :iranga";

    $uzklausa = $pdo->prepare($sql);
    $uzklausa->execute([
        ":patalpa" => $patalposId,
        ":iranga" => $irangosId
    ]);

    $kiekis = $uzklausa->fetchColumn();

    if ($kiekis > 0)
    {
        return true;
    }

    return false;
}
