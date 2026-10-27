#@(#) script.ddl

DROP TABLE IF EXISTS rezervacija;
DROP TABLE IF EXISTS dalyvio_registracija;
DROP TABLE IF EXISTS sekcija;
DROP TABLE IF EXISTS pranesimas;
DROP TABLE IF EXISTS patalpu_atstumas;
DROP TABLE IF EXISTS patalpos_iranga;
DROP TABLE IF EXISTS konferencija;
DROP TABLE IF EXISTS patalpa;
DROP TABLE IF EXISTS naudotojas;
DROP TABLE IF EXISTS iranga;
CREATE TABLE iranga
(
	pavadinimas varchar (100) NOT NULL,
	id integer NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(id)
);

CREATE TABLE naudotojas
(
	vardas varchar (50) NOT NULL,
	pavarde varchar (100) NOT NULL,
	el_pastas varchar (254) NOT NULL,
	slaptazodzio_maisa varchar (255) NOT NULL,
	aktyvus boolean NOT NULL,
	role char (16) NOT NULL,
	id integer NOT NULL AUTO_INCREMENT,
	CHECK(role in ('registruotas', 'organizatorius', 'administratorius')),
	PRIMARY KEY(id),
	CONSTRAINT uq_naudotojas_el_pastas UNIQUE(el_pastas)
);

CREATE TABLE patalpa
(
	pavadinimas varchar (100) NOT NULL,
	talpa integer NOT NULL,
	aktyvi boolean NOT NULL,
	id integer NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(id)
);

CREATE TABLE konferencija
(
	pavadinimas varchar (150) NOT NULL,
	aprasymas varchar (255) NULL,
	id integer NOT NULL AUTO_INCREMENT,
	fk_konferencija_naudotojas integer NOT NULL,
	PRIMARY KEY(id),
	CONSTRAINT fkc_konferencija_naudotojas FOREIGN KEY(fk_konferencija_naudotojas) REFERENCES naudotojas (id)
);

CREATE TABLE patalpos_iranga
(
	id integer NOT NULL AUTO_INCREMENT,
	fk_patalpos_iranga_patalpa integer NOT NULL,
	fk_patalpos_iranga_iranga integer NOT NULL,
	PRIMARY KEY(id),
	CONSTRAINT uq_patalpos_iranga UNIQUE(fk_patalpos_iranga_patalpa, fk_patalpos_iranga_iranga),
	CONSTRAINT fkc_patalpos_iranga_patalpa FOREIGN KEY(fk_patalpos_iranga_patalpa) REFERENCES patalpa (id),
	CONSTRAINT fkc_patalpos_iranga_iranga FOREIGN KEY(fk_patalpos_iranga_iranga) REFERENCES iranga (id)
);

CREATE TABLE patalpu_atstumas
(
	atstumas integer NOT NULL,
	id integer NOT NULL AUTO_INCREMENT,
	fk_atstumas_patalpa1 integer NOT NULL,
	fk_atstumas_patalpa2 integer NOT NULL,
	PRIMARY KEY(id),
	CONSTRAINT uq_patalpu_pora UNIQUE(fk_atstumas_patalpa1, fk_atstumas_patalpa2),
	CONSTRAINT chk_patalpu_pora CHECK(fk_atstumas_patalpa1 < fk_atstumas_patalpa2),
	CONSTRAINT fkc_atstumas_patalpa1 FOREIGN KEY(fk_atstumas_patalpa1) REFERENCES patalpa (id),
	CONSTRAINT fkc_atstumas_patalpa2 FOREIGN KEY(fk_atstumas_patalpa2) REFERENCES patalpa (id)
);

CREATE TABLE pranesimas
(
	tekstas varchar (255) NOT NULL,
	sukurimo_data date NOT NULL,
	laikas time NOT NULL,
	id integer NOT NULL AUTO_INCREMENT,
	fk_pranesimas_naudotojas integer NOT NULL,
	PRIMARY KEY(id),
	CONSTRAINT fkc_pranesimas_naudotojas FOREIGN KEY(fk_pranesimas_naudotojas) REFERENCES naudotojas (id)
);

CREATE TABLE sekcija
(
	pavadinimas varchar (150) NOT NULL,
	aprasymas varchar (255) NULL,
	id integer NOT NULL AUTO_INCREMENT,
	fk_sekcija_konferencija integer NOT NULL,
	PRIMARY KEY(id),
	CONSTRAINT fkc_sekcija_konferencija FOREIGN KEY(fk_sekcija_konferencija) REFERENCES konferencija (id)
);

CREATE TABLE dalyvio_registracija
(
	registracijos_laikas timestamp NOT NULL,
	id integer NOT NULL AUTO_INCREMENT,
	fk_registracija_naudotojas integer NOT NULL,
	fk_registracija_sekcija integer NOT NULL,
	PRIMARY KEY(id),
	CONSTRAINT uq_dalyvio_sekcija UNIQUE(fk_registracija_naudotojas, fk_registracija_sekcija),
	CONSTRAINT fkc_registracija_naudotojas FOREIGN KEY(fk_registracija_naudotojas) REFERENCES naudotojas (id),
	CONSTRAINT fkc_registracija_sekcija FOREIGN KEY(fk_registracija_sekcija) REFERENCES sekcija (id)
);

CREATE TABLE rezervacija
(
	data date NOT NULL,
	pradzios_laikas time NOT NULL,
	pabaigos_laikas time NOT NULL,
	klausytoju_kiekis integer NOT NULL,
	busena char (11) NOT NULL,
	id integer NOT NULL AUTO_INCREMENT,
	fk_rezervacija_naudotojas integer NOT NULL,
	fk_rezervacija_patalpa integer NOT NULL,
	fk_rezervacija_sekcija integer NULL,
	CHECK(busena in ('laukiama', 'patvirtinta', 'atmesta')),
	PRIMARY KEY(id),
	UNIQUE(fk_rezervacija_sekcija),
	CONSTRAINT fkc_rezervacija_naudotojas FOREIGN KEY(fk_rezervacija_naudotojas) REFERENCES naudotojas (id),
	CONSTRAINT 	fkc_rezervacija_patalpa FOREIGN KEY(fk_rezervacija_patalpa) REFERENCES patalpa (id),
	CONSTRAINT fkc_rezervacija_sekcija FOREIGN KEY(fk_rezervacija_sekcija) REFERENCES sekcija (id)
);
