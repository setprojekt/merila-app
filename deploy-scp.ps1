# SCP Deployment Skripta za Windows (Alternative za RSync)
# Uporaba: .\deploy-scp.ps1

# Konfiguracija
$SERVER = "upravitelj@192.168.178.153"
$REMOTE_PATH = "/var/www/merila-app"
$LOCAL_PATH = "."

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SCP Deployment - Merila App" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Preveri, da je scp na voljo
try {
    $scpVersion = scp 2>&1 | Select-Object -First 1
    Write-Host "[OK] SCP namescen" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] SCP ni namescen!" -ForegroundColor Red
    Write-Host ""
    Write-Host "SCP je vkljucen v Windows 10/11. Preveri:" -ForegroundColor Yellow
    Write-Host "1. Odpri Windows Features in omogoci OpenSSH Client" -ForegroundColor White
    Write-Host "2. Ali uporabi FileZilla za rocni prenos" -ForegroundColor White
    Write-Host ""
    exit 1
}

# Seznam map in datotek za prenos
$ITEMS_TO_SYNC = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "lang",
    "public",
    "resources",
    "routes",
    "storage",
    "tests",
    "artisan",
    "composer.json",
    "composer.lock",
    "package.json",
    "package-lock.json",
    "vite.config.js",
    "phpunit.xml",
    ".editorconfig",
    ".gitignore",
    ".gitattributes",
    ".env.example"
)

Write-Host "[1/3] Priprava datotek za prenos..." -ForegroundColor Yellow

# Ustvari začasno mapo za prenos
$TEMP_DIR = "$env:TEMP\merila-deploy-$(Get-Date -Format 'yyyyMMddHHmmss')"
New-Item -ItemType Directory -Path $TEMP_DIR -Force | Out-Null

try {
    # Kopiraj datoteke v začasno mapo
    foreach ($item in $ITEMS_TO_SYNC) {
        $sourcePath = Join-Path $LOCAL_PATH $item
        $destPath = Join-Path $TEMP_DIR $item
        
        if (Test-Path $sourcePath) {
            if (Test-Path $sourcePath -PathType Container) {
                # Kopiraj mapo (brez cache datotek)
                $excludePatterns = @(
                    "storage\logs\*.log",
                    "storage\framework\cache\*",
                    "storage\framework\sessions\*",
                    "storage\framework\views\*",
                    "bootstrap\cache\*.php",
                    "public\build",
                    "public\hot",
                    "public\storage"
                )
                
                # Preprosta kopija (brez exclude - to je omejitev)
                Copy-Item -Path $sourcePath -Destination $destPath -Recurse -Force -ErrorAction SilentlyContinue
            } else {
                # Kopiraj datoteko
                Copy-Item -Path $sourcePath -Destination $destPath -Force -ErrorAction SilentlyContinue
            }
        }
    }
    
    Write-Host "[OK] Datoteke pripravljene" -ForegroundColor Green
    Write-Host ""
    
    Write-Host "[2/3] Prenos datotek na streznik..." -ForegroundColor Yellow
    Write-Host "   Server: $SERVER" -ForegroundColor Gray
    Write-Host "   Cilj: $REMOTE_PATH" -ForegroundColor Gray
    Write-Host ""
    Write-Host "To lahko traja nekaj minut..." -ForegroundColor Gray
    
    # Prenos preko SCP
    scp -r "$TEMP_DIR\*" "${SERVER}:${REMOTE_PATH}/"
    
    $scpSuccess = $LASTEXITCODE -eq 0
    
    if ($scpSuccess) {
        Write-Host ""
        Write-Host "[OK] Prenos uspesen!" -ForegroundColor Green
        Write-Host ""
        
        Write-Host "[3/3] Zaganjanje deploymenta..." -ForegroundColor Yellow
        ssh $SERVER "cd $REMOTE_PATH; ./deploy.sh"
        
        $deploySuccess = $LASTEXITCODE -eq 0
        if ($deploySuccess) {
            Write-Host ""
            Write-Host "[OK] Deployment uspesen!" -ForegroundColor Green
        } else {
            Write-Host ""
            Write-Host "[ERROR] Napaka pri deploymentu!" -ForegroundColor Red
            Write-Host "Zazenite rocno na strezniku: ssh $SERVER" -ForegroundColor Yellow
        }
    } else {
        Write-Host ""
        Write-Host "[ERROR] Napaka pri prenosu!" -ForegroundColor Red
        Write-Host ""
        Write-Host "Preveri:" -ForegroundColor Yellow
        Write-Host "  - SSH dostop do streznika" -ForegroundColor White
        Write-Host "  - Pravice za pisanje v $REMOTE_PATH" -ForegroundColor White
        Write-Host "  - Omrezna povezava" -ForegroundColor White
    }
} finally {
    # Pocisti začasno mapo
    if (Test-Path $TEMP_DIR) {
        Remove-Item -Path $TEMP_DIR -Recurse -Force -ErrorAction SilentlyContinue
    }
}
