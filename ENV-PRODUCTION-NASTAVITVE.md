# Podrobna Razlaga Nastavitev .env Datoteke za Produkcijo

## 📋 Pregled

Ta dokument podrobno razlaga vse nastavitve v `.env` datoteki, ki so potrebne za pravilno delovanje aplikacije v produkcijskem okolju.

---

## 🔧 Osnovne Nastavitve Aplikacije

### `APP_NAME`
```env
APP_NAME="Merila 37.001"
```
**Razlaga:**
- Ime aplikacije, ki se uporablja v različnih kontekstih (emaili, logi, cache prefixi)
- **Produkcija:** Nastavite na dejansko ime aplikacije
- **Varnost:** Ne vključuje občutljivih podatkov

### `APP_ENV`
```env
APP_ENV=production
```
**Razlaga:**
- Določa okolje aplikacije (`local`, `staging`, `production`)
- **Produkcija:** **VEDNO** nastavite na `production`
- **Vpliv:**
  - Omogoča produkcijske optimizacije
  - Skrije debug informacije
  - Aktivira produkcijske cache mehanizme
  - Spremeni obnašanje error handlinga

**⚠️ Pomembno:** Nikoli ne nastavite na `local` ali `development` v produkciji!

### `APP_KEY`
```env
APP_KEY=base64:VašGeneriraniKljučTukaj
```
**Razlaga:**
- 32-bitni šifrirni ključ za Laravel
- **Produkcija:** **MORA** biti nastavljen in **UNIKATEN** za vsako aplikacijo
- **Uporaba:** Za šifriranje podatkov, session cookie-je, password reset token-e, itd.
- **Varnost:** 
  - Ne delite tega ključa
  - Ne commitajte v git
  - Če ga spremenite, bodo vsi šifrirani podatki neuporabni

**Kako generirati APP_KEY:**

#### 1. V Docker okolju (priporočeno za ta projekt):
```powershell
# Windows PowerShell
docker compose exec laravel.test php artisan key:generate
```

```bash
# Linux/Mac
docker compose exec laravel.test php artisan key:generate
```

#### 2. Brez Docker (če imate PHP nameščen lokalno):
```bash
php artisan key:generate
```

#### 3. Kje se shrani:
- Ukaz **avtomatsko** posodobi `.env` datoteko
- Ključ se doda v vrstico: `APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
- **Format:** Vedno se začne z `base64:`, sledi 44 znakov dolg niz

#### 4. Preverjanje:
Po generiranju preverite `.env` datoteko:
```env
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Pričakovani izhod ukaza:**
```
Application key set successfully.
```

#### 5. Kdaj generirati:
- ✅ **Prvič** ob namestitvi aplikacije
- ✅ **Vedno** ko kopirate aplikacijo na nov server
- ✅ **Nikoli** ne generirajte znova, če aplikacija že deluje (izgubili boste dostop do šifriranih podatkov!)

#### 6. Če pozabite generirati:
Aplikacija vam bo prikazala napako:
```
RuntimeException: No application encryption key has been specified.
```
Rešitev: Zaženite `php artisan key:generate`

#### 7. Za produkcijski server:
```bash
# Prepričajte se, da ste v pravi mapi
cd /path/to/your/application

# Generirajte ključ
php artisan key:generate

# Preverite, da je ključ nastavljen
grep APP_KEY .env
```

**⚠️ KRITIČNO POMEMBNO - PRODUKCIJSKI SERVER:**

**✅ DA - Generirajte APP_KEY na produkcijskem serverju!**

**Zakaj mora biti APP_KEY generiran na produkcijskem serverju:**

1. **Varnost:**
   - Vsak server mora imeti svoj unikaten ključ
   - Če bi kopirali ključ iz development okolja, bi bilo to varnostno tveganje
   - Če bi bil development ključ kompromitiran, bi to vplivalo na produkcijo

