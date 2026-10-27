-- Demonstraciniai duomenys naujai, tuščiai duomenų bazei.
-- Visų trijų naudotojų slaptažodis: Demo1234!
SET NAMES utf8mb4;

-- Naudotojai
INSERT INTO naudotojas
(id, vardas, pavarde, el_pastas, slaptazodzio_maisa, aktyvus, role)
VALUES
(1, 'Darius', 'Dalyvis', 'dalyvis@example.test',
 '$2y$12$6TRRL.VFcyS6KFqFik2B2etnIyztHrlOtijWG/ITLGzVimJxY4td2', 1, 'registruotas'),
(2, 'Ona', 'Organizatorė', 'organizatorius@example.test',
 '$2y$12$bZiqbufD5sBq4H5wSo2EUOgUR.tAICGIav.eeD7KNHM9P5Wj93AlC', 1, 'organizatorius'),
(3, 'Adomas', 'Administratorius', 'admin@example.test',
 '$2y$12$LvZP4J.PAwC.vQef7KU6Y./pjmt0JpNGNh1r0ZGXN5QcEHCY90CO6', 1, 'administratorius');

-- Patalpos
INSERT INTO patalpa (id, pavadinimas, talpa, aktyvi)
VALUES
(1, '101 auditorija', 30, 1),
(2, '202 auditorija', 60, 1),
(3, 'Didžioji salė', 120, 1);

-- Įranga
INSERT INTO iranga (id, pavadinimas)
VALUES
(1, 'Projektorius'),
(2, 'Mikrofonas'),
(3, 'Kompiuteris');

-- Įranga patalpose
INSERT INTO patalpos_iranga
(fk_patalpos_iranga_patalpa, fk_patalpos_iranga_iranga)
VALUES
(1, 1),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(3, 3);

-- Sąlyginiai atstumai tarp patalpų (mažesnis skaičius – arčiau)
INSERT INTO patalpu_atstumas
(atstumas, fk_atstumas_patalpa1, fk_atstumas_patalpa2)
VALUES
(20, 1, 2),
(35, 2, 3),
(70, 1, 3);

-- Konferencija
INSERT INTO konferencija
(id, pavadinimas, aprasymas, fk_konferencija_naudotojas)
VALUES
(1, 'Interneto technologijų konferencija',
 'Demonstracinė konferencija apie interneto technologijas.', 2);

-- Sekcijos
INSERT INTO sekcija
(id, pavadinimas, aprasymas, fk_sekcija_konferencija)
VALUES
(1, 'PHP ir MySQL pagrindai',
 'Internetinių sistemų kūrimas naudojant PHP ir MySQL.', 1),
(2, 'Kompiuterių tinklai',
 'Kompiuterių tinklų veikimo principai ir technologijos.', 1);

-- Rezervacijos: patvirtinta, laukiama ir atmesta.
-- Ketvirta rezervacija persidengia su pirma, todėl jos patvirtinti negalima.
INSERT INTO rezervacija
(id, data, pradzios_laikas, pabaigos_laikas, klausytoju_kiekis,
 busena, fk_rezervacija_naudotojas, fk_rezervacija_patalpa)
VALUES
(1, '2027-06-10', '10:00:00', '12:00:00', 50, 'patvirtinta', 2, 2),
(2, '2027-06-11', '09:00:00', '11:00:00', 90, 'laukiama', 2, 3),
(3, '2027-06-12', '15:00:00', '16:00:00', 20, 'atmesta', 2, 1),
(4, '2027-06-10', '11:00:00', '13:00:00', 40, 'laukiama', 2, 2);
