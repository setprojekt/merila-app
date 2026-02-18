# Kaj točno izbrisati/izključiti pri prenosu preko FileZille

Natančen seznam datotek in map, ki jih **NE prenašajte** pri prenosu preko FileZille.

---

## 1. `bootstrap/` mapa - brez cache datotek

### ✅ PRENESI:
- `bootstrap/app.php`
- `bootstrap/providers.php`
- `bootstrap/cache/.gitignore` (samo ta datoteka!)

### ❌ NE PRENESI:
- `bootstrap/cache/*.php` - vse PHP datoteke v cache mapi
- `bootstrap/cache/config.php`
- `bootstrap/cache/routes.php`
- `bootstrap/cache/services.php`
- `bootstrap/cache/packages.php`
- `bootstrap/cache/events.php`
- Vse ostale datoteke v `bootstrap/cache/` (razen `.gitignore`)

**Praktično:** V FileZilli prenesite celotno `bootstrap/` mapo, vendar **ročno izbrišite** na strežniku vse `.php` datoteke iz `bootstrap/cache/` (pustite samo `.gitignore`).

---

## 2. `public/` mapa - brez build/hot/storage

### ✅ PRENESI:
- `public/.htaccess`
- `public/index.php`
- `public/favicon.ico`
- `public/robots.txt`
- `public/css/` - celotna mapa
- `public/js/` - celotna mapa

### ❌ NE PRENESI:
- `public/build/` - **celotna mapa** (se generira z `npm run build`)
- `public/hot` - **datoteka** (development datoteka)
- `public/storage` - **symlink ali mapa** (se ustvari z `php artisan storage:link`)

**Praktično:** V FileZilli prenesite `public/`, vendar **preverite**, da v njej ni:
- `build/` mape
- `hot` datoteke
- `storage` symlinka/mape

Če te obstajajo lokalno, jih **izključite** iz prenosa ali **ročno izbrišite** na strežniku po prenosu.

---

## 3. `storage/` mapa - struktura map brez vsebine

### ✅ PRENESI:
- **Strukturo map:**
  - `storage/app/`
  - `storage/app/.gitignore`
  - `storage/app/private/`
  - `storage/app/private/.gitignore`
  - `storage/app/public/`
  - `storage/app/public/.gitignore`
  - `storage/framework/`
  - `storage/framework/.gitignore`
  - `storage/framework/cache/`
  - `storage/framework/cache/.gitignore`
  - `storage/framework/cache/data/`
  - `storage/framework/cache/data/.gitignore`
  - `storage/framework/sessions/`
  - `storage/framework/sessions/.gitignore`
  - `storage/framework/testing/`
  - `storage/framework/testing/.gitignore`
  - `storage/framework/views/`
  - `storage/framework/views/.gitignore`
  - `storage/logs/`
  - `storage/logs/.gitignore`

### ❌ NE PRENESI:
- `storage/logs/*.log` - vse log datoteke
- `storage/framework/cache/data/*` - vse datoteke v cache/data (razen `.gitignore`)
- `storage/framework/sessions/*` - vse session datoteke (razen `.gitignore`)
- `storage/framework/views/*.php` - vse compiled view datoteke (razen `.gitignore`)
- `storage/app/*` - vse datoteke v app (razen `.gitignore` in strukture map)

**Praktično:** V FileZilli prenesite `storage/` mapo, vendar **preverite**, da v njej ni:
- Datotek z razširitvijo `.log`
- PHP datotek (razen če so to `.gitignore`)
- Drugih datotek (razen `.gitignore`)

Če te obstajajo lokalno, jih **izključite** iz prenosa ali **ročno izbrišite** na strežniku po prenosu.

---

## 4. `phpunit.xml` - opcijsko

**Če nimate `phpunit.xml` v root mapi:** To je v redu, ni obvezno. Aplikacija bo delovala brez njega.

**Če ga imate:** Prenesite ga (ni problem, če ga prenesete).

---

## 5. Hitri pregled - kaj preveriti po prenosu

Po prenosu na strežniku preverite:

```bash
# Preveri bootstrap/cache - mora biti samo .gitignore
ls -la bootstrap/cache/
# Mora pokazati samo .gitignore (in morda .gitignore v podmapah)

# Preveri public - ne sme biti build/, hot, storage
ls -la public/
# Ne sme biti: build/, hot, storage

# Preveri storage - samo struktura in .gitignore
find storage/ -type f ! -name ".gitignore" | head -20
# Ne sme biti datotek (razen .gitignore)
```

---

## 6. Alternativa: Prenos samo potrebnih datotek

Če se želite izogniti ročnemu brisanju, lahko v FileZilli **izključite** te datoteke/mape pred prenosom:

### V FileZilli (Site Manager → Advanced → Filename filters):

**Exclude files:**
```
*.log
bootstrap/cache/*.php
public/build/*
public/hot
public/storage
```

**Exclude directories:**
```
bootstrap/cache (vendar pustite .gitignore - to je težje, zato ročno)
public/build
public/storage
```

**Opomba:** FileZilla ne omogoča izključitve vsebine mape, vendar ohraniti `.gitignore`. Zato je najlažje:
1. Prenesti celotno strukturo
2. Na strežniku ročno izbrisati nepotrebne datoteke (glej zgoraj)

---

## 7. Skripta za čiščenje na strežniku (opcijsko)

Po prenosu lahko na strežniku zaženete:

```bash
cd /var/www/merila-app

# Izbriši cache datoteke iz bootstrap
find bootstrap/cache -name "*.php" -type f -delete

# Izbriši build, hot, storage iz public (če obstajajo)
rm -rf public/build public/hot public/storage

# Izbriši vsebino storage (ohrani strukturo in .gitignore)
find storage -type f ! -name ".gitignore" -delete
```

**⚠️ POZOR:** Ta skripta izbriše vsebino. Zaženite jo **samo po prvem prenosu**, ne ob posodobitvah (ker bi izbrisali npr. naložene datoteke).

---

**Skratka:** Prenesite strukturo map, vendar **izključite** cache datoteke, build/hot/storage iz public, in vsebino storage map (razen `.gitignore` datotek).
