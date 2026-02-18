# 📁 WinSCP Deployment - Navodila

## 🎯 **Kako Zaženi Deployment**

### **Možnost 1: PowerShell Skripta (Avtomatizirano)** ⭐

`deploy-winscp.ps1` uporablja WinSCP komandno linijo (`WinSCP.com`), ne GUI aplikacijo.

#### **Zaženi v PowerShell:**

```powershell
cd "c:\Projekt\merila 37.001"
.\deploy-winscp.ps1
```

Skripta bo:
1. Preverila, ali je WinSCP nameščen
2. Vprašala za geslo (ali SSH ključ)
3. Sinhronizirala datoteke
4. Zažela deployment na strežniku

---

### **Možnost 2: WinSCP GUI (Ročno)** ⭐⭐

Če raje uporabljaš grafični vmesnik:

#### **Korak 1: Odpri WinSCP**

1. Zaženi WinSCP aplikacijo
2. Klikni **New Site** (ali `Ctrl+N`)

#### **Korak 2: Nastavi povezavo**

Vnesi podatke:
- **File protocol:** `SFTP`
- **Host name:** `192.168.178.153`
- **Port number:** `22`
- **User name:** `upravitelj`
- **Password:** tvoje geslo

Klikni **Save** (shrani kot "Merila Production")

#### **Korak 3: Poveži se**

Klikni **Login** (ali `Ctrl+L`)

#### **Korak 4: Navigiraj**

- **Lokalna stran (levo):** Pojdi v `c:\Projekt\merila 37.001`
- **Oddaljena stran (desno):** Pojdi v `/var/www/merila-app`

#### **Korak 5: Sinhroniziraj**

1. Klikni ikono **Synchronize** (dve puščici v krogu) ali `Ctrl+S`
2. Nastavi:
   - **Local directory:** `c:\Projekt\merila 37.001`
   - **Remote directory:** `/var/www/merila-app`
   - **Synchronization mode:** `Remote`
   - **Direction:** `Both` ali `Remote`
3. Klikni **Compare**
4. Preveri, katere datoteke se bodo prenesle
5. Klikni **Synchronize**

#### **Korak 6: Izključi datoteke (pomembno!)**

Pred sinhronizacijo nastavi **Exclude**:
- `vendor/`
- `node_modules/`
- `.git/`
- `.env`
- `storage/logs/*.log`
- `storage/framework/cache/*`
- `storage/framework/sessions/*`
- `storage/framework/views/*`
- `bootstrap/cache/*.php`
- `public/build/`
- `public/hot`
- `public/storage`

#### **Korak 7: Zaženi deployment na strežniku**

Po sinhronizaciji:
1. Klikni desni klik na oddaljeni strani
2. Izberi **Custom Commands** → **Open Terminal**
3. Vnesi:
```bash
cd /var/www/merila-app
./deploy.sh --no-git
```

---

### **Možnost 3: WinSCP Komandna Linija (Napredno)**

Uporabi WinSCP komandno linijo direktno:

```powershell
# Ustvari skripto
$script = @"
option batch abort
option confirm off
open sftp://upravitelj@192.168.178.153/
synchronize remote "c:\Projekt\merila 37.001" "/var/www/merila-app" -delete -exclude="vendor/;node_modules/;.git/;.env"
call "cd /var/www/merila-app && ./deploy.sh --no-git"
exit
"@

# Zaženi
& "C:\Program Files (x86)\WinSCP\WinSCP.com" /script=-
```

---

## 🔧 **Nastavitev SSH Ključa (Priporočeno)**

Za varnostno povezavo brez gesla:

### **1. Generiraj SSH ključ:**

```powershell
ssh-keygen -t rsa -b 4096 -C "winscp-deploy"
# Shrani v: C:\Users\tvoje-ime\.ssh\winscp-deploy
```

### **2. Kopiraj javni ključ na strežnik:**

```powershell
type C:\Users\tvoje-ime\.ssh\winscp-deploy.pub | ssh upravitelj@192.168.178.153 "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"
```

### **3. V WinSCP:**

1. **Advanced** → **Authentication**
2. Izberi **Private key file**
3. Izberi `C:\Users\tvoje-ime\.ssh\winscp-deploy`
4. Shrani

---

## 📋 **Checklist za WinSCP Deployment**

### **Pred deploymentom:**
- [ ] WinSCP nameščen
- [ ] SSH dostop do strežnika deluje
- [ ] Poznano geslo ali SSH ključ nastavljen
- [ ] Lokalna mapa: `c:\Projekt\merila 37.001`
- [ ] Oddaljena mapa: `/var/www/merila-app`

### **Med deploymentom:**
- [ ] Povezan na strežnik
- [ ] Navigiral v prave mape
- [ ] Nastavil exclude datoteke
- [ ] Sinhroniziral datoteke
- [ ] Preveril, da so se datoteke prenesle

### **Po deploymentu:**
- [ ] Zažel `./deploy.sh --no-git` na strežniku
- [ ] Preveril, da aplikacija deluje
- [ ] Preveril log datoteke

---

## 🚨 **Troubleshooting**

### **Problem: "Permission denied"**
```bash
# Na strežniku
sudo chown -R www-data:www-data /var/www/merila-app
sudo chmod -R 775 /var/www/merila-app/storage
```

### **Problem: "Connection refused"**
- Preveri, da SSH teče na strežniku: `sudo systemctl status ssh`
- Preveri firewall

### **Problem: "Host key verification failed"**
- V WinSCP: **Advanced** → **Accept new host key**
- Ali ročno: `ssh-keyscan -t rsa 192.168.178.153 >> ~/.ssh/known_hosts`

---

## ✅ **Hitri Postopek (WinSCP GUI)**

1. **Odpri WinSCP** → **New Site**
2. **Nastavi:** SFTP, `192.168.178.153`, port 22, user `upravitelj`
3. **Login**
4. **Synchronize** (`Ctrl+S`)
5. **Nastavi:** Local: `c:\Projekt\merila 37.001`, Remote: `/var/www/merila-app`
6. **Exclude:** `vendor/;node_modules/;.git/;.env`
7. **Synchronize**
8. **Terminal** → `cd /var/www/merila-app && ./deploy.sh --no-git`

---

**Zadnja posodobitev:** 2026-01-23
