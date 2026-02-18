# 📤 Navodila za prenos preko FileZille

Kompleten vodič za prenos aplikacije na strežnik preko FileZille (FTP/SFTP).

---

## 🔧 **1. PRIPRAVA DATOTEK ZA PRENOS**

### **Datoteke, ki jih MORATE prenesti:**

✅ **Vse datoteke iz projekta, RAZEN:**
- ❌ `vendor/` (se namesti na strežniku z `composer install`)
- ❌ `node_modules/` (se namesti na strežniku z `npm install`)
- ❌ `.git/` (če ne uporabljate git na strežniku)
- ❌ `.env` (ustvarite na strežniku iz `.env.example`)
- ❌ `storage/logs/*.log` (log datoteke)
- ❌ `storage/framework/cache/*` (cache datoteke)
- ❌ `storage/framework/sessions/*` (session datoteke)
- ❌ `storage/framework/views/*` (compiled views)
- ❌ `bootstrap/cache/*.php` (razen `.gitignore`)
- ❌ `public/build/` (se generira z `npm run build`)
- ❌ `public/hot` (development datoteka)
- ❌ `public/storage` (se ustvari z `php artisan storage:link`)

### **Datoteke, ki jih MORATE prenesti:**

✅ **Vse ostale datoteke in mape:**
- ✅ `app/` - celotna mapa
- ✅ `bootstrap/` - celotna mapa (brez cache datotek)
- ✅ `config/` - celotna mapa
- ✅ `database/` - celotna mapa
- ✅ `lang/` - celotna mapa
- ✅ `public/` - celotna mapa (brez build/hot/storage)
- ✅ `resources/` - celotna mapa
- ✅ `routes/` - celotna mapa
- ✅ `storage/` - struktura map (brez vsebine)
- ✅ `tests/` - celotna mapa
- ✅ `artisan` - datoteka
- ✅ `composer.json` - datoteka
- ✅ `composer.lock` - datoteka
- ✅ `package.json` - datoteka
- ✅ `package-lock.json` - datoteka
- ✅ `vite.config.js` - datoteka
- ✅ `phpunit.xml` - datoteka
- ✅ `.editorconfig` - datoteka
- ✅ `.gitignore` - datoteka
- ✅ `.gitattributes` - datoteka
- ✅ `.env.example` - datoteka (pomembno!)
- ✅ Vse `.md` dokumentacijske datoteke
- ✅ Vse `.php` datoteke v root mapi (npr. `create-admin-user.php`)

---

## 📥 **2. PRENOS PREKO FILEZILLE**

### **2.1 Povezovanje na strežnik:**

1. **Odprite FileZilla**
2. **Kliknite "Site Manager"** (📁 ikona) ali `Ctrl+S`
3. **Kliknite "New Site"**
4. **Vnesite podatke:**
   - **Protocol:** `SFTP - SSH File Transfer Protocol` (priporočeno) ali `FTP - File Transfer Protocol`
   - **Host:** `vaš-strežnik.si` ali IP naslov
   - **Port:** `22` (za SFTP) ali `21` (za FTP)
   - **Logon Type:** `Normal`
   - **User:** vaše uporabniško ime
   - **Password:** vaše geslo
5. **Kliknite "Connect"**

### **2.2 Navigacija na strežniku:**

- **Lokalna stran (levo):** Vaš računalnik
- **Oddaljena stran (desno):** Strežnik

**Na strežniku pojdite na:**
```
/var/www/merila-app
```

**ALI** če uporabljate cPanel/Plesk:
```
/home/username/public_html/merila-app
```

### **2.3 Prenos datotek:**

1. **Na lokalni strani** pojdite v mapo projekta
2. **Izberite vse datoteke in mape** (razen tistih iz seznama zgoraj)
3. **Povlecite in spustite** na oddaljeno stran
4. **Počakajte, da se prenos zaključi**

⚠️ **POMEMBNO:**
- Prenos lahko traja več minut (odvisno od hitrosti interneta)
- Ne prekinite povezave med prenosom
- Preverite, da so se vse datoteke uspešno prenesle

---

## 🔐 **3. NASTAVITEV NA STREŽNIKU**

### **3.1 Povezovanje preko SSH:**

Povežite se na strežnik preko SSH:
```bash
ssh username@vaš-strežnik.si
```

