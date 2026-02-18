# Navodila za uvoz meril

## Kako uvesti merila iz CSV datoteke

### 1. Priprava CSV datoteke

CSV datoteka mora vsebovati naslednje stolpce (prva vrstica je glava):

#### Obvezni stolpci:
- **Notranja številka** (ali `internal_id`) - mora biti edinstvena
- **Ime merila** (ali `name`)

#### Opcijski stolpci:
- **Vrsta merila** (ali `type`)
- **Lokacija** (ali `location`)
- **Frekvenca pregleda (leta)** (ali `frequency_years`) - privzeto: 2.0
- **Datum zadnjega pregleda** (ali `last_check_date`) - format: YYYY-MM-DD ali DD.MM.YYYY
- **Status** - možne vrednosti:
  - `USTREZA` (privzeto)
  - `NE_USTREZA`
  - `IZLOCENO`
  - `V_KONTROLI`
- **Odgovoren uporabnik** (ali `user_email`) - email naslov uporabnika

### 2. Format datuma

Datum lahko vnesete v naslednjih formatih:
- `2024-01-15` (YYYY-MM-DD)
- `15.01.2024` (DD.MM.YYYY)
- `15/01/2024` (DD/MM/YYYY)

### 3. Postopek uvoza

1. Odprite seznam meril v aplikaciji
2. Kliknite gumb **"Uvozi merila"** (zelen gumb z ikono puščice navzdol)
3. Izberite CSV datoteko
4. Kliknite **"Uvozi"**
5. Sistem vas bo obvestil o rezultatu uvoza

### 4. Primeri CSV datotek

Na voljo so tri različne primer CSV datoteke:

1. **`primer-uvoz-meril.csv`** - Polna verzija z vsemi stolpci (10 meril)
2. **`primer-uvoz-meril-minimal.csv`** - Minimalna verzija samo z obveznimi stolpci (5 meril)
3. **`primer-uvoz-meril-razlicni-datumi.csv`** - Primer z različnimi formati datumov (5 meril)

Vse datoteke najdete v mapi `gradivo/`.

### 5. Opozorila

- Če merilo z isto notranjo številko že obstaja, bo preskočeno
- Vrstice brez obveznih podatkov bodo preskočene
- Sistem bo avtomatsko izračunal datum naslednjega pregleda na podlagi datuma zadnjega pregleda in frekvence

### 6. Podprti formati datotek

- CSV (`.csv`)
- Excel (`.xls`, `.xlsx`) - omejeno podprto
- Besedilne datoteke (`.txt`)

### 7. Kodiranje

CSV datoteka mora biti v UTF-8 kodiranju za pravilno prikazovanje slovenskih znakov (č, š, ž).