2. **Šifriranje podatkov:**
   - APP_KEY se uporablja za šifriranje občutljivih podatkov
   - Session cookie-ji so šifrirani s tem ključem
   - Password reset token-i so šifrirani s tem ključem
   - Če bi uporabili isti ključ, bi lahko nekdo z development ključem dešifriral produkcijske podatke

3. **Izolacija okolij:**
   - Development in produkcija morata biti popolnoma ločeni
   - Vsako okolje mora imeti svoje varnostne ključe

**Postopek za produkcijski server:**

```bash
# 1. Povežite se na produkcijski server (SSH)
ssh user@produkcijski-server.si

# 2. Navigirajte v mapo aplikacije
cd /var/www/merila

# 3. Preverite, da .env datoteka obstaja
ls -la .env

# 4. Če .env ne obstaja, kopirajte .env.example
cp .env.example .env

# 5. Generirajte APP_KEY NA PRODUKCIJSKEM SERVERJU
php artisan key:generate

# 6. Preverite, da je ključ nastavljen
grep APP_KEY .env
```

**❌ NIKOLI ne naredite tega:**
- ❌ Kopiranje APP_KEY iz development okolja v produkcijo
- ❌ Uporaba istega APP_KEY za več serverjev
- ❌ Commit APP_KEY v git repozitorij
- ❌ Deljenje APP_KEY med različnimi aplikacijami

**✅ Vedno naredite:**
- ✅ Generirajte nov APP_KEY na vsakem serverju
- ✅ Uporabite različne APP_KEY za development, staging in produkcijo
- ✅ Shranite APP_KEY varno (samo v .env datoteki na serverju)
- ✅ Preverite, da je .env v .gitignore

**⚠️ POMEMBNO:**
- Vsaka instalacija aplikacije mora imeti **svoj unikaten** APP_KEY
- **Nikoli** ne kopirajte APP_KEY iz ene instalacije v drugo
- **Nikoli** ne commitajte `.env` datoteke v git (preverite `.gitignore`)
- **Vedno** generirajte APP_KEY na produkcijskem serverju, ne lokalno!

### `APP_DEBUG`
```env
APP_DEBUG=false
```
**Razlaga:**
- Omogoča ali onemogoča debug način
- **Produkcija:** **VEDNO** nastavite na `false`
- **Razlogi:**
  - ❌ `true` prikazuje občutljive informacije (stack trace, SQL poizvedbe, spremenljivke)
  - ❌ `true` je počasnejši (dodatni overhead za debug informacije)
  - ❌ `true` predstavlja varnostno tveganje
- **Development:** `true` (za lažje debugiranje)

### `APP_URL`
```env
APP_URL=https://vasadomena.si
```
**Razlaga:**
- Osnovni URL aplikacije
- **Produkcija:** Nastavite na dejanski produkcijski URL
- **Format:** `https://domena.si` (brez končnega `/`)
- **Uporaba:**
  - Generiranje URL-jev v emailih
  - Redirecti
  - Asset URL-ji
- **⚠️ Pomembno:** Uporabite `https://` v produkciji!

### `APP_TIMEZONE`
```env
APP_TIMEZONE=Europe/Ljubljana
```
**Razlaga:**
- Časovni pas aplikacije
- **Produkcija:** Nastavite na pravilni časovni pas
- **Primeri:** `Europe/Ljubljana`, `UTC`, `Europe/London`

### `APP_LOCALE`
```env
APP_LOCALE=sl
```
**Razlaga:**
- Privzeti jezik aplikacije
- **Produkcija:** Nastavite na glavni jezik uporabnikov

### `APP_FALLBACK_LOCALE`
```env
APP_FALLBACK_LOCALE=en
```
**Razlaga:**
- Rezervni jezik, če prevod ni na voljo
- **Produkcija:** Običajno `en`

### `APP_FAKER_LOCALE`
```env
APP_FAKER_LOCALE=sl_SI
```
**Razlaga:**
- Lokalizacija za testne podatke (Faker)
- **Produkcija:** Običajno enako kot `APP_LOCALE`

---

## 🗄️ Nastavitve Podatkovne Baze

