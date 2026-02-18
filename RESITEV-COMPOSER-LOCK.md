# Rešitev: Manjka composer.lock

## Problem
Composer poroča, da manjka `composer.lock` datoteka. To pomeni, da Composer ne ve, katere natančne verzije paketov naj namesti.

## Rešitev

### Možnost 1: Ustvari composer.lock lokalno (PRIPOROČENO)

Če imaš Docker zagnan lokalno:

```powershell
# V PowerShell v projektu
cd "c:\Projekt\merila 37.001"
docker compose exec laravel.test composer install
```

To bo:
- Namestilo pakete
- Ustvarilo `composer.lock` datoteko

Nato sinhroniziraj `composer.lock` na produkcijski server preko WinSCP.

### Možnost 2: Ustvari composer.lock na produkcijskem serverju

Na produkcijskem serverju zaženi:

```bash
cd /var/www/merila-app

# Ustvari composer.lock z namestitvijo paketov
composer install --no-dev --optimize-autoloader
```

**POMEMBNO:** Uporabi `composer install` (ne `composer update`), ker:
- `composer install` uporabi `composer.json` in ustvari `composer.lock`
- `composer update` posodobi pakete na najnovejše verzije

### Možnost 3: Posodobi deploy.sh

Če želiš, da `deploy.sh` avtomatsko ustvari `composer.lock`, če ne obstaja:

V `deploy.sh` spremeni:
```bash
# Iz:
composer install --no-dev --optimize-autoloader

# V:
if [ ! -f composer.lock ]; then
  echo "📦 Ustvarjanje composer.lock..."
  composer update --no-dev --optimize-autoloader
else
  composer install --no-dev --optimize-autoloader
fi
```

## Kaj je composer.lock?

`composer.lock` je datoteka, ki:
- Vsebuje natančne verzije vseh nameščenih paketov
- Zagotavlja, da se na vseh okoljih nameščajo iste verzije
- Pospešuje namestitev (Composer ne mora iskati najnovejših verzij)
- Je pomembna za produkcijo (stabilnost)

## Preverjanje

Po namestitvi preveri:

```bash
# Na produkcijskem serverju
cd /var/www/merila-app
test -f composer.lock && echo "✓ composer.lock obstaja" || echo "✗ composer.lock NE obstaja"
ls -lh composer.lock
```

## Naslednji Koraki

1. **Lokalno:** Zaženi `composer install` v Docker kontejnerju
2. **Sinhroniziraj:** Kopiraj `composer.lock` na produkcijski server
3. **Na produkciji:** Zaženi `./deploy.sh --no-git`

Ali pa direktno na produkciji:

```bash
cd /var/www/merila-app
composer install --no-dev --optimize-autoloader
./deploy.sh --no-git
```
