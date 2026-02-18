# Hitra Namestitev - Kako Zažeti

## Možnost 1: Avtomatska Namestitev (Priporočeno)

Zaženite PowerShell skripto, ki bo naredila vse avtomatsko:

```powershell
cd "c:\Projekt\merila 37.001"
.\install.ps1
```

Skripta bo:
1. Preverila Docker
2. Kopirala .env datoteko
3. Posodobila .env z manjkajočimi spremenljivkami
4. Zagnala Docker kontejnerje
5. Namestila Composer pakete
6. Generirala aplikacijski ključ
7. Zagnala migracije
8. Vam pomagala namestiti Filament (interaktivno)

**Opomba:** Pri Filament namestitvi boste morali ročno vnesti:
- Username
- Email  
- Password

## Možnost 2: Ročna Namestitev

Če želite ročno izvesti korake, sledite [NAMESTITEV-PODROBNO.md](NAMESTITEV-PODROBNO.md)

## Preverjanje Statusa

### Preverite Docker kontejnerje:
```powershell
docker compose ps
```

Morali bi videti 4 kontejnerje z statusom "Up":
- laravel.test
- mysql
- redis
- mailpit

### Če kontejnerji niso zagnani:
```powershell
# Preverite Docker Desktop - mora biti zagnan
# Nato zaženite:
docker compose up -d

# Počakajte 1-2 minuti, da se kontejnerji zaženejo
# Preverite status:
docker compose ps
```

### Preverite Docker build proces:
```powershell
docker compose logs laravel.test
```

Če vidite, da se še gradi, počakajte, da se konča (lahko traja 5-10 minut prvič).

## Naslednji Koraki Ko So Kontejnerji Zagnani

Ko vidite, da so vsi kontejnerji "Up", zaženite:

```powershell
# 1. Namesti pakete (če še niso)
docker compose exec laravel.test composer install

# 2. Generiraj ključ
docker compose exec laravel.test php artisan key:generate

# 3. Namesti Filament
docker compose exec laravel.test php artisan filament:install --panels

# 4. Zaženi migracije
docker compose exec laravel.test php artisan migrate
```

## Pomoč

Če imate težave:
1. Preverite [NAMESTITEV-PODROBNO.md](NAMESTITEV-PODROBNO.md) za troubleshooting
2. Preverite Docker loge: `docker compose logs`
3. Preverite, ali Docker Desktop teče