### `DB_CONNECTION`
```env
DB_CONNECTION=mysql
```
**Razlaga:**
- Tip podatkovne baze
- **Možnosti:** `mysql`, `mariadb`, `pgsql`, `sqlite`, `sqlsrv`
- **Produkcija:** Običajno `mysql` ali `mariadb`
- **Development:** Lahko `sqlite` za hitrejši razvoj

### `DB_HOST`
```env
DB_HOST=127.0.0.1
```
**Razlaga:**
- Naslov strežnika podatkovne baze
- **Produkcija:** 
  - Lokalni strežnik: `127.0.0.1` ali `localhost`
  - Oddaljen strežnik: IP naslov ali domena
- **Docker:** Če je MySQL v Docker containerju, uporabite ime servisa (npr. `mysql`)

### `DB_PORT`
```env
DB_PORT=3306
```
**Razlaga:**
- Vrata podatkovne baze
- **MySQL/MariaDB:** `3306` (privzeto)
- **PostgreSQL:** `5432`
- **SQL Server:** `1433`
- **Produkcija:** Običajno privzeta vrednost, razen če je spremenjena

### `DB_DATABASE`
```env
DB_DATABASE=merila_production
```
**Razlaga:**
- Ime podatkovne baze
- **Produkcija:** Uporabite opisno ime (npr. `merila_production`)
- **⚠️ Pomembno:** Baza mora že obstajati!

### `DB_USERNAME`
```env
DB_USERNAME=merila_user
```
**Razlaga:**
- Uporabniško ime za dostop do baze
- **Produkcija:** 
  - Ustvarite dedikiranega uporabnika (ne `root`!)
  - Dajte mu samo potrebne pravice
- **Varnost:** Uporabite močno geslo

### `DB_PASSWORD`
```env
DB_PASSWORD=VašeMočnoGeslo123!
```
**Razlaga:**
- Geslo za dostop do baze
- **Produkcija:** 
  - Uporabite **močno geslo** (min. 16 znakov, mešanica)
  - Ne commitajte v git
  - Shranite v varnem mestu
- **⚠️ Varnost:** To je ena najpomembnejših nastavitev!

### `DB_CHARSET` (opcijsko)
```env
DB_CHARSET=utf8mb4
```
**Razlaga:**
- Kodiranje znakov
- **Produkcija:** `utf8mb4` (podpira emoji in vse Unicode znake)
- **Privzeto:** `utf8mb4` za MySQL/MariaDB

### `DB_COLLATION` (opcijsko)
```env
DB_COLLATION=utf8mb4_unicode_ci
```
**Razlaga:**
- Pravila za primerjavo znakov
- **Produkcija:** `utf8mb4_unicode_ci` (najboljša podpora za slovenščino)
- **Privzeto:** `utf8mb4_unicode_ci` za MySQL/MariaDB

---

## 🔴 Redis Nastavitve

### `REDIS_CLIENT`
```env
REDIS_CLIENT=phpredis
```
**Razlaga:**
- PHP knjižnica za Redis
- **Možnosti:** `phpredis` (hitrejši, C extension) ali `predis` (čisti PHP)
- **Produkcija:** `phpredis` (zahteva PHP extension)
- **Fallback:** `predis` (če `phpredis` ni na voljo)

### `REDIS_HOST`
```env
REDIS_HOST=127.0.0.1
```
**Razlaga:**
- Naslov Redis strežnika
- **Produkcija:**
  - Lokalni: `127.0.0.1` ali `localhost`
  - Oddaljen: IP ali domena
- **Docker:** Če je Redis v containerju, uporabite ime servisa (npr. `redis`)

### `REDIS_PASSWORD`
```env
REDIS_PASSWORD=null
```
**Razlaga:**
- Geslo za Redis (če je zahtevano)
- **Produkcija:** 
  - Če Redis ni zaščiten: `null` ali pustite prazno
  - Če je zaščiten: nastavite močno geslo
- **Varnost:** V produkciji priporočeno zaščititi Redis z geslom

