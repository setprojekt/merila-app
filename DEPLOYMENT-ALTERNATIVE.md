# 🚀 Alternativne Metode za Deployment

Različne možnosti za prenos aplikacije na produkcijski strežnik.

---

## 📋 **1. GITHUB ACTIONS (CI/CD Pipeline)** ⭐⭐⭐⭐⭐

### **Prednosti:**
- ✅ Popolnoma avtomatiziran
- ✅ Zgodovina deploymentov
- ✅ Rollback možnost
- ✅ Testiranje pred deploymentom
- ✅ Email obvestila
- ✅ Brezplačno za javne repozitorije

### **Zahteve:**
- GitHub repozitorij
- SSH ključi za strežnik
- GitHub Actions omogočen

### **Postopek:**

#### **A. Ustvari GitHub Actions Workflow:**

Ustvari `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches:
      - main  # ALI master
  workflow_dispatch:  # Ročni zagon

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, mysql, redis, gd, zip
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '20'
      
      - name: Install Composer dependencies
        run: composer install --no-dev --optimize-autoloader --no-interaction
      
      - name: Install NPM dependencies
        run: npm ci
      
      - name: Build assets
        run: npm run build
      
      - name: Deploy to server
        uses: appleboy/scp-action@master
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          port: 22
          source: "."
          target: "/var/www/merila-app"
          exclude: |
            vendor/
            node_modules/
            .git/
            .env
            storage/logs/*.log
            storage/framework/cache/*
            storage/framework/sessions/*
            storage/framework/views/*
            bootstrap/cache/*.php
            public/build/
            public/hot
            public/storage
            .github/
      
      - name: Run deployment script
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          port: 22
          script: |
            cd /var/www/merila-app
            ./deploy.sh
```

#### **B. Nastavi GitHub Secrets:**

1. Odpri GitHub repozitorij
2. Pojdi na **Settings** → **Secrets and variables** → **Actions**
3. Dodaj naslednje secrets:
   - `SERVER_HOST` = `192.168.178.153` (ali tvoj IP)
   - `SERVER_USER` = `upravitelj`
   - `SSH_PRIVATE_KEY` = tvoj privatni SSH ključ

#### **C. Generiraj SSH ključ (če ga nimaš):**

```powershell
# Lokalno (Windows)
ssh-keygen -t rsa -b 4096 -C "github-actions"
# Shrani v: C:\Users\tvoje-ime\.ssh\github-actions

# Kopiraj javni ključ na strežnik
type C:\Users\tvoje-ime\.ssh\github-actions.pub | ssh upravitelj@192.168.178.153 "cat >> ~/.ssh/authorized_keys"

# Kopiraj privatni ključ v GitHub Secrets (celotno vsebino datoteke)
type C:\Users\tvoje-ime\.ssh\github-actions
```

#### **D. Uporaba:**

1. Commitaj in pushaj spremembe:
```powershell
git add .
git commit -m "Deploy: opis sprememb"
git push origin main
```

2. GitHub Actions bo avtomatsko:
   - Zgradil aplikacijo
   - Prenesel na strežnik
   - Zažel deployment

---

## 📁 **2. WINSCP (GUI Orodje)** ⭐⭐⭐⭐

### **Prednosti:**
- ✅ Grafični vmesnik
- ✅ Enostavna uporaba
- ✅ Varno (SFTP/SCP)
- ✅ Sinhronizacija map
- ✅ Skriptiranje možno

### **Zahteve:**
- WinSCP nameščen
- SSH dostop do strežnika

### **Postopek:**

#### **A. Namesti WinSCP:**

1. Prenesi iz: https://winscp.net/
2. Namesti

#### **B. Poveži se na strežnik:**

1. Odpri WinSCP
2. Klikni **New Site**
3. Vnesi podatke:
   - **File protocol:** SFTP
   - **Host name:** `192.168.178.153`
   - **Port number:** `22`
   - **User name:** `upravitelj`
   - **Password:** tvoje geslo
4. Klikni **Save** in **Login**

#### **C. Sinhroniziraj datoteke:**

1. **Lokalna stran (levo):** Navigiraj v `c:\Projekt\merila 37.001`
2. **Oddaljena stran (desno):** Navigiraj v `/var/www/merila-app`

3. **Izberi datoteke za prenos:**
   - Izključi: `vendor/`, `node_modules/`, `.git/`, `.env`, cache datoteke

4. **Prenesi:**
   - Desni klik → **Upload**
   - Ali uporabi **Synchronize** (ikonka z dvema puščicama)

#### **D. Avtomatizacija z WinSCP skripto:**

Ustvari `deploy-winscp.txt`:

```
# WinSCP skripta za deployment
option batch abort
option confirm off

# Povezava
open sftp://upravitelj@192.168.178.153/ -hostkey="ssh-rsa 2048 ..."

# Sinhronizacija
synchronize remote "c:\Projekt\merila 37.001" "/var/www/merila-app" -delete -exclude="vendor/;node_modules/;.git/;.env;storage/logs/*.log;storage/framework/cache/*;storage/framework/sessions/*;storage/framework/views/*;bootstrap/cache/*.php;public/build/;public/hot;public/storage"

# Zaženi deployment
call "cd /var/www/merila-app && ./deploy.sh"

exit
```

