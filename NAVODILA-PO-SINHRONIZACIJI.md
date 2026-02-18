# Navodila po Sinhronizaciji Datotek na Produkcijski Server

## ✅ Kaj je že narejeno
- Datoteke so sinhronizirane na strežnik (`/var/www/merila-app`)

## 📋 Naslednji Koraki na Produkcijskem Serverju

### 1. Poveži se na strežnik preko SSH

**Iz Windows PowerShell ali WinSCP Terminal:**
```bash
ssh upravitelj@192.168.178.153
```

Ali v WinSCP:
- Desni klik na oddaljeni strani → **Custom Commands** → **Open Terminal**

---

### 2. Pojdi v direktorij aplikacije

```bash
cd /var/www/merila-app
```

---

### 3. Preveri, da so datoteke na mestu

```bash
ls -la
```

Morali bi videti:
- `composer.json`
- `package.json`
- `artisan`
- `app/`, `config/`, `database/`, `resources/`, itd.

---

### 4. Namesti Composer pakete

```bash
composer install --no-dev --optimize-autoloader
```

**Opomba:** Če `composer` ni na voljo, ga morate namestiti:
```bash
# Preveri, če je nameščen
composer --version

# Če ni, namesti:
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

### 5. Namesti NPM pakete in zgradi frontend

```bash
npm install
npm run build
```

**Opomba:** Če `npm` ni na voljo:
```bash
# Preveri Node.js
node --version
npm --version

# Če ni nameščen, namesti Node.js (npr. preko nvm ali apt)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
nvm install 20
nvm use 20
```

---

### 6. Nastavi .env datoteko

```bash
# Kopiraj .env.example v .env (če še ne obstaja)
cp .env.example .env

# Uredi .env datoteko
nano .env
```

**Pomembne nastavitve v .env:**
```env
APP_NAME="Merila"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.178.153

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=merila_db
DB_USERNAME=merila_user
DB_PASSWORD=tvoje_geslo

# Cache
CACHE_DRIVER=file
SESSION_DRIVER=file

# Mail (nastavi glede na tvoj mail server)
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=...
MAIL_USERNAME=...
MAIL_PASSWORD=...
```

**Shrani:** `Ctrl+X`, nato `Y`, nato `Enter`

---

### 7. Generiraj aplikacijski ključ

```bash
php artisan key:generate
```

---

### 8. Nastavi storage link (če potrebno)

```bash
php artisan storage:link
```

---

### 9. Nastavi dovoljenja

```bash
# Nastavi lastnika (prilagodi uporabnika glede na tvoj sistem)
sudo chown -R www-data:www-data /var/www/merila-app
# ali
sudo chown -R upravitelj:upravitelj /var/www/merila-app

# Nastavi dovoljenja za storage in cache
sudo chmod -R 775 /var/www/merila-app/storage
sudo chmod -R 775 /var/www/merila-app/bootstrap/cache
```

---

### 10. Zaženi migracije

```bash
php artisan migrate --force
```

**Opomba:** `--force` je potreben v produkciji, ker Laravel v produkciji ne zahteva potrditve.

---

### 11. Zaženi optimizacije (PRIPOROČENO)

**Možnost A: Uporabi deploy.sh skripto (AVTOMATSKO)**
```bash
# Naredi skripto izvedljivo
chmod +x deploy.sh

# Zaženi deploy (brez git pull, ker si že sinhroniziral)
./deploy.sh --no-git
```

**Možnost B: Ročno zaženi optimizacije**
```bash
# Cache konfiguracijo
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache

# Optimizacija Laravel
php artisan optimize
```

---

### 12. Preveri, da aplikacija deluje

```bash
# Preveri, da so datoteke na mestu
ls -la public/

# Preveri, da je storage link nastavljen
ls -la public/storage

# Preveri log datoteke (če so napake)
tail -f storage/logs/laravel.log
```

---

### 13. Restart PHP-FPM (če je nameščen)

```bash
# Preveri, ali teče PHP-FPM
sudo systemctl status php8.3-fpm
# ali
sudo systemctl status php8.2-fpm
# ali
sudo systemctl status php-fpm

# Restart (prilagodi verzijo)
sudo systemctl reload php8.3-fpm
# ali
sudo systemctl restart php8.3-fpm
```

---

### 14. Preveri Web Server konfiguracijo

**Za Nginx:**
```bash
# Preveri konfiguracijo
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

**Za Apache:**
```bash
# Preveri konfiguracijo
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

---

## 🎯 Hitri Postopek (Če imaš deploy.sh)

Po sinhronizaciji datotek:

```bash
cd /var/www/merila-app
chmod +x deploy.sh
./deploy.sh --no-git
```

To bo naredilo vse avtomatsko:
- ✅ Composer install
- ✅ NPM install & build
- ✅ Migracije
- ✅ Cache optimizacije
- ✅ PHP-FPM reload

---

## ⚠️ Troubleshooting

### Problem: "composer: command not found"
```bash
# Namesti Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Problem: "npm: command not found"
```bash
# Namesti Node.js preko nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
nvm install 20
```

### Problem: "Permission denied" pri migracijah
```bash
# Nastavi dovoljenja
sudo chown -R www-data:www-data /var/www/merila-app
sudo chmod -R 775 /var/www/merila-app/storage
sudo chmod -R 775 /var/www/merila-app/bootstrap/cache
```

### Problem: "SQLSTATE[HY000] [2002] Connection refused"
- Preveri, da MySQL teče: `sudo systemctl status mysql`
- Preveri, da so podatki v `.env` pravilni
- Preveri, da MySQL posluša na pravilnem portu

### Problem: Aplikacija ne deluje
```bash
# Preveri log datoteke
tail -f storage/logs/laravel.log

# Preveri, da je APP_KEY nastavljen
php artisan key:generate

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## ✅ Checklist

- [ ] SSH povezava na strežnik deluje
- [ ] Datoteke so v `/var/www/merila-app`
- [ ] Composer paketi nameščeni
- [ ] NPM paketi nameščeni in frontend zgrajen
- [ ] `.env` datoteka nastavljena
- [ ] `APP_KEY` generiran
- [ ] Dooljenja nastavljena
- [ ] Migracije zagnane
- [ ] Cache optimizacije zagnane
- [ ] PHP-FPM reloadan
- [ ] Web server konfiguriran
- [ ] Aplikacija dostopna v brskalniku

---

**Zadnja posodobitev:** 2026-01-23
