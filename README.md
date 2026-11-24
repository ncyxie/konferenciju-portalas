# Konferencijų organizavimo portalas

Konferencijų organizavimo portalas – internetinė sistema, skirta patalpų konferencijoms rezervuoti ir dalyviams registruoti į sekcijas. Organizatoriai gali ieškoti tinkamų patalpų pagal datą, laiką, klausytojų skaičių ir įrangą, pateikti rezervacijos prašymus ir gauti pranešimus apie jų patvirtinimą. Administratorius tvirtina prašymus ir tvarko naudotojų paskyras, o dalyviai registruojasi į sekcijas, kuriose dar yra laisvų vietų.

Projektas sukurtas naudojant **PHP, MySQL, HTML, CSS ir JavaScript**. Sistemos paleidimui naudojamas **Docker**, todėl PHP, Apache ir MySQL nereikia atskirai konfigūruoti.

## Reikalavimai

Projektui paleisti reikalinga:

- Docker su Docker Compose
- Git

## Diegimas

Pradinį paruošimą reikia atlikti vieną kartą. Toliau pateiktos komandos skirtos **Linux terminalui**.

**Atsisiųsti projektą:**

```bash
git clone https://github.com/ncyxie/konferenciju-portalas.git
cd konferenciju-portalas
```

**Sukurti aplinkos konfigūracijos failą:**

```bash
cp .env.example .env
```

Faile `.env` nurodyti pasirinktus `MYSQL_PASSWORD` ir `MYSQL_ROOT_PASSWORD` slaptažodžius. Įprastoje Linux aplinkoje palikti `SHARED_GID=0`. Jei projektas laikomas VirtualBox bendrinamame aplanke, nurodyti `vboxsf` grupės numerį, kurį parodo komanda `getent group vboxsf`.

**Sukurti ir paleisti Docker konteinerius:**

```bash
docker compose up -d --build
```

Pirmą kartą paleidus sistemą, automatiškai vykdomi `db/schema.sql` ir `db/seed.sql` failai. Jie sukuria duomenų bazės lenteles ir įkelia kontrolinius (demonstracinius) duomenis.

## Paleidimas

Jei sistema jau įdiegta, projekto aplanke paleisti konteinerius:

```bash
docker compose up -d
```

Sistemą atidaryti naršyklėje:

- **Konferencijų organizavimo portalas:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081

## Sustabdymas

Sustabdyti Docker konteinerius:

```bash
docker compose down
```

Duomenų bazės duomenys išsaugomi Docker tome, todėl kitą kartą paleidus sistemą jie išlieka.

## Duomenų bazės perkūrimas

Duomenų bazė sukuriama tik pirmą kartą paleidus sistemą. Jei pakeičiami `db/schema.sql` arba `db/seed.sql` failai, duomenų bazę reikia sukurti iš naujo. Tada visi esami duomenys ištrinami ir vėl įkeliami kontroliniai duomenys.

```bash
docker compose down -v
docker compose up -d --build
```

## Demonstraciniai prisijungimai

Visų demonstracinių naudotojų slaptažodis yra `demo1234`.

| Rolė | El. paštas | Pastaba |
|---|---|---|
| Administratorius | admin@ktu.lt | |
| Organizatorius | org@ktu.lt | Turi patvirtintų ir laukiančių rezervacijų bei pranešimų |
| Registruotas naudotojas | dalyvis@ktu.lt | Dar neužsiregistravęs nė į vieną sekciją |
| Registruotas naudotojas | ruta@ktu.lt, tomas@ktu.lt, egle@ktu.lt | Jau užsiregistravę į sekcijas |
| Registruotas naudotojas | karolis@ktu.lt | Paskyra išjungta, todėl prisijungti negali |

Kontroliniuose duomenyse sekcija „Kompiuterių tinklai“ jau yra pilna, o vienas laukiantis prašymas persidengia su patvirtinta rezervacija. Taip galima iš karto pademonstruoti apsaugą nuo perpildymo ir užimtumo tikrinimą.

## Pagrindinės funkcijos

**Registruotas naudotojas (dalyvis):**

- Registracija į konferencijos sekciją, kai joje dar yra laisvų vietų.

**Organizatorius:**

- Patalpų paieška pagal datą, laiką, klausytojų skaičių ir įrangą.
- Artimiausių patalpų parinkimas pagal koreliacinę lentelę.
- Savaitės patalpų užimtumo tvarkaraščio peržiūra pagal valandas.
- Rezervacijos prašymo pateikimas.
- Vidinių pranešimų apie patvirtintas rezervacijas peržiūra.

**Administratorius:**

- Rezervacijų prašymų patvirtinimas ir atmetimas (užimtos patalpos patvirtinti neleidžiama).
- Patalpų užimtumo tvarkaraščio peržiūra.
- Naudotojų paskyrų kūrimas, redagavimas, išjungimas ir įjungimas.

## Projekto struktūra

```text
konferenciju-portalas/
├── db/
│   ├── Dockerfile
│   ├── schema.sql
│   └── seed.sql
├── src/
├── .env.example
├── Dockerfile
├── README.md
└── compose.yaml
```

## Licencija

Projektas platinamas pagal MIT licenciją.
