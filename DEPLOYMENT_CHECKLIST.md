# ✅ Deployment Checklist - Hitri Pregled

## 📋 **PRI PRENOSU NA SERVER:**

### **1. Server Setup**
- [ ] PHP 8.3 namestljen (+ vse extensions)
- [ ] MySQL 8.0 namestljen in konfiguriran
- [ ] Redis namestljen in zagnan
- [ ] Nginx/Apache namestljen
- [ ] Composer namestljen
- [ ] Node.js & NPM namestljen

### **2. Aplikacija**
- [ ] Aplikacija prenesena na `/var/www/merila-app`
- [ ] `composer install --no-dev --optimize-autoloader` zažen
- [ ] `npm install && npm run build` zažen
- [ ] `.env` datoteka ustvarjena iz `.env.example`
- [ ] `php artisan key:generate` zažen

### **3. Database**
- [ ] Database `merila_production` ustvarjen
- [ ] Database user ustvarjen z pravicami
- [ ] `php artisan migrate --force` zažen
- [ ] Seed podatki (če potrebno)

### **4. .env Nastavitve (KLJUČNO!)**
```env
APP_ENV=production          # ⚠️ OBVEZNO!
APP_DEBUG=false            # ⚠️ OBVEZNO!
APP_URL=https://domena.si  # ⚠️ Z HTTPS!

SESSION_DRIVER=redis       # ⚠️ ZA HITRO ODJAVO!
CACHE_STORE=redis
QUEUE_CONNECTION=redis

SESSION_SECURE_COOKIE=true # ⚠️ ZA HTTPS!
SESSION_SAME_SITE=lax
```

### **5. Nginx/Apache**
- [ ] Config datoteka ustvarjena
- [ ] Site aktiviran
- [ ] `nginx -t` / `apachectl configtest` uspešen
- [ ] Web server restarted

### **6. SSL**
- [ ] Let's Encrypt certifikat namestljen
- [ ] Auto-renewal konfiguriran
- [ ] HTTPS deluje (redirect HTTP → HTTPS)

### **7. Optimizacija**
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan event:cache`
- [ ] Composer autoloader optimiziran

### **8. Permissions**
- [ ] `chown -R www-data:www-data /var/www/merila-app`
- [ ] `chmod -R 755 /var/www/merila-app`
- [ ] `chmod -R 775 storage bootstrap/cache`

### **9. Queue Worker (če potrebno)**
- [ ] Systemd service ustvarjen
- [ ] Queue worker zagnan in aktiven
- [ ] Auto-start na boot omogočen

### **10. Scheduler (Cron)**
- [ ] Cron job za `schedule:run` dodan
- [ ] Cron aktiven za `www-data` user

### **11. Varnost**
- [ ] Firewall (UFW) konfiguriran
- [ ] Fail2Ban namestljen
- [ ] `expose_php = Off` v php.ini
- [ ] Sensitive datoteke zaščitene (`.env`, `storage/`)

### **12. Backup**
- [ ] Backup script ustvarjen
- [ ] Cron job za backup nastavljen
- [ ] Backup direktorij ustvarjen

### **13. Monitoring**
- [ ] Log files lokacije poznane
- [ ] Log rotation konfiguriran
- [ ] Monitoring tools namestljeni (opcijsko)

---

## 🔄 **OB POSODOBITVI APLIKACIJE:**

- [ ] `git pull` (ali prenesi nove datoteke)
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `npm install && npm run build`
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] Restart PHP-FPM: `sudo systemctl reload php8.3-fpm`
- [ ] Restart queue worker (če uporabljaš)
- [ ] Testiraj aplikacijo

---

## ⚠️ **KRITIČNE NAPAKA (BOLJŠE NE STORITI):**

- ❌ **NE pustiti** `APP_DEBUG=true` na produkciji
- ❌ **NE pustiti** `APP_ENV=local` na produkciji
- ❌ **NE uporabljati** `SESSION_DRIVER=file` (počasna odjava!)
- ❌ **NE pozabiti** na `SESSION_SECURE_COOKIE=true` z HTTPS
- ❌ **NE pustiti** storage/cache z napačnimi permissions
- ❌ **NE pozabiti** na SSL certifikat

---

## 🚨 **HITRI FIX ZA POGOSTE PROBLEME:**

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
```bash
php artisan config:clear
# Preveri .env credentials
mysql -u merila_user -p merila_production
```

### **Redis Error:**
```bash
sudo systemctl restart redis-server
redis-cli ping  # Mora vrniti PONG
```

---

**📖 Za podrobnosti glej `DEPLOYMENT.md`**
