# 📤 Kopiranje deploy.sh na Strežnik

## 🚀 **Hitra Metoda (Avtomatizirano)**

Zaženi PowerShell skripto:

```powershell
cd "c:\Projekt\merila 37.001"
.\kopiraj-deploy-na-server.ps1
```

To bo:
1. Prekopiralo `deploy.sh` na strežnik
2. Nastavilo izvršljive pravice

---

## 📋 **Ročne Metode**

### **Metoda 1: SCP (Komandna Linija)**

```powershell
cd "c:\Projekt\merila 37.001"
scp deploy.sh upravitelj@192.168.178.153:/var/www/merila-app/deploy.sh
```

Nato na strežniku:
```bash
ssh upravitelj@192.168.178.153
cd /var/www/merila-app
chmod +x deploy.sh
```

### **Metoda 2: WinSCP (GUI)**

1. Odpri WinSCP
2. Poveži se na `192.168.178.153`
3. Lokalna stran: `c:\Projekt\merila 37.001`
4. Oddaljena stran: `/var/www/merila-app`
5. Povleci `deploy.sh` na strežnik
6. Desni klik na `deploy.sh` → **Properties** → Omogoči **Execute**

### **Metoda 3: FileZilla**

1. Odpri FileZilla
2. Poveži se na strežnik
3. Kopiraj `deploy.sh` v `/var/www/merila-app/`
4. Desni klik → **File permissions** → Nastavi na `755`

### **Metoda 4: Direktno na Strežniku**

Če imaš SSH dostop:

```bash
ssh upravitelj@192.168.178.153
cd /var/www/merila-app
nano deploy.sh
```

Nato kopiraj vsebino iz lokalnega `deploy.sh` in shrani (`Ctrl+O`, `Enter`, `Ctrl+X`).

Nastavi pravice:
```bash
chmod +x deploy.sh
```

---

## ✅ **Preverjanje**

Preveri, da je `deploy.sh` na strežniku:

```bash
ssh upravitelj@192.168.178.153
cd /var/www/merila-app
ls -la deploy.sh
```

Morali bi videti:
```
-rwxr-xr-x 1 upravitelj upravitelj 1234 Jan 23 12:00 deploy.sh
```

Testiraj:
```bash
./deploy.sh --no-git
```

---

## 🔧 **Če deploy.sh že obstaja na strežniku**

Če že obstaja, ga prepiši:

```powershell
# Lokalno
scp deploy.sh upravitelj@192.168.178.153:/var/www/merila-app/deploy.sh
```

Ali z WinSCP:
- Povleci in prepiši obstoječo datoteko

---

## 🚨 **Troubleshooting**

### **Problem: "Permission denied"**
```bash
# Na strežniku
sudo chown upravitelj:upravitelj /var/www/merila-app/deploy.sh
chmod +x /var/www/merila-app/deploy.sh
```

### **Problem: "SCP ni nameščen"**
- Omogoči OpenSSH Client v Windows Features
- Ali uporabi WinSCP/FileZilla

### **Problem: "Connection refused"**
- Preveri SSH dostop: `ssh upravitelj@192.168.178.153`
- Preveri firewall

---

**Zadnja posodobitev:** 2026-01-23