### `REDIS_PORT`
```env
REDIS_PORT=6379
```
**Razlaga:**
- Vrata Redis strežnika
- **Privzeto:** `6379`
- **Produkcija:** Običajno privzeta vrednost

### `REDIS_DB`
```env
REDIS_DB=0
```
**Razlaga:**
- Številka Redis podatkovne baze (0-15)
- **Produkcija:** `0` za splošne podatke
- **Uporaba:** Redis ima 16 ločenih "baz" (0-15)

### `REDIS_CACHE_DB`
```env
REDIS_CACHE_DB=1
```
**Razlaga:**
- Redis baza za cache podatke
- **Produkcija:** `1` (ločeno od glavne baze)
- **Razlog:** Ločitev cache podatkov od drugih podatkov

### `REDIS_PREFIX` (opcijsko)
```env
REDIS_PREFIX=merila-production-
```
**Razlaga:**
- Predpona za vse Redis ključe
- **Produkcija:** Uporabite opisno predpono
- **Razlog:** Če delite Redis z drugimi aplikacijami, preprečite konflikte
- **Privzeto:** Avtomatsko generirano iz `APP_NAME`

### `REDIS_CLUSTER` (opcijsko)
```env
REDIS_CLUSTER=redis
```
**Razlaga:**
- Način Redis clusterja
- **Produkcija:** `redis` za običajno uporabo
- **Napredno:** Za Redis cluster uporabite `redis-cluster`

### `REDIS_PERSISTENT` (opcijsko)
```env
REDIS_PERSISTENT=false
```
**Razlaga:**
- Ali naj se vzpostavi trajna povezava
- **Produkcija:** `false` (običajno)
- **Napredno:** `true` za boljšo zmogljivost pri velikem številu povezav

---

## 💾 Cache Nastavitve

### `CACHE_STORE`
```env
CACHE_STORE=redis
```
**Razlaga:**
- Driver za cache sistem
- **Možnosti:** `file`, `database`, `redis`, `memcached`, `array`
- **Produkcija:** 
  - **Najboljša izbira:** `redis` (zelo hitro)
  - **Alternativa:** `file` (če Redis ni na voljo)
- **Razlike:**
  - `redis`: Najhitrejši, primeren za več serverjev
  - `file`: Počasnejši, vendar enostavnejši
  - `database`: Počasnejši, vendar deluje povsod
  - `array`: Samo za testiranje (ne shranjuje med zahtevami)

### `CACHE_PREFIX` (opcijsko)
```env
CACHE_PREFIX=merila-production-cache-
```
**Razlaga:**
- Predpona za cache ključe
- **Produkcija:** Avtomatsko generirano iz `APP_NAME`
- **Razlog:** Preprečite konflikte z drugimi aplikacijami

---

## 🍪 Session Nastavitve

### `SESSION_DRIVER`
```env
SESSION_DRIVER=redis
```
**Razlaga:**
- Kje se shranjujejo seje uporabnikov
- **Možnosti:** `file`, `database`, `redis`, `cookie`, `array`
- **Produkcija:**
  - **Najboljša izbira:** `redis` (hitro, deluje med več serverji)
  - **Alternativa:** `database` (če Redis ni na voljo)
  - **Ne uporabljajte:** `file` (ne deluje z več serverji)
- **Razlike:**
  - `redis`: Najhitrejši, primeren za load balancing
  - `database`: Zanesljiv, deluje povsod
  - `file`: Počasnejši, ne deluje z več serverji
  - `cookie`: Omejeno (4KB), varnostno tveganje

### `SESSION_LIFETIME`
```env
SESSION_LIFETIME=120
```
**Razlaga:**
- Trajanje seje v minutah
- **Produkcija:** `120` (2 uri) je dobra izbira
- **Razlogi:**
  - Prekratko: Uporabniki se pogosto odjavljajo
  - Predolgo: Varnostno tveganje
- **Privzeto:** `120` minut

### `SESSION_ENCRYPT`
```env
SESSION_ENCRYPT=false
```
**Razlaga:**
- Ali naj se podatki seje šifrirajo
- **Produkcija:** `false` (običajno)
- **Napredno:** `true` za dodatno varnost (overhead)

