#!/bin/bash
# Optimizacijska skripta za produkcijski server

echo "🚀 Optimiziranje Laravel aplikacije za produkcijo..."

# 1. Cache konfiguracijo
php artisan config:cache

# 2. Cache routes
php artisan route:cache

# 3. Cache views
php artisan view:cache

# 4. Optimizacija autoloader-ja
composer install --optimize-autoloader --no-dev

# 5. Optimizacija Laravel
php artisan optimize

# 6. Zagon migracije za database indexe (če še niso zagnane)
php artisan migrate --force

echo "✅ Optimizacija končana!"
echo ""
echo "📝 Preverite tudi naslednje v .env datoteki:"
echo "   APP_ENV=production"
echo "   APP_DEBUG=false"
echo "   LOG_LEVEL=error"
echo "   CACHE_DRIVER=file (ali redis če imate)"
echo "   OPCACHE_ENABLED=1 (v php.ini)"
