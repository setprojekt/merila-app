# 🚀 Avtomatiziran Prenos na Produkcijo

Več načinov za avtomatiziran prenos aplikacije iz lokalnega okolja na produkcijski strežnik.

---

## 📋 **Možnosti Prenosa**

### **1. Git-based Deployment (Priporočeno) ⭐**
Najbolj profesionalen in varen način. Spremembe se commitajo v git, nato se na strežniku pulla.

### **2. RSync Deployment**
Hitro sinhroniziranje datotek preko SSH.

### **3. SSH + Skripta**
Avtomatiziran prenos in deployment preko SSH.

### **4. CI/CD Pipeline**
GitHub Actions, GitLab CI ali podobno (za naprednejše).

---

## 🔧 **1. GIT-BASED DEPLOYMENT (Priporočeno)**

### **Prednosti:**
- ✅ Varna verzija kontrola
- ✅ Možnost rollback-a
- ✅ Avtomatsko sledenje sprememb
- ✅ Enostavno delo v ekipi

### **Zahteve:**
- Git repozitorij (GitHub, GitLab, Bitbucket ali lokalni)
- SSH dostop do strežnika
- Git nameščen na strežniku

### **Postopek:**

#### **A. Lokalno (Windows):**

1. **Commitaj spremembe:**
```powershell
cd "c:\Projekt\merila 37.001"
git add .
git commit -m "Deploy: opis sprememb"
git push origin main
```

#### **B. Na strežniku:**

1. **Poveži se preko SSH:**
```bash
ssh upravitelj@intranet
cd /var/www/merila-app
```

2. **Pull najnovejše spremembe:**
```bash
git pull origin main
```

3. **Zaženi deployment skripto:**
```bash
./deploy.sh
```

Ali ročno:
```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
sudo systemctl reload php8.3-fpm
```

### **Avtomatizacija z Git Hook:**

Ustvari `deploy.sh` na strežniku (že obstaja) in ga naredi izvršljivega:
```bash
chmod +x /var/www/merila-app/deploy.sh
```

Nato lahko uporabljaš:
```bash
cd /var/www/merila-app
./deploy.sh
```

---

## 📤 **2. RSYNC DEPLOYMENT**

### **Prednosti:**
- ✅ Hitro sinhroniziranje
- ✅ Samo spremenjene datoteke
- ✅ Varno preko SSH

### **Zahteve:**
- RSync nameščen (na Windows: WSL ali Git Bash)
- SSH dostop do strežnika

### **Windows PowerShell Skripta:**

Ustvari `deploy-rsync.ps1`:

```powershell
# Konfiguracija
$SERVER = "upravitelj@intranet"
$REMOTE_PATH = "/var/www/merila-app"
$LOCAL_PATH = "c:\Projekt\merila 37.001"

# Izključi datoteke, ki jih ne prenašamo
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
    "public/storage"
)

# Zgradi exclude string
$excludeArgs = $EXCLUDE | ForEach-Object { "--exclude=$_" }

# RSync prenos
Write-Host "📤 Prenos datotek na strežnik..." -ForegroundColor Yellow
rsync -avz --delete $excludeArgs "$LOCAL_PATH/" "$SERVER`:$REMOTE_PATH/"

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Prenos uspešen!" -ForegroundColor Green
    Write-Host ""
    Write-Host "🔧 Sedaj zaženi na strežniku:" -ForegroundColor Cyan
    Write-Host "ssh $SERVER" -ForegroundColor White
    Write-Host "cd $REMOTE_PATH" -ForegroundColor White
    Write-Host "./deploy.sh" -ForegroundColor White
} else {
    Write-Host "❌ Napaka pri prenosu!" -ForegroundColor Red
}
```

### **Uporaba:**

```powershell
cd "c:\Projekt\merila 37.001"
.\deploy-rsync.ps1
```

### **Linux/Mac Bash Skripta:**

Ustvari `deploy-rsync.sh`:

```bash
#!/bin/bash

# Konfiguracija
SERVER="upravitelj@intranet"
REMOTE_PATH="/var/www/merila-app"
LOCAL_PATH="."

# RSync prenos
echo "📤 Prenos datotek na strežnik..."
rsync -avz --delete \
    --exclude='vendor/' \
    --exclude='node_modules/' \
    --exclude='.git/' \
    --exclude='.env' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='bootstrap/cache/*.php' \
    --exclude='public/build/' \
    --exclude='public/hot' \
    --exclude='public/storage' \
    "$LOCAL_PATH/" "$SERVER:$REMOTE_PATH/"

if [ $? -eq 0 ]; then
    echo "✅ Prenos uspešen!"
    echo ""
    echo "🔧 Sedaj zaženi na strežniku:"
    echo "ssh $SERVER"
    echo "cd $REMOTE_PATH"
    echo "./deploy.sh"
else
    echo "❌ Napaka pri prenosu!"
fi
```

---

## 🔐 **3. SSH + SKRIPTA (Vse v enem)**

### **Windows PowerShell Skripta:**

Ustvari `deploy-full.ps1`:

```powershell
# Konfiguracija
$SERVER = "upravitelj@intranet"
$REMOTE_PATH = "/var/www/merila-app"
$LOCAL_PATH = "c:\Projekt\merila 37.001"

