# Optimizacijska PowerShell skripta za produkcijski server

Write-Host "🚀 Optimiziranje Laravel aplikacije za produkcijo..." -ForegroundColor Cyan

# 1. Cache konfiguracijo
docker compose exec laravel.test php artisan config:cache

# 2. Cache routes
docker compose exec laravel.test php artisan route:cache

# 3. Cache views
docker compose exec laravel.test php artisan view:cache

# 4. Optimizacija autoloader-ja
docker compose exec laravel.test composer install --optimize-autoloader --no-dev

# 5. Optimizacija Laravel
docker compose exec laravel.test php artisan optimize

# 6. Zagon migracije za database indexe (če še niso zagnane)
docker compose exec laravel.test php artisan migrate --force

Write-Host "✅ Optimizacija končana!" -ForegroundColor Green
Write-Host ""
Write-Host "📝 Preverite tudi naslednje v .env datoteki:" -ForegroundColor Yellow
Write-Host "   APP_ENV=production"
Write-Host "   APP_DEBUG=false"
Write-Host "   LOG_LEVEL=error"
Write-Host "   CACHE_DRIVER=file (ali redis če imate)"
Write-Host "   OPCACHE_ENABLED=1 (v php.ini)"
