# Rešitev: bootstrap/cache mora obstajati in biti zapisljiv

## Problem
```
The /var/www/merila-app/bootstrap/cache directory must be present and writable.
```

## Rešitev

### 1. Ustvari direktorij in nastavi dovoljenja

Na produkcijskem serverju zaženi:

```bash
cd /var/www/merila-app

# Ustvari bootstrap/cache direktorij, če ne obstaja
mkdir -p bootstrap/cache

# Nastavi dovoljenja
sudo chmod -R 775 bootstrap/cache
sudo chown -R www-data:www-data bootstrap/cache
# ali če uporabljaš uporabnika 'upravitelj':
sudo chown -R upravitelj:upravitelj bootstrap/cache
```

### 2. Preveri strukturo bootstrap direktorija

```bash
# Preveri, da direktorij obstaja
ls -la bootstrap/

# Preveri dovoljenja
ls -ld bootstrap/cache
```

Morali bi videti nekaj podobnega:
```
drwxrwxr-x 2 www-data www-data 4096 ... cache
```

### 3. Ponovno zaženi composer install

```bash
cd /var/www/merila-app
composer install --no-dev --optimize-autoloader
```

### 4. Če še vedno ne deluje, preveri celotno strukturo

```bash
cd /var/www/merila-app

# Preveri, da bootstrap direktorij obstaja
test -d bootstrap && echo "✓ bootstrap" || echo "✗ bootstrap"

# Preveri, da bootstrap/cache obstaja
test -d bootstrap/cache && echo "✓ bootstrap/cache" || echo "✗ bootstrap/cache"

# Preveri dovoljenja
ls -ld bootstrap/cache
```

### 5. Ustvari tudi storage direktorije (če manjkajo)

Laravel potrebuje tudi storage direktorije:

```bash
cd /var/www/merila-app

# Ustvari storage strukturo
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Nastavi dovoljenja za storage
sudo chmod -R 775 storage
sudo chown -R www-data:www-data storage
# ali
sudo chown -R upravitelj:upravitelj storage
```

## Popolna Skripta za Nastavitev

Za hitro nastavitev zaženi:

```bash
cd /var/www/merila-app

# Ustvari vse potrebne direktorije
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Nastavi dovoljenja (prilagodi uporabnika)
sudo chmod -R 775 bootstrap/cache storage
sudo chown -R www-data:www-data bootstrap/cache storage
# ali
sudo chown -R upravitelj:upravitelj bootstrap/cache storage

# Ponovno zaženi composer
composer install --no-dev --optimize-autoloader
```

## Preverjanje

Po nastavitvi preveri:

```bash
cd /var/www/merila-app

echo "=== Preveri direktorije ==="
test -d bootstrap/cache && echo "✓ bootstrap/cache" || echo "✗ bootstrap/cache"
test -d storage/framework/cache/data && echo "✓ storage/framework/cache/data" || echo "✗ storage/framework/cache/data"
test -d storage/logs && echo "✓ storage/logs" || echo "✗ storage/logs"

echo ""
echo "=== Preveri dovoljenja ==="
ls -ld bootstrap/cache
ls -ld storage
```
