# ✅ FileZilla Prenos - Hitri Checklist

## 📤 **PRED PRENOSOM**

- [ ] FileZilla nameščena in odprta
- [ ] SSH/FTP podatki pripravljeni
- [ ] Lokacija na strežniku znana (`/var/www/merila-app` ali podobno)
- [ ] Preverjeno, da strežnik ima PHP 8.2+, MySQL, Redis, Composer, Node.js

## 📥 **MED PRENOSOM**

### **V FileZilli:**
- [ ] Povezan na strežnik (SFTP ali FTP)
- [ ] Navigiral na pravo mapo na strežniku
- [ ] Izbral vse datoteke in mape (razen vendor/, node_modules/, .git/, .env)
- [ ] Prenos v teku...
- [ ] Preverjeno, da se vse datoteke prenašajo

### **Datoteke, ki jih MORATE prenesti:**
- [ ] `app/` mapa
- [ ] `bootstrap/` mapa (brez cache)
- [ ] `config/` mapa
- [ ] `database/` mapa
- [ ] `lang/` mapa
- [ ] `public/` mapa (brez build/hot/storage)
- [ ] `resources/` mapa
- [ ] `routes/` mapa
- [ ] `storage/` struktura map (brez vsebine)
- [ ] `tests/` mapa
- [ ] `artisan` datoteka
- [ ] `composer.json` in `composer.lock`
- [ ] `package.json` in `package-lock.json`
- [ ] `vite.config.js`
- [ ] `.env.example`
- [ ] Vse `.md` dokumentacijske datoteke

### **Datoteke, ki jih NE prenašate:**
- [ ] `vendor/` - ❌ NE
- [ ] `node_modules/` - ❌ NE
- [ ] `.git/` - ❌ NE (razen če uporabljate git)
- [ ] `.env` - ❌ NE (ustvarite na strežniku)
- [ ] `storage/logs/*.log` - ❌ NE
- [ ] `public/build/` - ❌ NE
- [ ] Cache datoteke - ❌ NE

## 🔧 **PO PRENOSU NA STREŽNIKU (SSH)**

### **Osnovna nastavitev:**
- [ ] `cd /var/www/merila-app` (ali vaša lokacija)
- [ ] `composer install --no-dev --optimize-autoloader` ✅
- [ ] `npm install` ✅
- [ ] `npm run build` ✅
- [ ] `cp .env.example .env` ✅
- [ ] Uredil `.env` datoteko (APP_ENV=production, APP_DEBUG=false, DB nastavitve, Redis, itd.)
- [ ] `php artisan key:generate` ✅
- [ ] `php artisan storage:link` ✅

### **Pravice:**
- [ ] `sudo chown -R www-data:www-data /var/www/merila-app` ✅
- [ ] `sudo chmod -R 755 /var/www/merila-app` ✅
- [ ] `sudo chmod -R 775 storage bootstrap/cache` ✅

### **Baza podatkov:**
- [ ] Ustvaril bazo podatkov v MySQL
- [ ] Ustvaril uporabnika baze z pravicami
- [ ] Posodobil `.env` z DB podatki
- [ ] `php artisan migrate --force` ✅

### **Optimizacija:**
- [ ] `php artisan config:cache` ✅
- [ ] `php artisan route:cache` ✅
- [ ] `php artisan view:cache` ✅
- [ ] `php artisan event:cache` ✅

## ✅ **PREVERJANJE**

- [ ] Aplikacija se naloži v brskalniku (brez 500 error)
- [ ] Prijava deluje
- [ ] Preveril `storage/logs/laravel.log` (brez napak)
- [ ] Preveril pravice: `ls -la storage/` in `ls -la bootstrap/cache/`

## 🚨 **ČE KAJ NE DELUJE**

### **500 Error:**
```bash
sudo chmod -R 775 storage bootstrap/cache
php artisan config:clear && php artisan config:cache
sudo systemctl reload php8.3-fpm
```

### **403 Forbidden:**
```bash
sudo chown -R www-data:www-data /var/www/merila-app
```

### **Database Error:**
- Preveri `.env` credentials
- Preveri, da MySQL teče: `sudo systemctl status mysql`

### **Redis Error:**
- Preveri, da Redis teče: `sudo systemctl status redis-server`
- Testiraj: `redis-cli ping` (mora vrniti PONG)

---

**📖 Za podrobna navodila glej: `FILEZILLA-PRENOS.md`**