Zaženi:
```powershell
"C:\Program Files (x86)\WinSCP\WinSCP.com" /script=deploy-winscp.txt
```

#### **E. PowerShell skripta za WinSCP:**

Ustvari `deploy-winscp.ps1`:

```powershell
$winscpPath = "C:\Program Files (x86)\WinSCP\WinSCP.com"
$script = @"
option batch abort
option confirm off
open sftp://upravitelj@192.168.178.153/ -hostkey="ssh-rsa 2048 ..."
synchronize remote "c:\Projekt\merila 37.001" "/var/www/merila-app" -delete -exclude="vendor/;node_modules/;.git/;.env"
call "cd /var/www/merila-app && ./deploy.sh"
exit
"@

$script | & $winscpPath /script=-
```

---

## 🖥️ **3. GITHUB DESKTOP (GUI za Git)** ⭐⭐⭐

### **Prednosti:**
- ✅ Grafični vmesnik za Git
- ✅ Enostavno commitanje in pushanje
- ✅ Integracija z GitHub Actions
- ✅ Brezplačno

### **Zahteve:**
- GitHub Desktop nameščen
- GitHub repozitorij
- SSH dostop do strežnika (za deployment)

### **Postopek:**

#### **A. Namesti GitHub Desktop:**

1. Prenesi iz: https://desktop.github.com/
2. Namesti in se prijavi v GitHub

#### **B. Kloniraj repozitorij:**

1. **File** → **Clone repository**
2. Izberi svoj repozitorij
3. Kloniraj v `c:\Projekt\merila 37.001`

#### **C. Delovni proces:**

1. **Naredi spremembe** v datotekah
2. **Commit:**
   - Vidiš spremembe v levem panelu
   - Vnesi commit sporočilo
   - Klikni **Commit to main**

3. **Push:**
   - Klikni **Push origin**
   - Spremembe se pošljejo na GitHub

4. **Deployment:**
   - Če imaš GitHub Actions nastavljen, se deployment zažene avtomatsko
   - Ali pa ročno zaženi deployment skripto na strežniku

#### **D. Kombinacija z GitHub Actions:**

1. Uporabljaj GitHub Desktop za commit/push
2. GitHub Actions se avtomatsko zažene ob push-u
3. Deployment poteka avtomatsko

---

## 📊 **PRIMERJAVA METOD**

| Metoda | Avtomatizacija | Težavnost | Hitrost | Varnost | Priporočeno |
|--------|----------------|-----------|---------|---------|-------------|
| **GitHub Actions** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ✅ Najboljše |
| **WinSCP** | ⭐⭐⭐ | ⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ✅ Dobro |
| **GitHub Desktop** | ⭐⭐⭐ | ⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ✅ Dobro |
| **RSync/SCP** | ⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ✅ Dobro |
| **FileZilla** | ⭐ | ⭐ | ⭐⭐⭐ | ⭐⭐⭐ | ⚠️ Osnovno |

---

## 🎯 **PRIPOROČEN POSTOPEK**

### **Za začetek:**
1. **WinSCP** - Najenostavnejše za ročni prenos
2. **GitHub Desktop** - Za Git upravljanje
3. **RSync/SCP skripte** - Za avtomatizacijo

### **Za naprednejše:**
1. **GitHub Actions** - Popolnoma avtomatiziran CI/CD
2. Kombinacija: GitHub Desktop + GitHub Actions

---

## 🔧 **HITRI SETUP - GitHub Actions**

### **1. Ustvari workflow datoteko:**

```powershell
# Lokalno
New-Item -ItemType Directory -Path ".github\workflows" -Force
New-Item -ItemType File -Path ".github\workflows\deploy.yml"
```

### **2. Kopiraj vsebino iz zgoraj v `.github/workflows/deploy.yml`**

### **3. Nastavi GitHub Secrets:**
- `SERVER_HOST`
- `SERVER_USER`
- `SSH_PRIVATE_KEY`

### **4. Pushaj na GitHub:**
```powershell
git add .
git commit -m "Add GitHub Actions deployment"
git push origin main
```

### **5. Preveri deployment:**
- Odpri GitHub repozitorij
- Pojdi na **Actions** tab
- Vidiš deployment status

---

## 🚨 **TROUBLESHOOTING**

### **GitHub Actions - SSH napaka:**
```bash
# Preveri SSH ključ na strežniku
ssh-keygen -t rsa -b 4096
cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

### **WinSCP - Permission denied:**
- Preveri pravice na strežniku
- Uporabi sudo za deployment skripto

### **GitHub Desktop - Push napaka:**
- Preveri SSH ključe
- Preveri GitHub dostop

---

**Zadnja posodobitev:** 2026-01-23
