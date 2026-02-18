# SET Intranet - Modularna Aplikacija

Modularna spletna aplikacija za interno uporabo (Intranet) s trenutno implementiranim modulom za vodenje meril (70.0001).

## Moduli

- **Merila (70.0001)** - Centralizirano vodenje meril, opozarjanje na potek veljavnosti in generiranje dobavnic za kontrolo

## Tehnološki Stack

- **Framework:** Laravel 11
- **Admin Panel:** FilamentPHP v3
- **Okolje:** Docker (Laravel Sail)
- **Baza:** MySQL 8.0
- **Cache/Queue:** Redis
- **Email Testing:** Mailpit

## Hitri Začetek

> **⚠️ Za podrobna navodila korak za korakom glej [NAMESTITEV-PODROBNO.md](NAMESTITEV-PODROBNO.md)**

### 1. Kopiraj .env datoteko
```bash
cp .env.example .env
```

### 2. Zaženi Docker kontejnerje
```bash
docker compose up -d
```

### 3. Namesti pakete
```bash
docker compose exec laravel.test composer install
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan filament:install --panels
docker compose exec laravel.test php artisan migrate
```

### 4. Ustvari admin uporabnika
```bash
docker compose exec laravel.test php artisan make:filament-user
```

Aplikacija bo dostopna na: **http://localhost/admin**

## Funkcionalnosti

### ✅ Implementirano

- **Upravljanje meril**: CRUD operacije za merila
- **Semafor logika**: Barvno kodiranje glede na datum poteka
- **Bulk Action "Pošlji na kontrolo"**: Avtomatsko kreiranje dobavnic
- **Upravljanje dobavnic**: Kreiranje, urejanje, vračilo meril
- **Dashboard widgeti**: Statistike meril
- **Email Scheduler**: Dnevno opozarjanje (ob 08:00)
- **Tiskanje**: Print-friendly views za dobavnice

### ⚠️ Delno Implementirano

- **Email pošiljanje**: Console command pripravljen, email template še ni
- **PDF generiranje**: Print views pripravljene, spatie/laravel-pdf še ni uporabljen

## Struktura Modulov

```
Modules/
├── Instruments/      # Modul za merila
├── DeliveryNotes/    # Modul za dobavnice
├── Settings/         # Modul za nastavitve
└── Notifications/    # Modul za obveščanje
```

## Dokumentacija

- **[Navodila za namestitev](NAVODILA.md)** - Podrobna navodila za setup
- **[Navodila za aplikacijo](gradivo/navodila%20za%20aplikacijo.md)** - Specifikacija projekta
- **[Strategija za tiskanje](gradivo/print-strategija.md)** - Print funkcionalnost
- **[Status implementacije](IMPLEMENTACIJA.md)** - Kaj je dokončano

## Razvoj

### Artisan ukazi
```bash
docker compose exec laravel.test php artisan <command>
```

### Composer ukazi
```bash
docker compose exec laravel.test composer <command>
```

### NPM ukazi
```bash
docker compose exec laravel.test npm <command>
```

### Testiranje email schedulerja
```bash
docker compose exec laravel.test php artisan instruments:send-reminders
```

## Licenca

MIT
