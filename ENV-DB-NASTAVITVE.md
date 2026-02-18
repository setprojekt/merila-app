# 🗄️ Pravilne DB_ nastavitve v .env datoteki

## 📋 **Osnovne DB nastavitve za MySQL/MariaDB**

Za produkcijski strežnik morate uporabiti **MySQL** ali **MariaDB** (ne SQLite).

### **Minimalne obvezne nastavitve:**

```env
# Tip podatkovne baze (OBVEZNO mysql za produkcijo!)
DB_CONNECTION=mysql

# Naslov strežnika baze podatkov
DB_HOST=127.0.0.1
# ALI če je baza na drugem strežniku:
# DB_HOST=192.168.1.100

# Vrata (privzeto 3306 za MySQL)
DB_PORT=3306

# Ime baze podatkov
DB_DATABASE=merila_production

# Uporabniško ime za dostop do baze
DB_USERNAME=merila_user

# Geslo za dostop do baze
DB_PASSWORD=močno_geslo_tukaj
```

---

## 🔧 **Dodatne opcijske nastavitve**

### **Za naprednejšo konfiguracijo:**

```env
# Unix socket (če uporabljate Unix socket namesto TCP/IP)
# DB_SOCKET=/var/run/mysqld/mysqld.sock

# Charset (privzeto utf8mb4 - priporočeno)
DB_CHARSET=utf8mb4

# Collation (privzeto utf8mb4_unicode_ci - priporočeno)
DB_COLLATION=utf8mb4_unicode_ci

# URL za povezavo (alternativa za DB_HOST, DB_PORT, itd.)
# DB_URL=mysql://username:password@host:port/database

# Foreign keys (privzeto true)
DB_FOREIGN_KEYS=true
```

---

## 📝 **Primer popolne DB konfiguracije za produkcijo**

```env
# ============================================
# DATABASE CONFIGURATION
# ============================================

# Tip povezave (mysql, mariadb, pgsql, sqlite, sqlsrv)
DB_CONNECTION=mysql

# Naslov strežnika
DB_HOST=127.0.0.1

# Vrata
DB_PORT=3306

# Ime baze podatkov
DB_DATABASE=merila_production

# Uporabniško ime
DB_USERNAME=merila_user

# Geslo (uporabite močno geslo!)
DB_PASSWORD=VašeMočnoGeslo123!@#

# Charset in collation (za podporo slovenskih znakov)
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

---

## ⚠️ **Pomembne opombe**

### **1. DB_CONNECTION**
- ✅ Za produkcijo: `mysql` ali `mariadb`
- ❌ Za produkcijo NE uporabljajte: `sqlite` (samo za razvoj)

### **2. DB_HOST**
- `127.0.0.1` ali `localhost` - če je baza na istem strežniku
- IP naslov ali domena - če je baza na drugem strežniku
- Primer: `192.168.1.100` ali `db.tvoja-domena.si`

### **3. DB_DATABASE**
- Ime baze mora biti **ustvarjeno v MySQL** preden zaženete migracije
- Uporabite opisno ime, npr. `merila_production`, `merila_app`, itd.

### **4. DB_USERNAME in DB_PASSWORD**
- Uporabnik mora imeti **pravice** za dostop do baze
- Geslo mora biti **močno** (vsaj 12 znakov, mešanica velikih/malih črk, številk, znakov)

### **5. Varnost**
- ❌ **NE** shranjujte `.env` datoteko v git
- ✅ `.env` datoteka mora biti na strežniku z **pravimi pravicami** (npr. 600)
- ✅ Uporabite **močna gesla**

---

## 🔍 **Preverjanje nastavitev**

### **1. Preveri, da so nastavitve pravilne:**

```bash
php artisan tinker
```

Nato v tinker:
```php
DB::connection()->getPdo();
// Če vrne PDO objekt, je povezava uspešna

DB::select('SELECT VERSION()');
// Prikaže verzijo MySQL
```

### **2. Testiraj povezavo:**

```bash
php artisan db:show
```

### **3. Preveri, da baza obstaja:**

```bash
mysql -u merila_user -p merila_production
```

---

## 🚨 **Pogoste napake in rešitve**

### **Napaka: "Access denied for user"**
**Problem:** Napačno uporabniško ime ali geslo
**Rešitev:**
```bash
# Preveri credentials v .env
# Preveri, da uporabnik obstaja v MySQL:
mysql -u root -p
```
```sql
SELECT User, Host FROM mysql.user WHERE User='merila_user';
```

### **Napaka: "Unknown database"**
**Problem:** Baza podatkov ne obstaja
**Rešitev:**
```bash
mysql -u root -p
```
```sql
CREATE DATABASE merila_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### **Napaka: "Connection refused"**
**Problem:** MySQL ne teče ali napačen port
**Rešitev:**
```bash
# Preveri, da MySQL teče
sudo systemctl status mysql

# Preveri port
netstat -tuln | grep 3306
```

### **Napaka: "Can't connect to MySQL server"**
**Problem:** Napačen DB_HOST ali firewall blokira
**Rešitev:**
- Preveri `DB_HOST` v `.env`
- Preveri firewall nastavitve
- Preveri, da MySQL posluša na pravi naslov

---

## 📊 **Primerjava: SQLite vs MySQL**

### **SQLite (samo za razvoj):**
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
# DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD niso potrebni
```

### **MySQL (za produkcijo):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=merila_production
DB_USERNAME=merila_user
DB_PASSWORD=geslo
```

---

## ✅ **Checklist za DB nastavitve**

- [ ] `DB_CONNECTION=mysql` (ne sqlite!)
- [ ] `DB_HOST` pravilno nastavljen (127.0.0.1 ali IP naslov)
- [ ] `DB_PORT` pravilno nastavljen (3306 za MySQL)
- [ ] `DB_DATABASE` ime baze pravilno
- [ ] `DB_USERNAME` uporabniško ime pravilno
- [ ] `DB_PASSWORD` geslo močno in varno
- [ ] Baza podatkov ustvarjena v MySQL
- [ ] Uporabnik ustvarjen z pravicami
- [ ] Povezava testirana (`php artisan tinker` → `DB::connection()->getPdo()`)
- [ ] Migracije zažene (`php artisan migrate --force`)

---

## 🔗 **Povezane datoteke**

- `config/database.php` - Konfiguracija podatkovne baze
- `DEPLOYMENT.md` - Navodila za deployment
- `DEPLOYMENT_CHECKLIST.md` - Checklist za deployment

---

**Zadnja posodobitev:** 2026-01-20