### `SESSION_SECURE_COOKIE`
```env
SESSION_SECURE_COOKIE=true
```
**Razlaga:**
- Ali naj se cookie pošlje samo preko HTTPS
- **Produkcija:** **VEDNO** `true` (če uporabljate HTTPS)
- **⚠️ Pomembno:** Če je `true` brez HTTPS, seje ne bodo delovale!

### `SESSION_HTTP_ONLY`
```env
SESSION_HTTP_ONLY=true
```
**Razlaga:**
- Prepreči JavaScript dostop do cookie-ja
- **Produkcija:** **VEDNO** `true` (zaščita pred XSS)
- **⚠️ Varnost:** Nikoli ne nastavite na `false`!

### `SESSION_SAME_SITE`
```env
SESSION_SAME_SITE=lax
```
**Razlaga:**
- Zaščita pred CSRF napadi
- **Možnosti:** `lax`, `strict`, `none`
- **Produkcija:** `lax` (dobro ravnovesje med varnostjo in funkcionalnostjo)
- **Razlike:**
  - `lax`: Dovoli cross-site GET zahteve (priporočeno)
  - `strict`: Najbolj varno, vendar lahko povzroča težave
  - `none`: Zahteva `SESSION_SECURE_COOKIE=true`

---

## 📬 Queue (Čakalne Vrste) Nastavitve

### `QUEUE_CONNECTION`
```env
QUEUE_CONNECTION=database
```
**Razlaga:**
- Kje se shranjujejo čakalne vrste
- **Možnosti:** `sync`, `database`, `redis`, `sqs`, `beanstalkd`
- **Produkcija:**
  - **Za manjše aplikacije:** `database` (enostavno)
  - **Za večje aplikacije:** `redis` (hitrejši)
- **Razlike:**
  - `sync`: Izvaja takoj (za testiranje)
  - `database`: Zanesljiv, deluje povsod
  - `redis`: Hitrejši, primeren za večje obremenitve
- **⚠️ Pomembno:** Če uporabljate `database` ali `redis`, zaženite worker:
  ```bash
  php artisan queue:work
  ```

---

## 📧 Mail Nastavitve

### `MAIL_MAILER`
```env
MAIL_MAILER=smtp
```
**Razlaga:**
- Tip mail strežnika
- **Možnosti:** `smtp`, `sendmail`, `mailgun`, `ses`, `postmark`
- **Produkcija:** `smtp` (najpogostejši)

### `MAIL_HOST`
```env
MAIL_HOST=smtp.gmail.com
```
**Razlaga:**
- Naslov SMTP strežnika
- **Produkcija:** Naslov vašega mail providerja
- **Primeri:**
  - Gmail: `smtp.gmail.com`
  - Outlook: `smtp-mail.outlook.com`
  - Lastni strežnik: `mail.vasadomena.si`

### `MAIL_PORT`
```env
MAIL_PORT=587
```
**Razlaga:**
- Vrata SMTP strežnika
- **Produkcija:** 
  - `587` za TLS (priporočeno)
  - `465` za SSL
  - `25` za nešifrirano (ne priporočeno)

### `MAIL_USERNAME`
```env
MAIL_USERNAME=vas@email.com
```
**Razlaga:**
- Uporabniško ime za SMTP
- **Produkcija:** Email naslov ali uporabniško ime

### `MAIL_PASSWORD`
```env
MAIL_PASSWORD=VašeGeslo
```
**Razlaga:**
- Geslo za SMTP
- **Produkcija:** Geslo za email račun
- **⚠️ Varnost:** Ne commitajte v git!

### `MAIL_ENCRYPTION`
```env
MAIL_ENCRYPTION=tls
```
**Razlaga:**
- Tip šifriranja
- **Možnosti:** `tls`, `ssl`, `null`
- **Produkcija:** `tls` (priporočeno) ali `ssl`