Write-Host "🚀 Avtomatiziran deployment..." -ForegroundColor Cyan
Write-Host ""

# 1. RSync prenos
Write-Host "[1/3] Prenos datotek..." -ForegroundColor Yellow
$excludeArgs = @(
    "vendor/", "node_modules/", ".git/", ".env",
    "storage/logs/*.log", "storage/framework/cache/*",
    "storage/framework/sessions/*", "storage/framework/views/*",
    "bootstrap/cache/*.php", "public/build/", "public/hot", "public/storage"
) | ForEach-Object { "--exclude=$_" }

rsync -avz --delete $excludeArgs "$LOCAL_PATH/" "$SERVER`:$REMOTE_PATH/"

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Napaka pri prenosu!" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Prenos uspešen" -ForegroundColor Green

# 2. Deployment na strežniku
Write-Host "[2/3] Deployment na strežniku..." -ForegroundColor Yellow
ssh $SERVER "cd $REMOTE_PATH && ./deploy.sh"

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Napaka pri deploymentu!" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Deployment uspešen" -ForegroundColor Green

# 3. Preverjanje
Write-Host "[3/3] Preverjanje..." -ForegroundColor Yellow
ssh $SERVER "cd $REMOTE_PATH && php artisan --version"

Write-Host ""
Write-Host "✅ Deployment končan!" -ForegroundColor Green
```

---

## 🔄 **4. GIT HOOK (Avtomatski deployment ob push)**

### **Na strežniku:**

1. **Ustvari post-receive hook:**
```bash
cd /var/www/merila-app
mkdir -p .git/hooks
nano .git/hooks/post-receive
```

2. **Vsebina hook-a:**
```bash
#!/bin/bash
cd /var/www/merila-app
git --git-dir=/var/www/merila-app/.git --work-tree=/var/www/merila-app checkout -f
./deploy.sh
```

3. **Naredi izvršljivega:**
```bash
chmod +x .git/hooks/post-receive
```

4. **Nastavi bare repository:**
```bash
cd /var/www
git clone --bare /path/to/your/repo.git merila-app.git
```

Nato na lokalnem računalniku:
```bash
git remote add production upravitelj@intranet:/var/www/merila-app.git
git push production main
```

---

## 📝 **5. HITRI DEPLOYMENT SKRIPTI**

### **Windows: `deploy.ps1`**

```powershell
param(
    [string]$Method = "git"  # git, rsync, full
)

$SERVER = "upravitelj@intranet"
$REMOTE_PATH = "/var/www/merila-app"

switch ($Method) {
    "git" {
        Write-Host "📤 Git push..." -ForegroundColor Yellow
        git add .
        git commit -m "Deploy: $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
        git push origin main
        
        Write-Host "🔧 Deployment na strežniku..." -ForegroundColor Yellow
        ssh $SERVER "cd $REMOTE_PATH && git pull && ./deploy.sh"
    }
    "rsync" {
        & ".\deploy-rsync.ps1"
    }
    "full" {
        & ".\deploy-full.ps1"
    }
}
```

### **Uporaba:**
```powershell
.\deploy.ps1          # Git deployment (privzeto)
.\deploy.ps1 -Method rsync
.\deploy.ps1 -Method full
```

---

## ✅ **PRIMERJAVA METOD**

| Metoda | Hitrost | Varnost | Kompleksnost | Priporočeno |
|--------|---------|---------|--------------|-------------|
| **Git** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐ | ✅ Da |
| **RSync** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ | ✅ Da |
| **SSH+Skripta** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⚠️ Srednje |
| **CI/CD** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⚠️ Napredno |

---

## 🎯 **PRIPOROČEN POSTOPEK**

### **Za začetek:**
1. Nastavi Git repozitorij
2. Uporabi `deploy.sh` na strežniku
3. Lokalno: `git push`, nato SSH in `./deploy.sh`

### **Za naprednejše:**
1. Uporabi RSync za hitrejši prenos
2. Avtomatiziraj z PowerShell skripto
3. Razmisli o CI/CD pipeline

---

## 🔧 **NASTAVITEV NA STREŽNIKU**

### **1. Preveri, da je deploy.sh izvršljiv:**
```bash
chmod +x /var/www/merila-app/deploy.sh
```

### **2. Preveri SSH dostop:**
```bash
# Lokalno (Windows)
ssh upravitelj@intranet
```

### **3. Preveri Git:**
```bash
# Na strežniku
cd /var/www/merila-app
git status
```

---

## 🚨 **TROUBLESHOOTING**

### **Problem: "Permission denied" pri deploy.sh**
```bash
chmod +x /var/www/merila-app/deploy.sh
```

### **Problem: RSync ni nameščen (Windows)**
- Namesti WSL (Windows Subsystem for Linux)
- Ali uporabi Git Bash (ki vključuje rsync)

### **Problem: Git push ne deluje**
- Preveri SSH ključe: `ssh-keygen` in `ssh-copy-id`
- Preveri git remote: `git remote -v`

---

**Zadnja posodobitev:** 2026-01-23
