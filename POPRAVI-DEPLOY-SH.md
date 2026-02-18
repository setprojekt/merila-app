# 🔧 Popravljanje deploy.sh na Strežniku

Napaka "cannot execute: required file not found" običajno pomeni:
1. Windows line endings (CRLF) namesto Unix (LF)
2. Napačen shebang interpreter

## 🚀 **Hitra Rešitev**

### **Na strežniku:**

```bash
cd /var/www/merila-app

# Preveri line endings
file deploy.sh

# Če vidiš "CRLF", jih popravi:
sed -i 's/\r$//' deploy.sh

# ALI uporabi dos2unix (če je nameščen):
dos2unix deploy.sh

# Preveri shebang
head -n 1 deploy.sh
# Mora biti: #!/bin/bash

# Če bash ni na /bin/bash, poišči:
which bash
# Običajno je na: /usr/bin/bash ali /bin/bash

# Nastavi pravice
chmod +x deploy.sh

# Testiraj
./deploy.sh --no-git
```

---

## 📤 **Alternativa: Prekopiraj Popravljeno Datoteko**

### **Lokalno (Windows):**

1. **Popravi line endings:**
   - Odpri `deploy.sh` v editorju, ki podpira Unix line endings
   - Shrani kot Unix format (LF, ne CRLF)

2. **Kopiraj na strežnik:**
```powershell
cd "c:\Projekt\merila 37.001"
scp deploy.sh upravitelj@192.168.178.153:/var/www/merila-app/deploy.sh
```

3. **Na strežniku:**
```bash
ssh upravitelj@192.168.178.153
cd /var/www/merila-app
chmod +x deploy.sh
./deploy.sh --no-git
```

---

## 🔍 **Diagnostika**

### **Preveri line endings:**
```bash
# Na strežniku
cat -A deploy.sh | head -n 1
# Če vidiš ^M$ na koncu, so Windows line endings
```

### **Preveri shebang:**
```bash
head -n 1 deploy.sh
# Mora biti: #!/bin/bash
```

### **Preveri bash lokacijo:**
```bash
which bash
ls -la /bin/bash
ls -la /usr/bin/bash
```

### **Testiraj bash direktno:**
```bash
bash deploy.sh --no-git
# Če to deluje, je problem v shebang vrstici
```

---

## ✅ **Popravljena Verzija**

Ustvaril sem popravljeno verzijo `deploy.sh` brez emoji znakov in z Unix line endings.

Kopiraj jo na strežnik:
```powershell
.\kopiraj-deploy-na-server.ps1
```

Ali ročno:
```powershell
scp deploy.sh upravitelj@192.168.178.153:/var/www/merila-app/deploy.sh
```

Nato na strežniku:
```bash
chmod +x /var/www/merila-app/deploy.sh
dos2unix /var/www/merila-app/deploy.sh  # Če je nameščen
./deploy.sh --no-git
```

---

**Zadnja posodobitev:** 2026-01-23
