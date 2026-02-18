# Podrobna Navodila za Namestitev

## Predpogoji

Preverite, da imate nameščeno:
- ✅ **Docker Desktop** (mora biti zagnan)
- ✅ **Git** (opcijsko, če uporabljate verzioniranje)

## Korak 1: Preverite Docker

### 1.1 Odprite Docker Desktop
- Zaženite Docker Desktop aplikacijo
- Počakajte, da se Docker zažene (ikonka v system tray mora biti zelena)

### 1.2 Preverite, da Docker deluje
Odprite PowerShell ali Command Prompt in zaženite:
```powershell
docker --version
```
Morali bi videti verzijo Docker (npr. `Docker version 24.0.0`)

## Korak 2: Odprite Terminal v Projektu

### 2.1 Navigirajte v projekt direktorij
```powershell
cd "c:\Projekt\merila 37.001"
```

### 2.2 Preverite, da ste v pravilnem direktoriju
```powershell
dir
```
Morali bi videti datoteke kot so: `composer.json`, `docker-compose.yml`, `artisan`, itd.

## Korak 3: Kopirajte .env Datoteko

### 3.1 Preverite, ali .env že obstaja
```powershell
Test-Path .env
```
Če vrne `False`, morate kopirati `.env.example` v `.env`

### 3.2 Kopirajte .env.example v .env
```powershell
Copy-Item .env.example .env
```

### 3.3 Preverite, da je .env ustvarjen
```powershell
Test-Path .env
```
Sedaj bi moralo vrniti `True`

## Korak 4: Zaženite Docker Kontejnerje

### 4.1 Zaženite Docker Compose
```powershell
docker compose up -d
```

**Kaj se dogaja:**
- Docker bo začel prenašati potrebne slike (prvič lahko traja 5-10 minut)
- Ustvaril bo kontejnerje: `laravel.test`, `mysql`, `redis`, `mailpit`
- `-d` pomeni "detached mode" (teče v ozadju)

**Pričakovani izhod:**
```
[+] Running 5/5
 ✔ Container merila-app-mysql-1      Started
 ✔ Container merila-app-redis-1       Started
 ✔ Container merila-app-mailpit-1    Started
 ✔ Container merila-app-laravel.test-1 Started
```

### 4.2 Preverite, da so kontejnerji zagnani
```powershell
docker compose ps
```

**Pričakovani izhod:**
Morali bi videti 4 kontejnerje z statusom "Up":
```
NAME                        STATUS
merila-app-laravel.test-1   Up
merila-app-mysql-1          Up
merila-app-redis-1          Up
merila-app-mailpit-1        Up
```

### 4.3 Če se kontejnerji ne zaženejo
```powershell
# Zaustavite vse
docker compose down

# Zaženite znova
docker compose up -d

# Preverite loge za napake
docker compose logs laravel.test
```

## Korak 5: Namestite Composer Pakete

### 5.1 Namestite pakete
```powershell
docker compose exec laravel.test composer install
```

**Kaj se dogaja:**
- Composer bo prenesel vse PHP pakete (Laravel, Filament, itd.)
- Prvič lahko traja 5-10 minut
- Videli boste seznam nameščenih paketov

**Pričakovani izhod:**
```
Loading composer repositories with package information
Installing dependencies from lock file
...
Package operations: XX installs, 0 updates, 0 removals
```

### 5.2 Če pride do napake
```powershell
# Počistite cache
docker compose exec laravel.test composer clear-cache

# Poskusite znova
docker compose exec laravel.test composer install --no-cache
```

## Korak 6: Generirajte Aplikacijski Ključ

### 6.1 Generirajte ključ
```powershell
docker compose exec laravel.test php artisan key:generate
```

**Pričakovani izhod:**
```
Application key set successfully.
```

### 6.2 Preverite .env datoteko
Odprite `.env` datoteko in preverite, da je vrstica `APP_KEY=` sedaj zapolnjena:
```
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

## Korak 7: Namestite Filament

### 7.1 Zaženite Filament installer
```powershell
docker compose exec laravel.test php artisan filament:install --panels
```

**Interaktivni vprašanja:**
1. **Panel ID:** Pritisnite `Enter` za privzeto (`admin`) ali vnesite drugo ime
2. **Username:** Vnesite vaše uporabniško ime (npr. `admin`)
3. **Email:** Vnesite vaš email (npr. `admin@example.com`)
4. **Password:** Vnesite geslo (najmanj 8 znakov)

**Pričakovani izhod:**
```
Panel ID [admin]:
Username:
Email:
Password:
...
Filament installed successfully!
```

### 7.2 Če pride do napake
```powershell
# Preverite, ali so migracije že zagnane
docker compose exec laravel.test php artisan migrate:status

# Če ne, zaženite migracije najprej
docker compose exec laravel.test php artisan migrate
```

## Korak 8: Zaženite Migracije

### 8.1 Zaženite migracije
```powershell
docker compose exec laravel.test php artisan migrate
```

**Kaj se dogaja:**
- Laravel bo ustvaril vse tabele v bazi podatkov
- Videli boste seznam migracij

**Pričakovani izhod:**
```
Running migrations...
2024_01_01_000000_create_users_table ................................. DONE
2024_01_01_000001_create_instruments_table .......................... DONE
2024_01_01_000002_create_delivery_notes_table ....................... DONE
2024_01_01_000003_create_delivery_note_items_table .................. DONE
```

### 8.2 Če pride do napake z bazo
```powershell
# Preverite, ali MySQL kontejner teče
docker compose ps mysql

