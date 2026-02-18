# Navodila za Namestitev

## Korak 1: Kopiraj .env datoteko

```bash
cp .env.example .env
```

## Korak 2: Zaženi Docker kontejnerje

```bash
docker compose up -d
```

## Korak 3: Namesti Composer pakete

```bash
docker compose exec laravel.test composer install
```

## Korak 4: Generiraj aplikacijski ključ

```bash
docker compose exec laravel.test php artisan key:generate
```

## Korak 5: Namesti Filament

```bash
docker compose exec laravel.test php artisan filament:install --panels
```

## Korak 6: Zaženi migracije

```bash
docker compose exec laravel.test php artisan migrate
```

## Korak 7: Ustvari admin uporabnika

```bash
docker compose exec laravel.test php artisan make:filament-user
```

## Korak 8: Namesti NPM pakete (opcijsko)

```bash
docker compose exec laravel.test npm install
```

## Dostop do aplikacije

- Aplikacija: http://localhost
- Mailpit (email testing): http://localhost:8025

## Artisan ukazi

Vsi artisan ukazi se izvajajo preko Docker:

```bash
docker compose exec laravel.test php artisan <command>
```

## Composer ukazi

```bash
docker compose exec laravel.test composer <command>
```

## NPM ukazi

```bash
docker compose exec laravel.test npm <command>
```
