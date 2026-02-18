# Navodila za Namestitev in Zagon

> **⚠️ Za podrobna navodila korak za korakom glej [NAMESTITEV-PODROBNO.md](NAMESTITEV-PODROBNO.md)**

## Predpogoji

- Docker Desktop nameščen in zagnan
- Git (opcijsko)

## Hitri Začetek

### Korak 1: Kopiraj .env datoteko

```bash
cp .env.example .env
```

## Korak 2: Zaženi Docker kontejnerje

```bash
docker compose up -d
```

Počakajte, da se vsi kontejnerji zaženejo (lahko traja nekaj minut pri prvi namestitvi).

## Korak 3: Namesti Composer pakete

```bash
docker compose exec laravel.test composer install
```

## Korak 4: Generiraj aplikacijski ključ

```bash
docker compose exec laravel.test php artisan key:generate
```

## Korak 5: Namesti Filament

```bash
docker compose exec laravel.test php artisan filament:install --panels
```

Odgovorite z:
- Panel ID: `admin` (ali pritisnite Enter za privzeto)
- Username: vaše ime
- Email: vaš email
- Password: vaše geslo

## Korak 6: Zaženi migracije

```bash
docker compose exec laravel.test php artisan migrate
```

## Korak 7: Namesti NPM pakete (opcijsko)

```bash
docker compose exec laravel.test npm install
```

## Korak 8: Zaženi aplikacijo

Aplikacija bo dostopna na: **http://localhost**

Filament admin panel: **http://localhost/admin**

Mailpit (za testiranje emailov): **http://localhost:8025**

## Uporabni Artisan Ukazi

### Ustvari admin uporabnika (če niste v koraku 5)
```bash
docker compose exec laravel.test php artisan make:filament-user
```

### Testiraj email scheduler
```bash
docker compose exec laravel.test php artisan instruments:send-reminders
```

### Zaženi scheduler (če uporabljate cron)
```bash
docker compose exec laravel.test php artisan schedule:work
```

### Clear cache
```bash
docker compose exec laravel.test php artisan cache:clear
docker compose exec laravel.test php artisan config:clear
docker compose exec laravel.test php artisan view:clear
```

## Uporaba Aplikacije

### 1. Dodajanje Meril
- Pojdite na **Merila** → **Ustvari novo**
- Izpolnite vse obvezne podatke
- Sistem bo avtomatsko izračunal `next_check_date` na podlagi `last_check_date` in `frequency_years`

### 2. Pošiljanje Meril na Kontrolo
- V seznamu meril izberite merila (checkbox)
- Kliknite **Bulk Actions** → **Pošlji na kontrolo**
- Vnesite prejemnika
- Sistem bo avtomatsko:
  - Ustvaril novo dobavnico
  - Spremenil status meril v `V_KONTROLI`
  - Dodal merila na dobavnico

### 3. Vračilo Meril
- Odprite dobavnico
- Za vsako merilo:
  - Označite **Vrnjeno**
  - Vnesite **Datum pregleda**
  - Izberite **Rezultat kontrole** (USTREZA/NE USTREZA)
  - Naložite nov certifikat (če je potreben)
- Ko so vsa merila obdelana, se dobavnica avtomatsko zaključi

### 4. Tiskanje Dobavnic
- V seznamu dobavnic kliknite **Natisni** akcijo
- Ali pa odprite dobavnico in kliknite **Natisni PDF**
- Uporabite gumb **Natisni** v brskalniku za tiskanje

## Troubleshooting

### Docker kontejnerji se ne zaženejo
```bash
docker compose down
docker compose up -d
```

### Napake pri migracijah
```bash
docker compose exec laravel.test php artisan migrate:fresh
```

### Problemi s pravili
```bash
docker compose exec laravel.test chmod -R 775 storage bootstrap/cache
docker compose exec laravel.test chown -R sail:sail storage bootstrap/cache
```

### Composer paketi se ne namestijo
```bash
docker compose exec laravel.test composer clear-cache
docker compose exec laravel.test composer install --no-cache
```

## Naslednji Koraki

1. **Email Pošiljanje**: Implementirajte email template in pošiljanje
2. **PDF Generiranje**: Namestite `spatie/laravel-pdf` in implementirajte PDF generiranje
3. **Nastavitve**: Ustvarite Settings modul z `filament/spatie-laravel-settings-plugin`
4. **Testiranje**: Testirajte vse workflow-e

## Podpora

Za vprašanja in podporo glej:
- [Navodila za aplikacijo](gradivo/navodila%20za%20aplikacijo.md)
- [Strategija za tiskanje](gradivo/print-strategija.md)
- [Status implementacije](IMPLEMENTACIJA.md)
