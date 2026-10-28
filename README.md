# Konferencijų organizavimo portalas

Konferencijų organizavimo portalas – internetinė sistema, skirta konferencijų informacijos peržiūrai, patalpų paieškai ir rezervacijų valdymui. Sistema leidžia organizatoriams ieškoti tinkamų patalpų pagal laiką, talpą ir įrangą, pateikti rezervacijos prašymus, o administratoriams – juos patvirtinti arba atmesti.

Projektas kuriamas naudojant **PHP, MySQL, HTML ir CSS**. Programos paleidimui naudojamas **Docker**.

## Reikalavimai

Norint paleisti projektą, reikia turėti:

- Docker su Docker Compose
- Git (projekto atsisiuntimui)

## Diegimas

Šiuos veiksmus reikia atlikti pirmą kartą diegiant projektą.

**1. Atsisiųskite projektą:**

```bash
git clone https://github.com/ncyxie/konferenciju-portalas.git
cd konferenciju-portalas
```

**2. Sukurkite aplinkos konfigūracijos failą:**

```bash
cp .env.example .env
```

Faile `.env` pakeiskite pavyzdinius MySQL slaptažodžius pasirinktais slaptažodžiais.

**3. Sukurkite ir paleiskite Docker konteinerius:**

```bash
docker compose up -d --build
```

Pirmą kartą inicializuojant tuščią duomenų bazę, automatiškai vykdomi `db/schema.sql` ir `db/seed.sql` failai, sukuriantys lenteles ir įkeliantys demonstracinius duomenis.

## Paleidimas

Jeigu projektas jau įdiegtas, atidarykite terminalą projekto aplanke ir vykdykite:

```bash
docker compose up -d
```

Sistemą galima pasiekti naršyklėje:

- **Konferencijų portalas:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081

## Sustabdymas

Norėdami sustabdyti Docker konteinerius, vykdykite:

```bash
docker compose down
```

Duomenų bazės duomenys išsaugomi Docker tome, todėl kitą kartą paleidus projektą jų kurti iš naujo nereikia.

## Demonstraciniai prisijungimai

| Rolė | El. paštas | Slaptažodis |
|---|---|---|
| Dalyvis | dalyvis@example.test | Demo1234! |
| Organizatorius | organizatorius@example.test | Demo1234! |
| Administratorius | admin@example.test | Demo1234! |

Šios paskyros skirtos tik sistemos demonstravimui.

## Pagrindinės funkcijos

- Konferencijų ir jų sekcijų peržiūra.
- Naudotojų prisijungimas ir prieigos teisių valdymas pagal roles.
- Patalpų paieška pagal datą, laiką, talpą ir įrangą.
- Artimiausių patalpų paieška pagal atstumą.
- Patalpų užimtumo peržiūra.
- Rezervacijos prašymo pateikimas.
- Rezervacijų patvirtinimas ir atmetimas.
- Persidengiančių patvirtintų rezervacijų tikrinimas.

## Projekto struktūra

```text
konferenciju-portalas/
├── db/
│   ├── schema.sql
│   └── seed.sql
├── src/
├── .env.example
├── Dockerfile
└── compose.yaml
```

## Licencija

Projektas platinamas pagal MIT licenciją.