### `MAIL_FROM_ADDRESS`
```env
MAIL_FROM_ADDRESS=noreply@vasadomena.si
```
**Razlaga:**
- Privzeti pošiljatelj emailov
- **Produkcija:** Nastavite na veljaven email naslov vaše domene

### `MAIL_FROM_NAME`
```env
MAIL_FROM_NAME="${APP_NAME}"
```
**Razlaga:**
- Ime pošiljatelja
- **Produkcija:** Ime aplikacije ali podjetja

---

## 📝 Logging Nastavitve

### `LOG_CHANNEL`
```env
LOG_CHANNEL=stack
```
**Razlaga:**
- Kanal za beleženje
- **Možnosti:** `stack`, `single`, `daily`, `syslog`, `errorlog`
- **Produkcija:** `daily` (ločene datoteke za vsak dan)
- **Razlike:**
  - `daily`: Ločene datoteke za vsak dan (priporočeno)
  - `single`: Ena datoteka (lahko postane velika)
  - `stack`: Kombinacija več kanalov

### `LOG_LEVEL`
```env
LOG_LEVEL=error
```
**Razlaga:**
- Minimalna stopnja za beleženje
- **Možnosti:** `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`
- **Produkcija:** `error` (beleži samo napake in kritične dogodke)
- **Development:** `debug` (beleži vse)
- **⚠️ Pomembno:** V produkciji ne uporabljajte `debug` (veliko podatkov, počasneje)!

### `LOG_DEPRECATIONS_CHANNEL` (opcijsko)
```env
LOG_DEPRECATIONS_CHANNEL=null
```
**Razlaga:**
- Kanal za opozorila o zastarelih funkcijah
- **Produkcija:** `null` (ne beleži) ali `daily` (če želite spremljati)

---

## 🔒 Varnostne Nastavitve

### `SANCTUM_STATEFUL_DOMAINS` (opcijsko)
```env
SANCTUM_STATEFUL_DOMAINS=vasadomena.si,www.vasadomena.si
```
**Razlaga:**
- Domene za Sanctum API avtentikacijo
- **Produkcija:** Nastavite na vaše domene (ločene z vejico)

### `SESSION_DOMAIN` (opcijsko)
```env
SESSION_DOMAIN=.vasadomena.si
```
**Razlaga:**
- Domena za session cookie-je
- **Produkcija:** 
  - Za poddomene: `.vasadomena.si` (pika na začetku)
  - Za glavno domeno: `vasadomena.si` ali pustite prazno

---

## ⚡ Optimizacijske Nastavitve

### `BROADCAST_DRIVER` (opcijsko)
```env
BROADCAST_DRIVER=log
```
**Razlaga:**
- Driver za real-time broadcasting
- **Možnosti:** `log`, `pusher`, `redis`, `null`
- **Produkcija:** `log` (če ne uporabljate) ali `redis` (če uporabljate)

### `FILESYSTEM_DISK`
```env
FILESYSTEM_DISK=local
```
**Razlaga:**
- Privzeti disk za shranjevanje datotek
- **Možnosti:** `local`, `public`, `s3`, `ftp`
- **Produkcija:** `local` (lokalni disk) ali `s3` (Amazon S3)

---

## 📋 Primer Popolne .env Datoteke za Produkcijo

```env
# ============================================
# OSNOVNE NASTAVITVE APLIKACIJE
# ============================================
APP_NAME="Merila 37.001"
APP_ENV=production
APP_KEY=base64:VašGeneriraniKljučTukaj
APP_DEBUG=false
APP_URL=https://vasadomena.si
APP_TIMEZONE=Europe/Ljubljana
APP_LOCALE=sl
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=sl_SI

# ============================================
# PODATKOVNA BAZA
# ============================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=merila_production
DB_USERNAME=merila_user
DB_PASSWORD=VašeMočnoGeslo123!
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# ============================================
# REDIS
# ============================================
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_PREFIX=merila-production-

# ============================================
# CACHE
# ============================================
CACHE_STORE=redis
CACHE_PREFIX=merila-production-cache-

# ============================================
# SESSION
# ============================================
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# ============================================
# QUEUE
# ============================================
QUEUE_CONNECTION=database

# ============================================
# MAIL
# ============================================
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=vas@email.com
MAIL_PASSWORD=VašeGeslo
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@vasadomena.si
MAIL_FROM_NAME="${APP_NAME}"

# ============================================
# LOGGING
# ============================================
LOG_CHANNEL=daily
LOG_LEVEL=error
LOG_DEPRECATIONS_CHANNEL=null

# ============================================
# FILESYSTEM
# ============================================
FILESYSTEM_DISK=local

# ============================================
# BROADCASTING
# ============================================
BROADCAST_DRIVER=log
```

