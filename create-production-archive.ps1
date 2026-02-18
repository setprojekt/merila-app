# Skripta za ustvarjanje arhiva datotek za produkcijo
# Uporaba: .\create-production-archive.ps1

$ErrorActionPreference = "Stop"

# Nastavitve
$ProjectRoot = $PSScriptRoot
$ArchiveName = "merila-production-$(Get-Date -Format 'yyyyMMdd-HHmmss').zip"
$ArchivePath = Join-Path $ProjectRoot $ArchiveName
$TempDir = Join-Path $ProjectRoot "temp-archive"

Write-Host "Ustvarjanje produkcijskega arhiva..." -ForegroundColor Cyan
Write-Host ""

# Preveri, ce temp direktorij ze obstaja in ga pocisti
if (Test-Path $TempDir) {
    Write-Host "Ciscenje starega temp direktorija..." -ForegroundColor Yellow
    Remove-Item -Path $TempDir -Recurse -Force
}

# Ustvari temp direktorij
New-Item -ItemType Directory -Path $TempDir -Force | Out-Null

Write-Host "Kopiranje datotek..." -ForegroundColor Cyan

# Direktoriji, ki se kopirajo v celoti
$DirectoriesToCopy = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "lang",
    "public",
    "resources",
    "routes"
)

foreach ($dir in $DirectoriesToCopy) {
    $sourcePath = Join-Path $ProjectRoot $dir
    if (Test-Path $sourcePath) {
        Write-Host "  [OK] $dir" -ForegroundColor Green
        $destPath = Join-Path $TempDir $dir
        Copy-Item -Path $sourcePath -Destination $destPath -Recurse -Force
    }
}

# Storage direktorij - kopiraj samo strukturo in .gitignore datoteke
Write-Host "  [OK] storage (samo struktura)" -ForegroundColor Green
$storageSource = Join-Path $ProjectRoot "storage"
$storageDest = Join-Path $TempDir "storage"
if (Test-Path $storageSource) {
    # Ustvari storage strukturo
    New-Item -ItemType Directory -Path $storageDest -Force | Out-Null
    
    # Kopiraj vse .gitignore datoteke in strukturo
    Get-ChildItem -Path $storageSource -Recurse -File -Filter ".gitignore" | ForEach-Object {
        $relativePath = $_.FullName.Substring($storageSource.Length + 1)
        $destFile = Join-Path $storageDest $relativePath
        $destDir = Split-Path $destFile -Parent
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        Copy-Item -Path $_.FullName -Destination $destFile -Force
    }
    
    # Ustvari potrebne poddirektorije
    $storageDirs = @(
        "app\public",
        "app\private",
        "framework\cache\data",
        "framework\sessions",
        "framework\views",
        "logs"
    )
    
    foreach ($subDir in $storageDirs) {
        $fullPath = Join-Path $storageDest $subDir
        if (-not (Test-Path $fullPath)) {
            New-Item -ItemType Directory -Path $fullPath -Force | Out-Null
        }
    }
}

# Posamezne datoteke
$FilesToCopy = @(
    "artisan",
    "composer.json",
    "composer.lock",
    "package.json",
    "vite.config.js",
    ".env.example",
    "deploy.sh",
    "optimize-production.sh"
)

foreach ($file in $FilesToCopy) {
    $sourcePath = Join-Path $ProjectRoot $file
    if (Test-Path $sourcePath) {
        Write-Host "  [OK] $file" -ForegroundColor Green
        Copy-Item -Path $sourcePath -Destination $TempDir -Force
    }
}

# Dokumentacija - kopiraj samo deployment navodila
Write-Host "  [OK] dokumentacija (deployment)" -ForegroundColor Green
$docsToCopy = @(
    "README.md",
    "DEPLOYMENT.md",
    "DEPLOYMENT_CHECKLIST.md",
    "PRODUCTION-OPTIMIZATION.md",
    "HITRA-NAMESTITEV.md",
    "SETUP.md"
)

$docsDir = Join-Path $TempDir "docs"
New-Item -ItemType Directory -Path $docsDir -Force | Out-Null

foreach ($doc in $docsToCopy) {
    $sourcePath = Join-Path $ProjectRoot $doc
    if (Test-Path $sourcePath) {
        Copy-Item -Path $sourcePath -Destination $docsDir -Force
    }
}

# Ciscenje - odstrani nepotrebne datoteke iz arhiva
Write-Host ""
Write-Host "Ciscenje nepotrebnih datotek..." -ForegroundColor Yellow

# Odstrani .git direktorije, ce obstajajo
Get-ChildItem -Path $TempDir -Recurse -Directory -Filter ".git" -ErrorAction SilentlyContinue | Remove-Item -Recurse -Force

# Odstrani node_modules, ce so bili kopirani
Get-ChildItem -Path $TempDir -Recurse -Directory -Filter "node_modules" -ErrorAction SilentlyContinue | Remove-Item -Recurse -Force

# Odstrani vendor, ce je bil kopiran
Get-ChildItem -Path $TempDir -Recurse -Directory -Filter "vendor" -ErrorAction SilentlyContinue | Remove-Item -Recurse -Force

# Odstrani cache datoteke
Get-ChildItem -Path $TempDir -Recurse -File -Filter "*.cache" -ErrorAction SilentlyContinue | Remove-Item -Force
Get-ChildItem -Path $TempDir -Recurse -File -Filter ".phpstorm.meta.php" -ErrorAction SilentlyContinue | Remove-Item -Force

# Odstrani .env datoteke (razen .env.example)
Get-ChildItem -Path $TempDir -Recurse -File -Filter ".env" -ErrorAction SilentlyContinue | Where-Object { $_.Name -ne ".env.example" } | Remove-Item -Force

# Ustvari ZIP arhiv
Write-Host ""
Write-Host "Ustvarjanje ZIP arhiva..." -ForegroundColor Cyan

# Odstrani stari arhiv, ce obstaja
if (Test-Path $ArchivePath) {
    Remove-Item -Path $ArchivePath -Force
}

# Ustvari ZIP arhiv
Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($TempDir, $ArchivePath)

# Izracunaj velikost arhiva
$archiveSize = (Get-Item $ArchivePath).Length
$archiveSizeMB = [math]::Round($archiveSize / 1MB, 2)

# Pocisti temp direktorij
Write-Host "Ciscenje temp direktorija..." -ForegroundColor Yellow
Remove-Item -Path $TempDir -Recurse -Force

Write-Host ""
Write-Host "Arhiv uspesno ustvarjen!" -ForegroundColor Green
Write-Host ""
Write-Host "Arhiv: $ArchiveName" -ForegroundColor Cyan
Write-Host "Velikost: $archiveSizeMB MB" -ForegroundColor Cyan
Write-Host "Lokacija: $ArchivePath" -ForegroundColor Cyan
Write-Host ""
Write-Host "Navodila za namestitev na produkciji:" -ForegroundColor Yellow
Write-Host "   1. Razsiri arhiv na produkcijskem serverju"
Write-Host "   2. Namesti odvisnosti: composer install --no-dev --optimize-autoloader"
Write-Host "   3. Namesti NPM pakete: npm install && npm run build"
Write-Host "   4. Kopiraj .env.example v .env in nastavi vrednosti"
Write-Host "   5. Zazeni migracije: php artisan migrate --force"
Write-Host "   6. Zazeni optimizacije: ./optimize-production.sh"
Write-Host "   7. Nastavi pravilne dovoljenja za storage/ in bootstrap/cache/"
Write-Host ""
