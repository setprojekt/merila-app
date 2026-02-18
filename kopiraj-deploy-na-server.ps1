# Skripta za kopiranje deploy.sh na strežnik
# Uporaba: .\kopiraj-deploy-na-server.ps1

$SERVER = "upravitelj@192.168.178.153"
$REMOTE_PATH = "/var/www/merila-app"
$LOCAL_DEPLOY = "deploy.sh"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Kopiranje deploy.sh na strežnik" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Preveri, da deploy.sh obstaja lokalno
if (-not (Test-Path $LOCAL_DEPLOY)) {
    Write-Host "[ERROR] deploy.sh ni najden v trenutni mapi!" -ForegroundColor Red
    Write-Host "Preveri, da si v pravi mapi: c:\Projekt\merila 37.001" -ForegroundColor Yellow
    exit 1
}

Write-Host "[1/2] Kopiranje deploy.sh na strežnik..." -ForegroundColor Yellow
Write-Host "   Server: $SERVER" -ForegroundColor Gray
Write-Host "   Cilj: $REMOTE_PATH/deploy.sh" -ForegroundColor Gray
Write-Host ""

# Kopiraj preko SCP
scp $LOCAL_DEPLOY "${SERVER}:${REMOTE_PATH}/deploy.sh"

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "[OK] deploy.sh uspešno prenesen!" -ForegroundColor Green
    Write-Host ""
    
    Write-Host "[2/2] Nastavljanje pravic..." -ForegroundColor Yellow
    ssh $SERVER "chmod +x $REMOTE_PATH/deploy.sh"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "[OK] Pravice nastavljene!" -ForegroundColor Green
        Write-Host ""
        Write-Host "✅ deploy.sh je sedaj na strežniku in pripravljen za uporabo!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Preveri z:" -ForegroundColor Cyan
        Write-Host "   ssh $SERVER" -ForegroundColor White
        Write-Host "   cd $REMOTE_PATH" -ForegroundColor White
        Write-Host "   ls -la deploy.sh" -ForegroundColor White
    } else {
        Write-Host ""
        Write-Host "[WARNING] Napaka pri nastavljanju pravic!" -ForegroundColor Yellow
        Write-Host "Nastavi ročno na strežniku: chmod +x $REMOTE_PATH/deploy.sh" -ForegroundColor Yellow
    }
} else {
    Write-Host ""
    Write-Host "[ERROR] Napaka pri kopiranju!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Preveri:" -ForegroundColor Yellow
    Write-Host "  - SSH dostop do strežnika" -ForegroundColor White
    Write-Host "  - SCP nameščen (Windows 10/11 ima SCP)" -ForegroundColor White
    Write-Host "  - Pravice za pisanje v $REMOTE_PATH" -ForegroundColor White
    Write-Host ""
    Write-Host "Alternativa - ročno kopiraj:" -ForegroundColor Yellow
    Write-Host "  1. Odpri WinSCP ali FileZilla" -ForegroundColor White
    Write-Host "  2. Poveži se na strežnik" -ForegroundColor White
    Write-Host "  3. Kopiraj deploy.sh v /var/www/merila-app/" -ForegroundColor White
    Write-Host "  4. Na strežniku: chmod +x /var/www/merila-app/deploy.sh" -ForegroundColor White
}
