# Preverjanje Manjkajočih Datotek

## Problem
Na produkcijskem serverju manjka `.env.example` datoteka.

## Rešitev

### 1. Sinhroniziraj `.env.example` na produkcijski server

V WinSCP kopiraj `.env.example` iz lokalnega računalnika na `/var/www/merila-app/`

### 2. Na produkcijskem serverju ustvari .env

```bash
cd /var/www/merila-app

# Kopiraj .env.example v .env
cp .env.example .env

# Generiraj APP_KEY
php artisan key:generate

# Uredi .env in nastavi MySQL podatke
nano .env
```

### 3. V .env nastavi (prilagodi):

```env
APP_NAME="Merila"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.178.153

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=merila_db
DB_USERNAME=merila_user
DB_PASSWORD=tvoje_geslo
```

### 4. Nato zaženi migracije

```bash
php artisan migrate --force
```

## Preverjanje Drugih Manjkajočih Datotek

Na produkcijskem serverju zaženi:

```bash
cd /var/www/merila-app

echo "=== Preveri osnovne datoteke ==="
test -f .env.example && echo "✓ .env.example" || echo "✗ .env.example"
test -f composer.json && echo "✓ composer.json" || echo "✗ composer.json"
test -f artisan && echo "✓ artisan" || echo "✗ artisan"
test -f package.json && echo "✓ package.json" || echo "✗ package.json"
test -f vite.config.js && echo "✓ vite.config.js" || echo "✗ vite.config.js"

echo ""
echo "=== Preveri direktorije ==="
test -d app && echo "✓ app/" || echo "✗ app/"
test -d bootstrap && echo "✓ bootstrap/" || echo "✗ bootstrap/"
test -d config && echo "✓ config/" || echo "✗ config/"
test -d database && echo "✓ database/" || echo "✗ database/"
test -d public && echo "✓ public/" || echo "✗ public/"
test -d resources && echo "✓ resources/" || echo "✗ resources/"
test -d routes && echo "✓ routes/" || echo "✗ routes/"
```
