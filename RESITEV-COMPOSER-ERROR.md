# Rešitev: Composer ne najde composer.json

## Problem
```
Composer could not find a composer.json file in /var/www/merila-app
```

To pomeni, da datoteke niso v pravilnem direktoriju ali niso bile pravilno sinhronizirane.

## Rešitev

### 1. Preveri, kje so datoteke

Na strežniku zaženi:

```bash
# Preveri trenutni direktorij
pwd

# Preveri, kaj je v direktoriju
ls -la

# Preveri, če so datoteke morda v poddirektoriju
find /var/www -name "composer.json" -type f 2>/dev/null
```

### 2. Preveri strukturo direktorijev

```bash
# Preveri, ali obstaja composer.json
ls -la /var/www/merila-app/composer.json

# Preveri strukturo
ls -la /var/www/merila-app/
```

### 3. Možne rešitve

#### Možnost A: Datoteke so v poddirektoriju

Če so datoteke v npr. `/var/www/merila-app/merila-app/` ali podobno:

```bash
# Preveri
ls -la /var/www/merila-app/

# Če so v poddirektoriju, jih premakni
cd /var/www/merila-app
mv merila-app/* .
mv merila-app/.* . 2>/dev/null || true
rmdir merila-app
```

#### Možnost B: Datoteke niso bile sinhronizirane

Preveri v WinSCP:
1. Odpri WinSCP
2. Poveži se na strežnik
3. Pojdi v `/var/www/merila-app`
4. Preveri, ali vidiš `composer.json`, `package.json`, `artisan`, itd.

Če datotek ni:
- Ponovno sinhroniziraj datoteke
- Preveri, da si sinhroniziral iz pravilne lokalne mape (`c:\Projekt\merila 37.001`)
- Preveri, da si sinhroniziral v pravilno oddaljeno mapo (`/var/www/merila-app`)

#### Možnost C: Sinhroniziraj ponovno

V WinSCP:
1. **Synchronize** (`Ctrl+S`)
2. **Local directory:** `c:\Projekt\merila 37.001`
3. **Remote directory:** `/var/www/merila-app`
4. **Direction:** `Remote` (ali `Both`)
5. **Exclude:** `vendor/;node_modules/;.git/;.env`
6. Klikni **Compare**
7. Preveri, katere datoteke se bodo prenesle
8. Klikni **Synchronize**

### 4. Preveri, da so vse datoteke na mestu

Po sinhronizaciji na strežniku zaženi:

```bash
cd /var/www/merila-app

# Preveri glavne datoteke
ls -la composer.json package.json artisan .env.example

# Preveri glavne direktorije
ls -d app/ config/ database/ resources/ routes/ public/ bootstrap/
```

Morali bi videti vse te datoteke in direktorije.

### 5. Ko so datoteke na mestu, zaženi deploy

```bash
cd /var/www/merila-app
./deploy.sh --no-git
```

## Hitri Preverjanje

Zaženi to na strežniku, da hitro preveriš:

```bash
cd /var/www/merila-app
echo "=== Trenutni direktorij ==="
pwd
echo ""
echo "=== Datoteke v direktoriju ==="
ls -la | head -20
echo ""
echo "=== Preveri composer.json ==="
test -f composer.json && echo "✓ composer.json obstaja" || echo "✗ composer.json NE obstaja"
echo ""
echo "=== Preveri package.json ==="
test -f package.json && echo "✓ package.json obstaja" || echo "✗ package.json NE obstaja"
echo ""
echo "=== Preveri artisan ==="
test -f artisan && echo "✓ artisan obstaja" || echo "✗ artisan NE obstaja"
echo ""
echo "=== Iskanje composer.json ==="
find /var/www -name "composer.json" -type f 2>/dev/null
```

## Če še vedno ne deluje

1. **Preveri WinSCP sinhronizacijo:**
   - Ali si sinhroniziral iz pravilne lokalne mape?
   - Ali si sinhroniziral v pravilno oddaljeno mapo?
   - Ali so bile datoteke dejansko prenesene?

2. **Ročno preveri strukturo:**
   ```bash
   # Na strežniku
   cd /var/www/merila-app
   tree -L 2  # če je nameščen tree
   # ali
   find . -maxdepth 2 -type f -name "*.json" -o -name "artisan"
   ```

3. **Če datoteke res niso na strežniku:**
   - Ponovno sinhroniziraj iz WinSCP
   - Preveri, da nisi izključil pomembnih datotek v Exclude nastavitvah
