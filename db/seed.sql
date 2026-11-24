-- Kontroliniai duomenys. Visų naudotojų slaptažodis: demo1234
SET NAMES utf8mb4;

-- Naudotojai (Karolis – išjungtas, prisijungti negali)
INSERT INTO naudotojas
(id, vardas, pavarde, el_pastas, slaptazodzio_maisa, aktyvus, role)
VALUES
(1, 'Darius', 'Dalyvis', 'dalyvis@ktu.lt',
 '$2y$12$0sCgHMIfpGaB3j7iACDiK.m/TrhHNqWVTki8lRbTdxRpzpBOSmcfO', 1, 'registruotas'),
(2, 'Ona', 'Organizatorė', 'org@ktu.lt',
 '$2y$12$WWAa.TxRsVpK4O4Zy6XbbeB.ODBoGaCd.6GvU3ZsI3MSDR/Ress9G', 1, 'organizatorius'),
(3, 'Adomas', 'Administratorius', 'admin@ktu.lt',
 '$2y$12$u47BMUkquYsPgkf6qTuB4.9JLGRNnBc92D3CXtLUjeAraWzckYIDK', 1, 'administratorius'),
(4, 'Rūta', 'Rimkutė', 'ruta@ktu.lt',
 '$2y$12$rms0fxLq4jhR9U.YyHcununZ0Y9Uzf13mVuq4dbBy3/iotHyEaTo.', 1, 'registruotas'),
(5, 'Tomas', 'Kazlauskas', 'tomas@ktu.lt',
 '$2y$12$YlBsfRO3OWBeXYbZs4/noONSODBZfKlAlMPT/jgJ4CjS1FgC19n5W', 1, 'registruotas'),
(6, 'Eglė', 'Petrauskienė', 'egle@ktu.lt',
 '$2y$12$W.WlEUp3UgaurXIVMSeUjOBuekzaisT4dRvZb5C/ySF2RehmbE7mO', 1, 'registruotas'),
(7, 'Karolis', 'Jonaitis', 'karolis@ktu.lt',
 '$2y$12$tz3O1yOX62LrikjeNZ1cVOZJGKnijx3gJfRmpFLGdFDhQzTpwXToy', 0, 'registruotas');

-- Patalpos (Seminarų kambarys mažas, kad sekcija būtų pilna)
INSERT INTO patalpa (id, pavadinimas, talpa, aktyvi)
VALUES
(1, '101 auditorija', 30, 1),
(2, '202 auditorija', 60, 1),
(3, 'Didžioji salė', 120, 1),
(4, 'Seminarų kambarys', 3, 1);

-- Įranga
INSERT INTO iranga (id, pavadinimas)
VALUES
(1, 'Projektorius'),
(2, 'Mikrofonas'),
(3, 'Kompiuteris');

-- Patalpų įranga
INSERT INTO patalpos_iranga
(fk_patalpos_iranga_patalpa, fk_patalpos_iranga_iranga)
VALUES
(1, 1),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(3, 3),
(4, 1);

-- Atstumai tarp patalpų
INSERT INTO patalpu_atstumas
(atstumas, fk_atstumas_patalpa1, fk_atstumas_patalpa2)
VALUES
(20, 1, 2),
(35, 2, 3),
(70, 1, 3),
(15, 1, 4),
(30, 2, 4),
(60, 3, 4);

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

-- Rezervacijos (4 persidengia su 1, todėl jos patvirtinti negalima)
INSERT INTO rezervacija
(id, data, pradzios_laikas, pabaigos_laikas, klausytoju_kiekis,
 busena, fk_rezervacija_naudotojas, fk_rezervacija_patalpa, fk_rezervacija_sekcija)
VALUES
(1, '2027-06-10', '10:00:00', '12:00:00', 50, 'patvirtinta', 2, 2, 1),
(2, '2027-06-11', '09:00:00', '11:00:00', 90, 'laukiama', 2, 3, NULL),
(3, '2027-06-12', '15:00:00', '16:00:00', 20, 'atmesta', 2, 1, NULL),
(4, '2027-06-10', '11:00:00', '13:00:00', 40, 'laukiama', 2, 2, NULL),
(5, '2027-06-12', '13:00:00', '14:00:00', 3, 'patvirtinta', 2, 4, 2);

-- Registracijos į sekcijas (2 sekcija pilna)
INSERT INTO dalyvio_registracija
(registracijos_laikas, fk_registracija_naudotojas, fk_registracija_sekcija)
VALUES
('2026-11-21 10:05:00', 4, 1),
('2026-11-21 10:06:00', 5, 1),
('2026-11-21 10:10:00', 4, 2),
('2026-11-21 10:12:00', 5, 2),
('2026-11-22 14:30:00', 6, 2);

-- Pranešimai organizatoriui
INSERT INTO pranesimas
(tekstas, sukurimo_data, laikas, fk_pranesimas_naudotojas)
VALUES
('Patvirtinta rezervacija: 202 auditorija, 2027-06-10 10:00–12:00.', '2026-11-20', '09:15:00', 2),
('Patvirtinta rezervacija: Seminarų kambarys, 2027-06-12 13:00–14:00.', '2026-11-20', '09:20:00', 2);
