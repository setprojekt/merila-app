# RSync Deployment Skripta za Windows
# Uporaba: .\deploy-rsync.ps1

# Konfiguracija - PRILAGODI!
$SERVER = "upravitelj@192.168.178.153"
$REMOTE_PATH = "/var/www/merila-app"
$LOCAL_PATH = "."

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "RSync Deployment - Merila App" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Preveri, da je rsync na voljo
try {
    $rsyncVersion = rsync --version 2>&1 | Select-Object -First 1
    Write-Host "[OK] RSync namescen" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] RSync ni namescen!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Namesti RSync:" -ForegroundColor Yellow
    Write-Host "1. Namesti WSL (Windows Subsystem for Linux)" -ForegroundColor White
    Write-Host "2. Ali uporabi Git Bash (ki vkljucuje rsync)" -ForegroundColor White
    Write-Host ""
    exit 1
}

# Izkljuci datoteke, ki jih ne prenasamo
$EXCLUDE = @(
    "vendor/",
    "node_modules/",
    ".git/",
    ".env",
    "storage/logs/*.log",
    "storage/framework/cache/*",
    "storage/framework/sessions/*",
    "storage/framework/views/*",
    "bootstrap/cache/*.php",
    "public/build/",
    "public/hot",
    "public/storage",
    ".DS_Store",
    "Thumbs.db"
)

# Zgradi exclude string
$excludeArgs = $EXCLUDE | ForEach-Object { "--exclude=$_" }

Write-Host "[1/2] Prenos datotek na streznik..." -ForegroundColor Yellow
Write-Host "   Server: $SERVER" -ForegroundColor Gray
Write-Host "   Cilj: $REMOTE_PATH" -ForegroundColor Gray
Write-Host ""

# RSync prenos
rsync -avz --delete $excludeArgs "$LOCAL_PATH/" "$SERVER`:$REMOTE_PATH/"

$rsyncSuccess = $LASTEXITCODE -eq 0

if ($rsyncSuccess) {
    Write-Host ""
    Write-Host "[OK] Prenos uspesen!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Naslednji koraki na strezniku:" -ForegroundColor Cyan
    Write-Host "   ssh $SERVER" -ForegroundColor White
    Write-Host "   cd $REMOTE_PATH" -ForegroundColor White
    Write-Host "   ./deploy.sh" -ForegroundColor White
    Write-Host ""
    Write-Host "Ali zelite avtomatsko zagnati deployment?" -ForegroundColor Yellow
    $runDeploy = Read-Host "Vnesite y za DA, n za NE"
    
    if ($runDeploy -eq "y" -or $runDeploy -eq "Y") {
        Write-Host ""
        Write-Host "[2/2] Zaganjanje deploymenta..." -ForegroundColor Yellow
        ssh $SERVER "cd $REMOTE_PATH; ./deploy.sh"
        
        $deploySuccess = $LASTEXITCODE -eq 0
        if ($deploySuccess) {
            Write-Host ""
            Write-Host "[OK] Deployment uspesen!" -ForegroundColor Green
        } else {
            Write-Host ""
            Write-Host "[ERROR] Napaka pri deploymentu!" -ForegroundColor Red
        }
    } else {
        Write-Host ""
        Write-Host "Deployment preskocen. Zazenite rocno na strezniku." -ForegroundColor Yellow
    }
} else {
    Write-Host ""
    Write-Host "[ERROR] Napaka pri prenosu!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Preveri:" -ForegroundColor Yellow
    Write-Host "  - SSH dostop do streznika" -ForegroundColor White
    Write-Host "  - Pravice za pisanje v $REMOTE_PATH" -ForegroundColor White
    Write-Host "  - RSync namescen na obeh straneh" -ForegroundColor White
    exit 1
}
