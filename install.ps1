# PowerShell skripta za avtomatsko namestitev

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SET Merila - Avtomatska Namestitev" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Korak 1: Preveri Docker
Write-Host "[1/8] Preverjanje Docker..." -ForegroundColor Yellow
try {
    $dockerVersion = docker --version
    Write-Host "[OK] Docker namescen: $dockerVersion" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Docker ni namescen ali ni v PATH!" -ForegroundColor Red
    exit 1
}

# Korak 2: Preveri .env
Write-Host "[2/8] Preverjanje .env datoteke..." -ForegroundColor Yellow
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    Write-Host "[OK] .env datoteka kopirana" -ForegroundColor Green
} else {
    Write-Host "[OK] .env datoteka ze obstaja" -ForegroundColor Green
}

# Korak 3: Posodobi .env z manjkajocimi spremenljivkami
Write-Host "[3/8] Posodabljanje .env..." -ForegroundColor Yellow
$envContent = Get-Content .env -Raw
if ($envContent -notmatch "WWWUSER=") {
    Add-Content .env "`nWWWUSER=1000`nWWWGROUP=1000"
}
if ($envContent -notmatch "^DB_DATABASE=merila_db") {
    $envContent = $envContent -replace '# DB_DATABASE=laravel', 'DB_DATABASE=merila_db'
    $envContent = $envContent -replace '# DB_USERNAME=root', 'DB_USERNAME=sail'
    $envContent = $envContent -replace '# DB_PASSWORD=', 'DB_PASSWORD=password'
    Set-Content .env $envContent
}
Write-Host "[OK] .env posodobljen" -ForegroundColor Green

# Korak 4: Zazeni Docker kontejnerje
Write-Host "[4/8] Zaganjanje Docker kontejnerjev..." -ForegroundColor Yellow
Write-Host "To lahko traja nekaj minut (prvic)..."
docker compose up -d
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Docker kontejnerji zagnani" -ForegroundColor Green
} else {
    Write-Host "[ERROR] Napaka pri zaganjanju Docker kontejnerjev!" -ForegroundColor Red
    Write-Host "Preverite Docker Desktop in poskusite znova." -ForegroundColor Yellow
    exit 1
}

# Počakaj, da se kontejnerji zaženejo
Write-Host "Počakam 30 sekund, da se kontejnerji zaženejo..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# Preveri status
$containers = docker compose ps --format json | ConvertFrom-Json
$running = $containers | Where-Object { $_.State -eq "running" }
if ($running.Count -lt 4) {
    Write-Host "[WARNING] Nekateri kontejnerji se se zaganjajo. Počakam se 30 sekund..." -ForegroundColor Yellow
    Start-Sleep -Seconds 30
}

# Korak 5: Namesti Composer pakete
Write-Host "[5/9] Namescanje Composer paketov..." -ForegroundColor Yellow
Write-Host "To lahko traja 5-10 minut..."
docker compose exec -T laravel.test composer install --no-interaction
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Composer paketi namesceni" -ForegroundColor Green
} else {
    Write-Host "[ERROR] Napaka pri namescanju paketov!" -ForegroundColor Red
    exit 1
}

# Korak 6: Generiraj aplikacijski kljuc
Write-Host "[6/9] Generiranje aplikacijskega kljuca..." -ForegroundColor Yellow
docker compose exec -T laravel.test php artisan key:generate --force
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Aplikacijski kljuc generiran" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Napaka pri generiranju kljuca (morda ze obstaja)" -ForegroundColor Yellow
}

# Korak 7: Zazeni migracije
Write-Host "[7/9] Zaganjanje migracij..." -ForegroundColor Yellow
docker compose exec -T laravel.test php artisan migrate --force
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Migracije zagnane" -ForegroundColor Green
} else {
    Write-Host "[ERROR] Napaka pri migracijah!" -ForegroundColor Red
    Write-Host "Preverite MySQL kontejner in poskusite znova." -ForegroundColor Yellow
}

# Korak 8: Nastavi dovoljenja za storage in cache
Write-Host "[8/9] Nastavljanje dovoljenj..." -ForegroundColor Yellow
docker compose exec -T laravel.test chown -R sail:sail /var/www/html/storage /var/www/html/bootstrap/cache
docker compose exec -T laravel.test chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
Write-Host "[OK] Dovoljenja nastavljena" -ForegroundColor Green

# Korak 9: Namesti Filament
Write-Host "[9/9] Namescanje Filament..." -ForegroundColor Yellow
Write-Host ""
Write-Host "[WARNING] POZOR: To je interaktivni korak!" -ForegroundColor Yellow
Write-Host "Odgovorite na vprasanja:" -ForegroundColor Yellow
Write-Host "  - Panel ID: pritisnite Enter (privzeto: admin)" -ForegroundColor Cyan
Write-Host "  - Username: vnesite vase uporabnisko ime" -ForegroundColor Cyan
Write-Host "  - Email: vnesite vas email" -ForegroundColor Cyan
Write-Host "  - Password: vnesite vase geslo (min. 8 znakov)" -ForegroundColor Cyan
Write-Host ""
docker compose exec laravel.test php artisan filament:install --panels

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Namestitev koncana!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Aplikacija je dostopna na:" -ForegroundColor Green
Write-Host "  - http://localhost" -ForegroundColor White
Write-Host "  - http://localhost/admin (Filament)" -ForegroundColor White
Write-Host ""
Write-Host "Mailpit (email testing):" -ForegroundColor Green
Write-Host "  - http://localhost:8025" -ForegroundColor White
Write-Host ""
