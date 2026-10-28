# Konferencijų organizavimo portalas

Konferencijų organizavimo portalas – internetinė sistema, skirta konferencijų informacijos peržiūrai, patalpų paieškai ir rezervacijų valdymui. Sistema leidžia organizatoriams ieškoti tinkamų patalpų pagal laiką, talpą ir įrangą, pateikti rezervacijos prašymus, o administratoriams – juos patvirtinti arba atmesti.

Projektas kuriamas naudojant **PHP, MySQL, HTML ir CSS**. Sistemos paleidimui naudojamas **Docker**.

## Reikalavimai

Projektui paleisti reikalinga:

- Docker su Docker Compose
- Git

## Diegimas

Pradinį paruošimą atlikti vieną kartą. Toliau pateiktos komandos skirtos **Linux terminalui**.

**Atsisiųsti projektą:**

```bash
git clone https://github.com/ncyxie/konferenciju-portalas.git
cd konferenciju-portalas
```

**Sukurti aplinkos konfigūracijos failą:**

```bash
cp .env.example .env
```

Faile `.env` nurodyti pasirinktus MySQL slaptažodžius. Naudojant „VirtualBox“ bendrinamą aplanką, prireikus nustatyti `SHARED_GID` reikšmę pagal aplanko grupės ID.

**Sukurti ir paleisti Docker konteinerius:**

```bash
docker compose up -d --build
```

Pirmą kartą inicializuojant tuščią duomenų bazę, automatiškai vykdomi `db/schema.sql` ir `db/seed.sql` failai, skirti lentelėms sukurti ir demonstraciniams duomenims įkelti.

## Paleidimas

Jau įdiegto projekto aplanke paleisti Docker konteinerius:

```bash
docker compose up -d
```

Sistemą atidaryti naršyklėje:

- **Konferencijų portalas:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081

## Sustabdymas

Sustabdyti Docker konteinerius:

```bash
docker compose down
```

Duomenų bazės duomenys išsaugomi Docker tome ir lieka prieinami kitą kartą paleidus projektą.

## Demonstraciniai prisijungimai

| Rolė | El. paštas | Slaptažodis |
|---|---|---|
| Dalyvis | dalyvis@example.test | Demo1234! |
| Organizatorius | organizatorius@example.test | Demo1234! |
| Administratorius | admin@example.test | Demo1234! |

Demonstracines paskyras naudoti tik sistemos testavimui ir pristatymui.

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