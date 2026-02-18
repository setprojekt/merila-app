# ✅ Checklist za Namestitev

Uporabite ta checklist, da sledite napredku pri namestitvi.

## Predpogoji

- [ ] Docker Desktop je nameščen
- [ ] Docker Desktop je zagnan (zelena ikona v system tray)
- [ ] Terminal (PowerShell/CMD) je odprt
- [ ] Ste v pravilnem direktoriju: `c:\Projekt\merila 37.001`

## Namestitev

### Korak 1: Docker Setup
- [ ] Preveril sem Docker verzijo: `docker --version`
- [ ] Kopiral sem `.env.example` v `.env`
- [ ] Zažel sem Docker kontejnerje: `docker compose up -d`
- [ ] Preveril sem, da so vsi kontejnerji zagnani: `docker compose ps`
  - [ ] laravel.test je "Up"
  - [ ] mysql je "Up"
  - [ ] redis je "Up"
  - [ ] mailpit je "Up"

### Korak 2: Composer Paketi
- [ ] Namestil sem Composer pakete: `docker compose exec laravel.test composer install`
- [ ] Preveril sem, da ni napak pri namestitvi

### Korak 3: Laravel Setup
- [ ] Generiral sem aplikacijski ključ: `docker compose exec laravel.test php artisan key:generate`
- [ ] Preveril sem, da je `APP_KEY` v `.env` datoteki zapolnjen

### Korak 4: Filament
- [ ] Namestil sem Filament: `docker compose exec laravel.test php artisan filament:install --panels`
- [ ] Vnesel sem podatke za admin uporabnika:
  - [ ] Username: ___________
  - [ ] Email: ___________
  - [ ] Password: ___________

### Korak 5: Baza Podatkov
- [ ] Zažel sem migracije: `docker compose exec laravel.test php artisan migrate`
- [ ] Preveril sem, da so vse migracije uspešne

### Korak 6: Preverjanje
- [ ] Odprl sem http://localhost v brskalniku
- [ ] Odprl sem http://localhost/admin (Filament login)
- [ ] Prijavil sem se z admin uporabnikom
- [ ] Vidim Dashboard z widgeti
- [ ] Vidim navigacijo (Merila, Dobavnice)

### Korak 7: Testiranje (Opcionalno)
- [ ] Odprl sem http://localhost:8025 (Mailpit)
- [ ] Namestil sem NPM pakete: `docker compose exec laravel.test npm install`
- [ ] Testiral sem email scheduler: `docker compose exec laravel.test php artisan instruments:send-reminders`

## Funkcionalnosti za Testiranje

### Merila
- [ ] Ustvaril sem novo merilo
- [ ] Vidim seznam meril z barvnim kodiranjem
- [ ] Testiral sem filtre (status, pretečeno, opozorilo)

### Bulk Action
- [ ] Izbral sem več meril
- [ ] Kliknil sem "Pošlji na kontrolo"
- [ ] Vnesel sem prejemnika
- [ ] Preveril sem, da se je dobavnica ustvarila
- [ ] Preveril sem, da so statusi meril spremenjeni v "V_KONTROLI"

### Dobavnice
- [ ] Odprl sem novo dobavnico
- [ ] Vidim merila na dobavnici
- [ ] Testiral sem vračilo meril:
  - [ ] Označil sem "Vrnjeno"
  - [ ] Vnesel sem datum pregleda
  - [ ] Izbral sem rezultat kontrole
  - [ ] Preveril sem, da se je status merila posodobil
- [ ] Preveril sem, da se dobavnica avtomatsko zaključi, ko so vsa merila vrnjena

### Tiskanje
- [ ] Kliknil sem "Natisni" na dobavnici
- [ ] Odprl se je print preview
- [ ] Testiral sem tiskanje iz brskalnika (Ctrl+P)

## Troubleshooting

Če imate težave, preverite:

- [ ] Docker kontejnerji tečejo: `docker compose ps`
- [ ] Loge za napake: `docker compose logs laravel.test`
- [ ] MySQL deluje: `docker compose logs mysql`
- [ ] Pravice za storage: `docker compose exec laravel.test chmod -R 775 storage`

## Opombe

- Datum namestitve: ___________
- Uporabljena verzija Docker: ___________
- Uporabljena verzija PHP: `docker compose exec laravel.test php -v`
- Uporabljena verzija Composer: `docker compose exec laravel.test composer --version`

---

**Ko so vsi koraki dokončani, je aplikacija pripravljena za uporabo! 🎉**
