# Ukazi za Produkcijski Server

## Korak 1: Pojdi v direktorij aplikacije

```bash
cd /var/www/merila-app
```

## Korak 2: Ustvari potrebne direktorije

```bash
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
```

## Korak 3: Nastavi dovoljenja

**Če uporabljaš uporabnika `www-data` (standardno za web server):**
```bash
sudo chmod -R 775 bootstrap/cache storage
sudo chown -R www-data:www-data bootstrap/cache storage
```

**Ali če uporabljaš uporabnika `upravitelj`:**
```bash
sudo chmod -R 775 bootstrap/cache storage
sudo chown -R upravitelj:upravitelj bootstrap/cache storage
```

## Korak 4: Preveri, da so direktoriji ustvarjeni

```bash
test -d bootstrap/cache && echo "✓ bootstrap/cache" || echo "✗ bootstrap/cache"
test -d storage/framework/cache/data && echo "✓ storage/framework/cache/data" || echo "✗ storage/framework/cache/data"
test -d storage/logs && echo "✓ storage/logs" || echo "✗ storage/logs"
```

## Korak 5: Zaženi composer install

```bash
composer install --no-dev --optimize-autoloader
```

## Korak 6: Če je composer install uspešen, zaženi deploy

```bash
./deploy.sh --no-git
```

---

## VSE NAJENKRAT (kopiraj in prilepi)

```bash
cd /var/www/merila-app && \
mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs && \
sudo chmod -R 775 bootstrap/cache storage && \
sudo chown -R www-data:www-data bootstrap/cache storage && \
composer install --no-dev --optimize-autoloader && \
./deploy.sh --no-git
```

**Ali če uporabljaš uporabnika `upravitelj`:**

```bash
cd /var/www/merila-app && \
mkdir -p bootstrap/cache storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs && \
sudo chmod -R 775 bootstrap/cache storage && \
sudo chown -R upravitelj:upravitelj bootstrap/cache storage && \
composer install --no-dev --optimize-autoloader && \
./deploy.sh --no-git
```

---

## Troubleshooting

### Če dobiš "Permission denied" pri chown:

Preveri, kateri uporabnik teče web server:
```bash
ps aux | grep -E 'apache|nginx|php-fpm' | head -1
```

Nato uporabi tega uporabnika v chown ukazu.

### Če composer install še vedno ne deluje:

Preveri, da je `composer.json` posodobljen:
```bash
grep "laravel-pdf" composer.json
```

Morali bi videti: `"spatie/laravel-pdf": "^1.0"`

Če vidiš `^2.0`, moraš sinhronizirati popravljen `composer.json` iz lokalnega računalnika.
