# WinSCP Deployment Skripta
# Uporaba: .\deploy-winscp.ps1
# Zahteva: WinSCP nameščen

$SERVER = "192.168.178.153"
$USER = "upravitelj"
$REMOTE_PATH = "/var/www/merila-app"
$LOCAL_PATH = "c:\Projekt\merila 37.001"

# Preveri, da je WinSCP nameščen
$winscpPath = "C:\Program Files (x86)\WinSCP\WinSCP.com"
if (-not (Test-Path $winscpPath)) {
    $winscpPath = "C:\Program Files\WinSCP\WinSCP.com"
    if (-not (Test-Path $winscpPath)) {
        Write-Host "[ERROR] WinSCP ni namescen!" -ForegroundColor Red
        Write-Host "Prenesi iz: https://winscp.net/" -ForegroundColor Yellow
        exit 1
    }
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "WinSCP Deployment - Merila App" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Preberi geslo (ali uporabi SSH ključ)
$usePassword = Read-Host "Uporabiti geslo za povezavo? (y/n) - Če n, moraš imeti SSH ključ nastavljen"

if ($usePassword -eq "y" -or $usePassword -eq "Y") {
    $password = Read-Host "Vnesi geslo" -AsSecureString
    $BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($password)
    $plainPassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)
    # URL encode geslo za WinSCP
    Add-Type -AssemblyName System.Web
    $plainPassword = [System.Web.HttpUtility]::UrlEncode($plainPassword)
    $connectionString = "sftp://$USER`:$plainPassword@$SERVER/"
} else {
    $connectionString = "sftp://$USER@$SERVER/"
}

$script = @"
option batch abort
option confirm off
open $connectionString
synchronize remote "$LOCAL_PATH" "$REMOTE_PATH" -delete -exclude="vendor/;node_modules/;.git/;.env;storage/logs/*.log;storage/framework/cache/*;storage/framework/sessions/*;storage/framework/views/*;bootstrap/cache/*.php;public/build/;public/hot;public/storage"
call "cd $REMOTE_PATH && chmod +x deploy.sh && ./deploy.sh --no-git"
exit
"@

Write-Host "[1/2] Sinhronizacija datotek..." -ForegroundColor Yellow

# Ustvari začasno datoteko za WinSCP skripto
$tempScript = [System.IO.Path]::GetTempFileName()
# WinSCP potrebuje UTF-8 brez BOM
$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllText($tempScript, $script, $utf8NoBom)

try {
    # Zaženi WinSCP s skripto
    & $winscpPath /script=$tempScript /ini=nul
    
    $winscpSuccess = $LASTEXITCODE -eq 0
} finally {
    # Počisti začasno datoteko
    if (Test-Path $tempScript) {
        Remove-Item -Path $tempScript -Force -ErrorAction SilentlyContinue
    }
}

if ($winscpSuccess) {
    Write-Host ""
    Write-Host "[OK] Deployment uspesen!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "[ERROR] Napaka pri deploymentu!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Preveri:" -ForegroundColor Yellow
    Write-Host "  - SSH dostop do streznika" -ForegroundColor White
    Write-Host "  - WinSCP nameščen" -ForegroundColor White
    Write-Host "  - SSH host key pravilen" -ForegroundColor White
}
