# 🚀 Deployment Guide - Produkcija (Ubuntu 24.04.3 LTS)

Kompleten vodič za prenos aplikacije na produkcijski server.

---

## 📋 **1. SERVER ZAHTEVE**

### **Minimalne zahteve:**
- **OS:** Ubuntu 24.04.3 LTS
- **PHP:** 8.2+ (8.3 priporočeno)
- **MySQL:** 8.0+
- **Redis:** 7.0+
- **RAM:** Minimum 2GB, priporočeno 4GB+
- **Disk:** Minimum 20GB prostora
- **Nginx/Apache:** Za web server
- **SSL certifikat:** Let's Encrypt priporočeno

### **PHP Extensions (obvezno):**
```bash
php8.3-cli php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring
php8.3-curl php8.3-zip php8.3-gd php8.3-redis php8.3-bcmath
php8.3-intl php8.3-imagick
```

---

## 🔧 **2. INICIALNA NASTAVITEV SERVERJA**

### **2.1 Osnovni paketi:**
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl wget git unzip
```

### **2.2 Namesti PHP 8.3:**
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl \
    php8.3-zip php8.3-gd php8.3-redis php8.3-bcmath \
    php8.3-intl php8.3-imagick
```

### **2.3 Namesti MySQL:**
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### **2.4 Namesti Redis:**
```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### **2.5 Namesti Composer:**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### **2.6 Namesti Node.js & NPM:**
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### **2.7 Namesti Nginx:**
```bash
sudo apt install -y nginx
```

---

## 📦 **3. PRENOS APLIKACIJE NA SERVER**

### **3.1 Kloniraj aplikacijo:**
```bash
cd /var/www
sudo git clone [your-repository-url] merila-app
sudo chown -R www-data:www-data merila-app
cd merila-app
```

**ALI prenesi datoteke preko SFTP/SCP:**
```bash
scp -r ./merila-app user@server:/var/www/
```

### **3.2 Namesti odvisnosti:**
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

---

## ⚙️ **4. KONFIGURACIJA APLIKACIJE**

### **4.1 Ustvari .env datoteko:**
```bash
cp .env.example .env
nano .env  # ALI: sudo nano .env
```

### **4.2 Ključne spremembe v .env:**

```env
# APP SETTINGS
APP_NAME="SET Intranet"
APP_ENV=production
APP_KEY=base64:... # Generiraj z: php artisan key:generate
APP_DEBUG=false  # ⚠️ VEDNO FALSE NA PRODUKCIJI!
APP_URL=https://tvoja-domena.si

# DATABASE
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=merila_production
DB_USERNAME=merila_user
DB_PASSWORD=močno_geslo_tukaj

# REDIS (OBVEZNO za hitro odjavo!)
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# MAIL (konfiguriraj za produkcijo)
MAIL_MAILER=smtp
MAIL_HOST=smtp.tvoja-domena.si
MAIL_PORT=587
MAIL_USERNAME=noreply@tvoja-domena.si
MAIL_PASSWORD=geslo_za_mail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tvoja-domena.si
MAIL_FROM_NAME="${APP_NAME}"

# LOGGING
LOG_CHANNEL=stack
LOG_LEVEL=error  # Na produkciji samo errors

# SESSION
SESSION_LIFETIME=480  # 8 ur
SESSION_SECURE_COOKIE=true  # ⚠️ OBVEZNO za HTTPS!
SESSION_SAME_SITE=lax

# FILESYSTEM
FILESYSTEM_DISK=local  # ALI s3 za cloud storage
```

### **4.3 Generiraj APP_KEY:**
```bash
php artisan key:generate
```

### **4.4 Ustvari bazo podatkov:**
```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE merila_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'merila_user'@'localhost' IDENTIFIED BY 'močno_geslo_tukaj';
GRANT ALL PRIVILEGES ON merila_production.* TO 'merila_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### **4.5 Zaženi migracije:**
```bash
php artisan migrate --force
php artisan db:seed  # Če potrebuješ seed podatke
```

---

## 🌐 **5. NGINX KONFIGURACIJA**

### **5.1 Ustvari Nginx config:**
```bash
sudo nano /etc/nginx/sites-available/merila-app
```

### **5.2 Vsebina config datoteke:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name tvoja-domena.si www.tvoja-domena.si;
    root /var/www/merila-app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### **5.3 Aktiviraj site:**
```bash
sudo ln -s /etc/nginx/sites-available/merila-app /etc/nginx/sites-enabled/
sudo nginx -t  # Testiraj konfiguracijo
sudo systemctl reload nginx
```

---

