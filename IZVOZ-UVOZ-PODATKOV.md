# Izvoz in uvoz podatkov (lokalna → produkcija)

Kako vnešene podatke iz lokalne baze izvoziti in nato uvesti v produkcijsko bazo.

---

## Kratek pregled

1. **Lokalno:** `php artisan db:export-data` → ustvari JSON v `storage/app/`
2. **Prenesite** JSON na strežnik (npr. FileZilla, SCP)
3. **Na produkciji:** `php artisan migrate:fresh --force` (če še niste), nato  
   `php artisan db:import-data storage/app/merila-data-export-...json`

---

## 1. Izvoz na lokalnem (razvojnem) računalniku

### Zahtevam

- Aplikacija zagnana z lokalno bazo (SQLite ali MySQL).
- V `.env` naj bo nastavljena **lokalna** baza.

### Ukaz

```bash
php artisan db:export-data
```

Izvozi vse **poslovne** tabele v eno JSON datoteko:

- `users`
- `instruments`
- `delivery_notes`
- `delivery_note_items`
- `settings`

Datoteka se shrani v `storage/app/` z imenom  
`merila-data-export-YYYY-MM-DD-HHMMSS.json`.

### Možnosti

```bash
# Izberete izhodno datoteko
php artisan db:export-data --output=storage/app/moj-izvoz.json

# Vključite še activity log in obvestila
php artisan db:export-data --include-activity --include-notifications
```

### Kje je datoteka

Po izvozu:

```
storage/app/merila-data-export-2026-01-20-143052.json
```

To datoteko boste prenesli na produkcijski strežnik.

---

## 2. Prenos JSON na strežnik

- **FileZilla / SFTP:** naložite datoteko npr. v `/var/www/merila-app/storage/app/`
- **SCP:**  
  `scp storage/app/merila-data-export-....json uporabnik@strežnik:/var/www/merila-app/storage/app/`

Preverite, da je na strežniku prava pot do datoteke (npr.  
`/var/www/merila-app/storage/app/merila-data-export-....json`).

---

## 3. Uvoz na produkcijskem strežniku

### Zahtevam

- Na produkciji je **MySQL** (ali MariaDB) v `.env`.
- Na strežniku so že zagnane **vse migracije** (enaka shema kot lokalno).
- Če želite čisto bazo: pred uvozom zaženite  
  `php artisan migrate:fresh --force` (izbriše vse tabele in jih znova ustvari).

### Koraki

**A) Po „svežem“ migracijah (prazna baza)**

Če ste pravkar naredili `migrate:fresh` (ali prvič namestitev):

```bash
cd /var/www/merila-app

# Pot do JSON datoteke (prilagodite ime)
php artisan db:import-data storage/app/merila-data-export-2026-01-20-143052.json --force
```

**B) Če v produkcijski bazi že imate podatke**

Če želite obstoječe vnose **zamenjati** z uvoženimi:

```bash
php artisan db:import-data storage/app/merila-data-export-2026-01-20-143052.json --truncate --force
```

`--truncate` izprazni ustrezne tabele pred uvozom.  
`--force` izogne se interaktivnim vprašanjem.

### Možnosti

| Možnost   | Pomen |
|----------|--------|
| `--force` | Brez potrditve (priporočeno za skripte) |
| `--truncate` | Izprazni navedene tabele pred uvozom |

---

## 4. Kaj se uvozi

Uvozi se vsebina iz JSON-a za tabele:

- `users`
- `instruments`
- `delivery_notes`
- `delivery_note_items`
- `settings`
- `activity_log` (če ste izvozili z `--include-activity`)
- `notifications` (če ste izvozili z `--include-notifications`)

Ostale tabele (npr. `cache`, `sessions`, `jobs`, `migrations`) se **ne** izvozijo in **ne** uvažajo.

---

## 5. Pomembne opombe

- **Gesla in PIN:** Uvoženi uporabniki obdržijo isto (hashirano) geslo in PIN kot v izvozu. Na produkciji se prijavijo z enakimi podatki.
- **Certificate_path:** Če merila navezujejo poti do datotek (npr. certifikati), te datoteke morajo obstajati tudi na strežniku na enakih ali ustrezno prilagojenih poteh.
- **Enaka shema:** Izvoz je prirejen trenutni shemi. Če imate na produkciji drugačne migracije, lahko uvoz spodleti. Zagotovite, da so na produkciji zagnane iste migracije kot lokalno.

---

## 6. Težave in rešitve

### „Datoteka ne obstaja“

- Preverite, da je pot do JSON **glede na** koren projekta (npr.  
  `storage/app/merila-data-export-....json`).
- Če uporabljate absoluto pot:  
  `php artisan db:import-data /var/www/merila-app/storage/app/merila-data-export-....json`

### „Duplicate key“ / napake pri vstavljanju

