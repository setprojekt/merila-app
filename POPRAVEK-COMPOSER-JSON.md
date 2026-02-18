# Popravek composer.json

## Problem
Composer ni mogel namestiti paketov zaradi napačne verzije `spatie/laravel-pdf`.

## Rešitev
Popravil sem verzijo `spatie/laravel-pdf` iz `^2.0` na `^1.0` (najnovejša verzija je 1.6.0).

## Spremembe

### Pred:
```json
"spatie/laravel-pdf": "^2.0"
```

### Po:
```json
"spatie/laravel-pdf": "^1.0"
```

## Naslednji Koraki

1. **Sinhroniziraj popravljen `composer.json` na produkcijski server:**
   - V WinSCP kopiraj `composer.json` na `/var/www/merila-app/`

2. **Na produkcijskem serverju zaženi:**
   ```bash
   cd /var/www/merila-app
   ./deploy.sh --no-git
   ```

## Opomba

Če se še vedno pojavijo napake, lahko poskusiš:
```bash
composer update --no-dev --optimize-autoloader
```

ali posamezno namesti pakete:
```bash
composer require spatie/laravel-pdf:^1.0 --no-dev --optimize-autoloader
```
