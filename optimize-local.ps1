# Optimizacija za lokalni razvoj
Write-Host "=== Optimizacija lokalnega razvojnega okolja ===" -ForegroundColor Cyan

# Čiščenje cachev
Write-Host "`nČiščenje cache-jev..." -ForegroundColor Yellow
docker compose exec laravel.test php artisan config:clear
docker compose exec laravel.test php artisan route:clear
docker compose exec laravel.test php artisan view:clear
docker compose exec laravel.test php artisan cache:clear

# Optimizacija autoloaderja
Write-Host "`nOptimizacija autoloaderja..." -ForegroundColor Yellow
docker compose exec laravel.test composer dump-autoload -o

Write-Host "`n=== Optimizacija končana! ===" -ForegroundColor Green
Write-Host "`nNasvet: Za najboljšo odzivnost zagotovite, da:" -ForegroundColor White
Write-Host "  1. Vite dev server teče (npm run dev)" -ForegroundColor White
Write-Host "  2. Redis je vklopljen za cache" -ForegroundColor White
Write-Host "  3. Database query-ji uporabljajo indexe" -ForegroundColor White
