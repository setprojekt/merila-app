# Mail Nastavitve - Navodila za Posodobitev

## Posodobite .env datoteko

Odprite `.env` datoteko in spremenite naslednje vrednosti:

```env
# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mail.set-trade.si
MAIL_PORT=4465
MAIL_USERNAME=opomnik@set-trade.si
MAIL_PASSWORD=globinavalovi588363
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=opomnik@set-trade.si
MAIL_FROM_NAME="SET Merila - Opomnik"
```

**Opomba:** 
- SMTP strežnik: `mail.set-trade.si` (uporabite to obliko, ne `mail@set-trade.si`)
- SMTP vrata: `4465` (SSL) ali `587` (če 4465 ne deluje, poskusite 587 z `MAIL_ENCRYPTION=tls`)

## Nastavitve v Super Admin Panelu

Po prijavi v Super Admin panel (**http://localhost/super-admin**):

### Globalne Nastavitve
1. Pojdite na **Nastavitve > Globalne Nastavitve**
2. Preverite/posodobite:
   - **Email Od:** `opomnik@set-trade.si`
   - **Ime Pošiljatelja:** `SET Merila - Opomnik`
   - **Email za Obvestila:** `opomnik@set-trade.si`
   - **Čas Pošiljanja Obvestil:** `08:00`
   - **Dnevi pred Potekom za Opozorilo:** `30`

### Nastavitve Meril
1. Pojdite na **Nastavitve > Nastavitve Meril**
2. Preverite/posodobite:
   - **Pošiljaj Email Obvestila:** `✓ DA`
   - **Prejemniki Obvestil:** seznam emailov, ločenih z vejico
   - **Dnevi za "Opozorilo" Status:** `30` dni
   - **Dnevi za "Poteklo" Status:** `5` dni

## Logika Obveščanja (iz specifikacije)

**Scheduler teče vsak dan ob 08:00:**

1. **Tedensko obvestilo (ob ponedeljkih):**
   - Za merila, ki potečejo med 30 in 5 dni

2. **Dnevno obvestilo:**
   - Za merila, ki potečejo v manj kot 5 dneh

## Po Posodobitvi

Po spremembi `.env` datoteke zaženite:
```bash
docker compose exec laravel.test php artisan config:clear
docker compose exec laravel.test php artisan cache:clear
```

## Testiranje Mail Nastavitev

Mail nastavitve lahko preverite:
1. V Super Admin panelu poskusite ustvariti novega uporabnika
2. Če ni napak, so mail nastavitve pravilne
3. Za testno pošiljanje boste mogli ustvariti test command

## Pomembno

- Geslo za `opomnik@set-trade.si` morate vnesti sami (varnostni razlogi)
- Vse nastavitve v Super Admin panelu so že posodobljene s pravimi vrednostmi
- Če imate težave z SSL, poskusite `MAIL_ENCRYPTION=tls` namesto `ssl`
