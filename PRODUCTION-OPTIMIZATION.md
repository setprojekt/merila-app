# Optimizacije za Produkcijski Server

> **Pomembno:** Za razlago kaj sta Redis in CDN, glej datoteko `REDIS-IN-CDN-RAZLAGA.md`

## 🎯 Razlike med Development in Production

### Development okolje (trenutno):
- ❌ APP_DEBUG=true - prikazuje debug informacije (počasneje)
- ❌ LOG_LEVEL=debug - beleži veliko podatkov
- ❌ Brez cache-iranja config/routes/views
- ❌ Brez optimizacije autoloader-ja
- ❌ Widget izvaja 3 poizvedbe na vsakem naložanju

### Production okolje (po optimizacijah):
- ✅ APP_DEBUG=false
- ✅ LOG_LEVEL=error
- ✅ Cache-irani config/routes/views
- ✅ Optimiziran autoloader
- ✅ Widget uporablja cache (5 minut) in eno poizvedbo

## 📋 Koraki za Optimizacijo

### 1. Optimiziraj Widget (✅ že narejeno)
Widget `InstrumentsStatsOverview` je optimiziran:
- Uporablja cache (5 minut)
- Ena poizvedba namesto 3
- Cache se avtomatsko osveži ob spremembah

### 2. Zagon Optimizacijske Skripte

**Na Windows (Docker):**
```powershell
.\optimize-production.ps1
```

**Na Linux/Produkcijskem serverju:**
```bash
chmod +x optimize-production.sh
./optimize-production.sh
```

### 3. Nastavitve v .env datoteki

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error

# Cache driver (za najboljšo hitrost uporabi redis, če ga imate)
CACHE_DRIVER=file
# CACHE_DRIVER=redis  # Če imate Redis

# Session driver
SESSION_DRIVER=file
# SESSION_DRIVER=redis  # Če imate Redis

# Database
DB_CONNECTION=mysql
```

### 4. PHP Opcache (za najboljšo hitrost)

V `php.ini` ali `php-fpm.conf`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  # V production
opcache.revalidate_freq=0
opcache.fast_shutdown=1
```

### 5. Database Optimizacije (✅ že narejeno)

Migracija z database indexi je pripravljena:
```bash
php artisan migrate
```

To bo dodalo indexe na:
- `instruments.name`, `type`, `location`, `department`, `archived`
- `delivery_notes.recipient`, `delivery_date`, `created_at`
- Kombinirani indexi za pogoste kombinacije

### 6. Web Server Optimizacije

**Nginx:**
```nginx
# Gzip kompresija
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

# Cache statičnih datotek
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

**Apache (.htaccess):**
```apache
# Gzip kompresija
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Cache statičnih datotek
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
</IfModule>
```

## 📊 Pričakovane Izboljšave Hitrosti

| Komponenta | Development | Production | Izboljšava |
|------------|-------------|------------|------------|
| Dashboard naložanje | ~3-5s | ~0.5-1s | **5x hitreje** |
| Seznam meril | ~2-4s | ~0.3-0.8s | **5x hitreje** |
| Urejanje merila | ~1-2s | ~0.2-0.5s | **4x hitreje** |
| Shranjevanje | ~0.5-1s | ~0.1-0.3s | **3x hitreje** |

## 🔍 Dodatne Optimizacije (Opcijsko)

### Redis Cache (za še boljšo hitrost)
```bash
# Namestite Redis
composer require predis/predis

# V .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### CDN za statične datoteke
- Uporabite CloudFlare ali podobno za statične datoteke
- Laravel Vite že optimizira asset-e

### Database Connection Pooling
- Za večje obremenitve uporabite connection pooling
- PgBouncer za PostgreSQL ali ProxySQL za MySQL

## ⚠️ Pomembno

Po vsaki spremembi kode v production:
```bash
php artisan optimize:clear  # Očisti cache
php artisan optimize        # Ponovno optimiziraj
```

Ob posodobitvah routes ali config:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