---

## ✅ Checklist za Produkcijo

Pred zagonom aplikacije v produkciji preverite:

- [ ] `APP_ENV=production` (nikoli `local` ali `development`)
- [ ] `APP_DEBUG=false` (nikoli `true`)
- [ ] `APP_KEY` je nastavljen in unikaten
- [ ] `APP_URL` uporablja `https://`
- [ ] `DB_PASSWORD` je močno geslo
- [ ] `DB_USERNAME` ni `root`
- [ ] `REDIS_PASSWORD` je nastavljen (če je Redis zaščiten)
- [ ] `SESSION_SECURE_COOKIE=true` (če uporabljate HTTPS)
- [ ] `SESSION_HTTP_ONLY=true`
- [ ] `LOG_LEVEL=error` (ne `debug`)
- [ ] `MAIL_*` nastavitve so pravilne
- [ ] Vse gesla so močna in varna
- [ ] `.env` datoteka **NI** v git repozitoriju

---

## 🔐 Varnostni Nasveti

1. **Nikoli ne commitajte `.env` datoteke v git!**
   - Preverite, da je v `.gitignore`

2. **Uporabite močna gesla:**
   - Min. 16 znakov
   - Mešanica velikih/malih črk, številk, simbolov

3. **HTTPS v produkciji:**
   - Vedno uporabljajte `https://` za `APP_URL`
   - Nastavite `SESSION_SECURE_COOKIE=true`

4. **Redis varnost:**
   - V produkciji zaščitite Redis z geslom
   - Ne izpostavljajte Redis na javnem omrežju

5. **Database varnost:**
   - Ustvarite dedikiranega uporabnika (ne `root`)
   - Dajte mu samo potrebne pravice
   - Ne izpostavljajte baze na javnem omrežju

6. **Redno posodabljanje:**
   - Redno posodabljajte Laravel in odvisnosti
   - Spremljajte varnostne opozorila

---

## 🆘 Pogosta Vprašanja

### Q: Ali moram uporabljati Redis?
**A:** Ne, vendar je priporočeno za boljšo zmogljivost. Za manjše aplikacije je `file` cache dovolj dober.

### Q: Kaj če Redis ni na voljo?
**A:** Nastavite `CACHE_STORE=file` in `SESSION_DRIVER=database`. Aplikacija bo delovala, vendar počasneje.

### Q: Kako generiram APP_KEY?
**A:** Zaženite `php artisan key:generate` v terminalu.

### Q: Ali moram spremeniti vse nastavitve?
**A:** Ne, samo tiste, ki so pomembne za vaše okolje. Osnovne nastavitve (APP_ENV, APP_DEBUG, DB_*) so obvezne.

### Q: Kaj če pozabim nastaviti APP_DEBUG=false?
**A:** To je **varnostno tveganje**! Aplikacija bo prikazovala občutljive informacije vsem uporabnikom.

---

## 📚 Dodatni Viri

- [Laravel Configuration Documentation](https://laravel.com/docs/configuration)
- [Laravel Environment Configuration](https://laravel.com/docs/configuration#environment-configuration)
- [Redis Documentation](https://redis.io/documentation)
- `PRODUCTION-OPTIMIZATION.md` - Optimizacije za produkcijo
- `REDIS-IN-CDN-RAZLAGA.md` - Razlaga Redis in CDN

---

**Zadnja posodobitev:** Januar 2026