# Preverite MySQL loge
docker compose logs mysql

# Če je problem, zaustavite in zaženite znova
docker compose restart mysql
```

## Korak 9: Ustvarite Admin Uporabnika (Če Niste v Koraku 7)

### 9.1 Ustvarite uporabnika
```powershell
docker compose exec laravel.test php artisan make:filament-user
```

**Interaktivni vprašanja:**
1. **Name:** Vaše ime (npr. `Administrator`)
2. **Email:** Vaš email
3. **Password:** Vaše geslo
4. **Role:** `admin` ali `user`

**Pričakovani izhod:**
```
Name:
Email:
Password:
Role [admin]:
User created successfully!
```

## Korak 10: Preverite, da Aplikacija Deluje

### 10.1 Odprite brskalnik
Pojdite na: **http://localhost**

Morali bi videti Laravel welcome stran ali preusmeritev na `/admin`

### 10.2 Odprite Filament Admin Panel
Pojdite na: **http://localhost/admin**

Morali bi videti Filament login stran.

### 10.3 Prijavite se
- Uporabite email in geslo, ki ste jih nastavili v koraku 7 ali 9
- Morali bi biti preusmerjeni na Dashboard

### 10.4 Preverite Mailpit (Email Testing)
Pojdite na: **http://localhost:8025**

Morali bi videti Mailpit dashboard za testiranje emailov.

## Korak 11: Namestite NPM Pakete (Opcionalno)

### 11.1 Namestite NPM pakete
```powershell
docker compose exec laravel.test npm install
```

### 11.2 Zgradite assets (za produkcijo)
```powershell
docker compose exec laravel.test npm run build
```

### 11.3 Za razvoj (watch mode)
```powershell
docker compose exec laravel.test npm run dev
```

## Troubleshooting

### Problem: Docker kontejnerji se ne zaženejo

**Rešitev:**
```powershell
# Zaustavite vse
docker compose down

# Preverite, ali so porti zasedeni
netstat -ano | findstr :80
netstat -ano | findstr :3306

# Če so zasedeni, spremenite port v .env:
# APP_PORT=8080
# FORWARD_DB_PORT=3307

# Zaženite znova
docker compose up -d
```

### Problem: "Connection refused" pri MySQL

**Rešitev:**
```powershell
# Preverite MySQL loge
docker compose logs mysql

# Počakajte, da se MySQL zažene (lahko traja 30 sekund)
# Preverite health status
docker compose ps mysql
```

### Problem: Composer paketi se ne namestijo

**Rešitev:**
```powershell
# Počistite cache
docker compose exec laravel.test composer clear-cache

# Poskusite z --no-cache
docker compose exec laravel.test composer install --no-cache

# Preverite, ali imate dovolj prostora na disku
```

### Problem: "Permission denied" napake

**Rešitev:**
```powershell
# Nastavite pravice (v Docker kontejnerju)
docker compose exec laravel.test chmod -R 775 storage bootstrap/cache
docker compose exec laravel.test chown -R sail:sail storage bootstrap/cache
```

### Problem: Migracije ne tečejo

**Rešitev:**
```powershell
# Preverite status migracij
docker compose exec laravel.test php artisan migrate:status

# Če je problem, zaženite fresh migracije (POZOR: izbriše podatke!)
docker compose exec laravel.test php artisan migrate:fresh

# Ali pa ročno preverite bazo
docker compose exec laravel.test php artisan tinker
# V tinker: DB::connection()->getPdo();
```

### Problem: Filament se ne naloži

**Rešitev:**
```powershell
# Preverite, ali je AdminPanelProvider registriran
docker compose exec laravel.test php artisan route:list | grep admin

# Preverite config cache
docker compose exec laravel.test php artisan config:clear
docker compose exec laravel.test php artisan cache:clear
```

## Preverjanje Namestitve

### Preverite, da vse deluje:

1. ✅ Docker kontejnerji tečejo: `docker compose ps`
2. ✅ Aplikacija se naloži: http://localhost
3. ✅ Admin panel deluje: http://localhost/admin
4. ✅ Baza podatkov deluje: `docker compose exec laravel.test php artisan tinker` → `DB::connection()->getPdo();`
5. ✅ Migracije so zagnane: `docker compose exec laravel.test php artisan migrate:status`

## Naslednji Koraki

Ko je vse nameščeno:

1. **Dodajte merila**: Pojdite na Merila → Ustvari novo
2. **Testirajte workflow**: Izberite merila → Pošlji na kontrolo
3. **Ustvarite dobavnico**: Preverite, da se avtomatsko kreira
4. **Testirajte tiskanje**: Kliknite Natisni na dobavnici

## Pomoč

Če imate težave:
1. Preverite loge: `docker compose logs laravel.test`
2. Preverite MySQL loge: `docker compose logs mysql`
3. Preverite, ali so vsi kontejnerji zagnani: `docker compose ps`
4. Glej [NAVODILA.md](NAVODILA.md) za dodatne informacije
