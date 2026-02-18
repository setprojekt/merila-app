# Ustvarjene Datoteke

## Problem
`composer.json` in `artisan` datoteki nista bili v root direktoriju projekta, kar je povzročalo napako pri zagonu `deploy.sh` na produkcijskem serverju.

## Rešitev
Ustvaril sem manjkajoče datoteke:

### 1. `composer.json`
- Osnovna konfiguracija za Laravel 11
- Vključuje FilamentPHP v3
- Vključuje Spatie pakete (activitylog, permission, settings, pdf)
- Nastavljen za PHP 8.2+

### 2. `artisan`
- Laravel Artisan CLI datoteka
- Potrebuje `bootstrap/app.php` za delovanje

## Naslednji Koraki

### Na Lokalnem Računalniku

1. **Sinhroniziraj datoteki na produkcijski server:**
   - V WinSCP sinhroniziraj `composer.json` in `artisan`
   - Ali uporabi `create-production-archive.ps1` za ustvarjanje novega arhiva

2. **Preveri, da so datoteke na mestu:**
   ```bash
   # Na produkcijskem serverju
   cd /var/www/merila-app
   ls -la composer.json artisan
   ```

### Na Produkcijskem Serverju

Po sinhronizaciji:

```bash
cd /var/www/merila-app

# Preveri, da datoteke obstajajo
ls -la composer.json artisan

# Naredi artisan izvedljivo
chmod +x artisan

# Zaženi deploy
./deploy.sh --no-git
```

## Opomba

Če `bootstrap/app.php` tudi ne obstaja, ga bo treba ustvariti. Preveri z:
```bash
test -f bootstrap/app.php && echo "OK" || echo "MANJKA"
```

## Preverjanje

Za hitro preverjanje na strežniku:
```bash
cd /var/www/merila-app
echo "=== Preveri datoteke ==="
test -f composer.json && echo "✓ composer.json" || echo "✗ composer.json"
test -f artisan && echo "✓ artisan" || echo "✗ artisan"
test -f bootstrap/app.php && echo "✓ bootstrap/app.php" || echo "✗ bootstrap/app.php"
```