- Če tabele niso prazne: uporabite **`--truncate`**  
  **ali** najprej `php artisan migrate:fresh --force`, nato uvoz.
- Preverite, da ni tekmujočih procesov (npr. drugi uvoz), ki istočasno vpisujejo podatke.

### „Unknown database“ / napake povezave

- Preverite `.env` na strežniku (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD).  
  Glej tudi `ENV-DB-NASTAVITVE.md`.

### Uvoz se konča brez sporočila o napaki, a podatkov ni

- Preverite, da ste ukaz zagnali v pravi mapi (`/var/www/merila-app`).
- Preverite, da JSON vsebuje ključ `tables` in ustrezne tabele (npr. z  
  `head -c 500 storage/app/merila-data-export-....json`).

---

## 7. Primer celotnega poteka

```bash
# ----- LOKALNO -----
php artisan db:export-data --output=storage/app/izvoz.json

# Prenesite izvoz.json na strežnik (FileZilla, SCP, …)

# ----- NA PRODUKCIJSKEM STREŽNIKU -----
cd /var/www/merila-app

# Po potrebi sveža baza
php artisan migrate:fresh --force

# Uvoz
php artisan db:import-data storage/app/izvoz.json --force

# Po uvozu (opcijsko)
php artisan config:cache
php artisan route:cache
```

Po tem so lokalno vnešeni podatki (uporabniki, merila, dobavnice, nastavitve) na produkciji.

---

## 8. Alternativa: mysqldump (samo MySQL ↔ MySQL)

Če **tako lokalno kot na produkciji** uporabljate **MySQL** (npr. Docker/Sail lokalno, MySQL na strežniku), lahko namesto `db:export-data` uporabite **mysqldump**.

### Kdaj je to možnost?

- ✅ Lokalna baza = MySQL (npr. prek Saila)
- ✅ Produkcijska baza = MySQL
- ❌ Če lokalno uporabljate **SQLite**, mysqldump ne more iz SQLite – uporabite `db:export-data` (JSON).

### Izvoz (lokalno)

**Samo podatki (brez CREATE TABLE):**

```bash
mysqldump -u root -p --no-create-info ime_tvoje_lokalne_baze > podatki_izvoz.sql
```

Geslo vnesete, ko ga mysqldump zahteva. Ime baze vzamete iz `.env`: `DB_DATABASE` (npr. `laravel` ali `merila_local`).

**Brez cache, sessions, jobs (samo „poslovni“ podatki):**

```bash
mysqldump -u root -p --no-create-info \
  --ignore-table=ime_baze.cache \
  --ignore-table=ime_baze.cache_locks \
  --ignore-table=ime_baze.sessions \
  --ignore-table=ime_baze.jobs \
  --ignore-table=ime_baze.job_batches \
  --ignore-table=ime_baze.failed_jobs \
  --ignore-table=ime_baze.password_reset_tokens \
  --ignore-table=ime_baze.migrations \
  ime_baze > podatki_izvoz.sql
```

`ime_baze` zamenjajte z dejanskim imenom lokalne baze. Z `--ignore-table` izpustite tabele, ki jih na produkciji ne želite prepisovati.

### Uvoz (na produkciji)

1. Na strežniku naj obstaja **ista shema** (npr. z `php artisan migrate:fresh --force`).
2. Nato uvažate SQL:

```bash
mysql -u merila_user -p merila_production < podatki_izvoz.sql
```

Če dobite napake zaradi foreign key (npr. vrstni red INSERTov), izklopite preverjanje **v isti seji** kot uvoz:

```bash
mysql -u merila_user -p merila_production -e "SET FOREIGN_KEY_CHECKS=0; SOURCE /var/www/merila-app/storage/app/podatki_izvoz.sql; SET FOREIGN_KEY_CHECKS=1;"
```

Pot do `podatki_izvoz.sql` naj bo **absolutna** in na strežniku. Če je datoteka v trenutnem imeniku:

```bash
mysql -u merila_user -p merila_production -e "SET FOREIGN_KEY_CHECKS=0; SOURCE $(pwd)/podatki_izvoz.sql; SET FOREIGN_KEY_CHECKS=1;"
```

### Primerjalna tabela

|                        | `db:export-data` + `db:import-data` | `mysqldump` + `mysql`      |
|------------------------|--------------------------------------|----------------------------|
| SQLite → MySQL         | ✅ Deluje                             | ❌ Ne (mysqldump je za MySQL) |
| MySQL → MySQL          | ✅ Deluje                             | ✅ Deluje                   |
| Izločitev tabel        | Samo poslovne (po privzetem)         | Z `--ignore-table`         |
| Odvisnost od Laravela  | Da (artisan)                         | Ne (samo MySQL orodja)     |
| Format                 | JSON                                 | SQL                        |

Če imate povsod MySQL in vam ustrezajo zgornji ukazi, lahko namesto JSON izvoza/ uvoza uporabite `mysqldump` in `mysql`.