### **3.2 Pojdite v mapo aplikacije:**

```bash
cd /var/www/merila-app
# ALI
cd ~/public_html/merila-app
```

### **3.3 Namestite odvisnosti:**

```bash
# Namesti PHP pakete
composer install --no-dev --optimize-autoloader

# Namesti Node.js pakete
npm install

# Zgradi frontend assets
npm run build
```

### **3.4 Ustvarite .env datoteko:**

```bash
# Kopiraj .env.example v .env
cp .env.example .env

# Uredi .env datoteko
nano .env
```

**Ključne nastavitve v .env:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://vaša-domena.si

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=merila_production
DB_USERNAME=merila_user
DB_PASSWORD=močno_geslo

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

SESSION_SECURE_COOKIE=true
```

### **3.5 Generiraj aplikacijski ključ:**

```bash
php artisan key:generate
```

### **3.6 Ustvari storage link:**

```bash
php artisan storage:link
```

### **3.7 Nastavi pravice:**

```bash
# Nastavi lastnika (prilagodite glede na vaš setup)
sudo chown -R www-data:www-data /var/www/merila-app

# Nastavi pravice
sudo chmod -R 755 /var/www/merila-app
sudo chmod -R 775 storage bootstrap/cache
```

### **3.8 Ustvari bazo podatkov:**

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE merila_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'merila_user'@'localhost' IDENTIFIED BY 'močno_geslo';
GRANT ALL PRIVILEGES ON merila_production.* TO 'merila_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### **3.9 Zaženi migracije:**

```bash
php artisan migrate --force
```

### **3.10 Optimiziraj za produkcijo:**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## ✅ **4. PREVERJANJE**

### **4.1 Preveri, da vse deluje:**

1. **Odprite spletno stran** v brskalniku
2. **Preveri, da se stran naloži brez napak**
3. **Preveri log datoteke:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### **4.2 Preveri pravice:**

```bash
# Preveri, da so storage in cache writable
ls -la storage/
ls -la bootstrap/cache/
```

---

## 🔄 **5. POSODOBITEV APLIKACIJE (Pozneje)**

Ko želite posodobiti aplikacijo:

1. **Prenesite nove datoteke** preko FileZille (prepišite stare)
2. **Povežite se preko SSH**
3. **Zaženite:**

```bash
cd /var/www/merila-app
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
sudo systemctl reload php8.3-fpm
```

---

## ⚠️ **6. POGOSTE NAPAKA**

### **Problem: Permission denied**
```bash
sudo chown -R www-data:www-data /var/www/merila-app
sudo chmod -R 775 storage bootstrap/cache
```

### **Problem: 500 Error**
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### **Problem: Database connection error**
- Preveri credentials v `.env`
- Preveri, da MySQL teče: `sudo systemctl status mysql`

### **Problem: Storage link ne deluje**
```bash
php artisan storage:link
```

---

## 📋 **7. CHECKLIST PRED PRENOSOM**

- [ ] FileZilla nameščena
- [ ] SSH dostop do strežnika
- [ ] Poznani FTP/SFTP podatki
- [ ] Poznana lokacija na strežniku (`/var/www/` ali `~/public_html/`)
- [ ] PHP 8.2+ nameščen na strežniku
- [ ] MySQL nameščen in zagnan
- [ ] Redis nameščen in zagnan (priporočeno)
- [ ] Composer nameščen na strežniku
- [ ] Node.js & NPM nameščena na strežniku

---

## 📋 **8. CHECKLIST PO PRENOSU**

- [ ] Vse datoteke prenesene
- [ ] `composer install` uspešen
- [ ] `npm install` uspešen
- [ ] `npm run build` uspešen
- [ ] `.env` datoteka ustvarjena in konfigurirana
- [ ] `php artisan key:generate` zažen
- [ ] `php artisan storage:link` zažen
- [ ] Pravice nastavljene
- [ ] Baza podatkov ustvarjena
- [ ] Migracije zažene
- [ ] Cache optimiziran
- [ ] Aplikacija deluje v brskalniku

---

## 📞 **PODPORA**

Za dodatno pomoč glej:
- `DEPLOYMENT.md` - Podrobna navodila za deployment
- `DEPLOYMENT_CHECKLIST.md` - Hitri checklist

---

**Zadnja posodobitev:** 2026-01-20