## 🔒 **6. SSL CERTIFIKAT (Let's Encrypt)**

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d tvoja-domena.si -d www.tvoja-domena.si
```

Certbot bo avtomatsko:
- Namestil certifikat
- Posodobil Nginx config za HTTPS
- Nastavil avtomatsko obnavljanje

---

## 🚀 **7. OPTIMIZACIJA ZA PRODUKCIJO**

### **7.1 Cache konfiguracija:**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### **7.2 Optimiziraj autoloader:**
```bash
composer install --no-dev --optimize-autoloader
```

### **7.3 Permissions:**
```bash
sudo chown -R www-data:www-data /var/www/merila-app
sudo chmod -R 755 /var/www/merila-app
sudo chmod -R 775 /var/www/merila-app/storage
sudo chmod -R 775 /var/www/merila-app/bootstrap/cache
```

---

## 🔄 **8. QUEUE WORKER (Če uporabljaš queue)**

### **8.1 Ustvari systemd service:**
```bash
sudo nano /etc/systemd/system/merila-queue.service
```

```ini
[Unit]
Description=Merila Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/merila-app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

### **8.2 Zaženi queue worker:**
```bash
sudo systemctl daemon-reload
sudo systemctl enable merila-queue
sudo systemctl start merila-queue
```

---

## 📊 **9. SCHEDULER (Cron Jobs)**

### **9.1 Ustvari cron job:**
```bash
sudo crontab -e -u www-data
```

Dodaj:
```cron
* * * * * cd /var/www/merila-app && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔐 **10. VARNOSTNE NASTAVITVE**

### **10.1 Firewall (UFW):**
```bash
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw enable
```

### **10.2 Fail2Ban (zaščita pred brute force):**
```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### **10.3 Skrij PHP verzijo:**
V `/etc/php/8.3/fpm/php.ini`:
```ini
expose_php = Off
```

### **10.4 File permissions:**
```bash
# Storage in cache morata biti writable
sudo chmod -R 775 storage bootstrap/cache
```

---

## 🔍 **11. MONITORING IN LOGS**

### **11.1 Laravel logi:**
```bash
tail -f /var/www/merila-app/storage/logs/laravel.log
```

### **11.2 Nginx logi:**
```bash
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### **11.3 PHP-FPM logi:**
```bash
tail -f /var/log/php8.3-fpm.log
```

---

## 📝 **12. BACKUP STRATEGIJA**

### **12.1 Database backup (dnevni):**
```bash
sudo nano /usr/local/bin/merila-backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/merila"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="merila_production"
DB_USER="merila_user"
DB_PASS="geslo_tukaj"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Ohrani samo zadnjih 7 dni
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete
```

```bash
sudo chmod +x /usr/local/bin/merila-backup.sh
sudo crontab -e
```

Dodaj (vsako noč ob 2:00):
```cron
0 2 * * * /usr/local/bin/merila-backup.sh
```

### **12.2 Files backup:**
```bash
tar -czf /var/backups/merila/files_$(date +%Y%m%d).tar.gz /var/www/merila-app/storage
```

---

## ✅ **13. PREVERJANJE PRED ZAGNOM**

### **Checklist:**
- [ ] `.env` nastavljen na `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` pravilen (HTTPS)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] Redis konfiguriran
- [ ] Database migracije zažene
- [ ] Nginx config testiran
- [ ] SSL certifikat aktiven
- [ ] Permissions pravilno nastavljene
- [ ] Cache optimiziran
- [ ] Queue worker zagnan (če potrebno)
- [ ] Cron job aktiven
- [ ] Firewall konfiguriran
- [ ] Backup strategija nastavljena

---

## 🔄 **14. DEPLOYMENT SKRIPT**

Ustvari `deploy.sh` za lažje posodabljanje:

```bash
#!/bin/bash
cd /var/www/merila-app
git pull origin main
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
sudo systemctl reload php8.3-fpm
sudo systemctl restart merila-queue  # Če uporabljaš queue
```

```bash
chmod +x deploy.sh
```

---

## 🆘 **15. TROUBLESHOOTING**

### **500 Error:**
- Preveri permissions: `sudo chmod -R 775 storage bootstrap/cache`
- Preveri `.env`: `php artisan config:clear && php artisan config:cache`
- Preveri logi: `tail -f storage/logs/laravel.log`

### **404 Error:**
- Preveri Nginx config: `sudo nginx -t`
- Preveri, da je `root` pravilen v Nginx configu

### **Database connection error:**
- Preveri MySQL service: `sudo systemctl status mysql`
- Preveri credentials v `.env`
- Testiraj: `php artisan tinker` → `DB::connection()->getPdo();`

### **Redis connection error:**
- Preveri Redis: `sudo systemctl status redis-server`
- Testiraj: `redis-cli ping`

---

## 📞 **PODPORA**

Za dodatno podporo kontaktiraj IT oddelek ali preveri Laravel dokumentacijo.

---

**Zadnja posodobitev:** 2026-01-20
