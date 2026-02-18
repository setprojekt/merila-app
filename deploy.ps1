# Avtomatiziran Deployment Skripta (Združljiva s PS 5.1)
param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("git", "rsync", "full")]
    [string]$Method = "rsync"  # Privzeto RSync, ker je bolj zanesljiv
)

$SERVER = "upravitelj@192.168.178.153"
$REMOTE_PATH = "/var/www/merila-app"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Deployment - Merila App" -ForegroundColor Cyan
Write-Host "Metoda: $Method" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

switch ($Method) {
    "git" {
        Write-Host "[1/3] Preverjanje git statusa..." -ForegroundColor Yellow
        
        # Preveri, ali je git repozitorij
        if (-not (Test-Path .git)) {
            Write-Host "[ERROR] Ni git repozitorija! Uporabi --Method rsync" -ForegroundColor Red
            exit 1
        }
        
        # Preveri, kateri branch je aktiven
        $currentBranch = git branch --show-current 2>$null
        if ([string]::IsNullOrWhiteSpace($currentBranch)) {
            # Če ni branch-a, poskusi "master" ali "main"
            $branches = git branch 2>$null
            if ($branches -match "master") {
                $currentBranch = "master"
            } elseif ($branches -match "main") {
                $currentBranch = "main"
            } else {
                Write-Host "[ERROR] Ni najdenega branch-a! Uporabi --Method rsync" -ForegroundColor Red
                exit 1
            }
        }
        
        Write-Host "Branch: $currentBranch" -ForegroundColor Gray
        
        Write-Host "[2/3] Git commit in push..." -ForegroundColor Yellow
        git add .
        $commitResult = git commit -m "Deploy: $(Get-Date -Format 'yyyy-MM-dd HH:mm')" 2>&1
        
        # Preveri, ali je bil commit uspešen (lahko ni sprememb)
        if ($LASTEXITCODE -ne 0 -and $commitResult -notmatch "nothing to commit") {
            Write-Host "[WARNING] Napaka pri commitu: $commitResult" -ForegroundColor Yellow
        }
        
        # Push (samo če je remote nastavljen)
        $remotes = git remote 2>$null
        if ($remotes -contains "origin") {
            git push origin $currentBranch
            if ($LASTEXITCODE -ne 0) {
                Write-Host "[WARNING] Napaka pri push-u. Poskusim z RSync..." -ForegroundColor Yellow
                $Method = "rsync"
                & "$PSScriptRoot\deploy-rsync.ps1"
                exit 0
            }
        } else {
            Write-Host "[WARNING] Ni remote 'origin'! Uporabljam RSync..." -ForegroundColor Yellow
            $Method = "rsync"
            & "$PSScriptRoot\deploy-rsync.ps1"
            exit 0
        }
        
        Write-Host "[3/3] Deployment na strežniku..." -ForegroundColor Yellow
        ssh $SERVER "cd $REMOTE_PATH; git pull origin $currentBranch; ./deploy.sh"
    }
    
    "rsync" {
        # Preveri, ali je rsync na voljo
        try {
            $null = rsync --version 2>&1 | Select-Object -First 1
            & "$PSScriptRoot\deploy-rsync.ps1"
        } catch {
            Write-Host "[WARNING] RSync ni namescen. Uporabljam SCP..." -ForegroundColor Yellow
            & "$PSScriptRoot\deploy-scp.ps1"
        }
    }
    
    "full" {
        # Preveri, ali je rsync na voljo
        try {
            $null = rsync --version 2>&1 | Select-Object -First 1
            Write-Host "[1/3] RSync prenos..." -ForegroundColor Yellow
            $EXCLUDE = @("vendor/", "node_modules/", ".git/", ".env", "storage/logs/*.log")
            $excludeArgs = $EXCLUDE | ForEach-Object { "--exclude=$_" }
            
            # Prenos datotek
            rsync -avz --delete $excludeArgs "./" "$SERVER`:$REMOTE_PATH/"
        } catch {
            Write-Host "[WARNING] RSync ni namescen. Uporabljam SCP..." -ForegroundColor Yellow
            & "$PSScriptRoot\deploy-scp.ps1"
            exit 0
        }
        
        Write-Host "[2/3] Deployment na strežniku..." -ForegroundColor Yellow
        ssh $SERVER "cd $REMOTE_PATH; ./deploy.sh"
        
        Write-Host "[3/3] Preverjanje..." -ForegroundColor Yellow
        ssh $SERVER "cd $REMOTE_PATH; php artisan --version"
    }
}
Write-Host "✅ Konec deploymenta." -ForegroundColor Green