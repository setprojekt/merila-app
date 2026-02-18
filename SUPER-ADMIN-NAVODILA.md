# Super Admin Panel - Navodila za Uporabo

## Dostop do Super Admin Panela

Super Admin panel je dostopen na naslovu: **http://localhost/super-admin**

### Prijava
- Samo uporabniki z vlogo `super_admin` lahko dostopajo do tega panela
- Prijavite se z vašim emailom in geslom

## Funkcionalnosti

### 1. Dashboard
- Pregled nad sistemom
- Statistike uporabnikov
- Hitre povezave do pomembnih funkcij

### 2. Upravljanje Uporabnikov

**Pot:** Super Admin > Uporabniki

#### Ustvarjanje Novega Uporabnika
1. Kliknite "Nov Uporabnik"
2. Izpolnite obrazec:
   - **Ime:** Polno ime uporabnika
   - **E-pošta:** Email naslov (uporablja se za prijavo)
   - **Vloga:** Izberite vlogo uporabnika
     - `Super Admin`: Vse pravice, dostop do Super Admin panela
     - `Admin`: Upravljanje meril in dobavnic
     - `Uporabnik`: Osnovna uporaba aplikacije
     - `Ogledovalec`: Samo ogled, brez urejanja
   - **Geslo:** Najmanj 8 znakov

#### Urejanje Uporabnika
1. Najdite uporabnika v seznamu
2. Kliknite "Uredi"
3. Spremenite potrebne podatke
4. **Opomba:** Geslo pustite prazno, če ga ne želite spremeniti

#### Brisanje Uporabnika
1. Najdite uporabnika v seznamu
2. Kliknite "Izbriši"
3. Potrdite izbris

**Opozorilo:** Brisanje uporabnika ne izbriše njegovih meril ali dobavnic.

### 3. Globalne Nastavitve

**Pot:** Super Admin > Nastavitve > Globalne Nastavitve

#### Podatki Podjetja
- **Ime Aplikacije:** Prikaže se v glavi aplikacije
- **Ime Podjetja:** Uporabljeno v dokumentih in emailih
- **Naslov Podjetja:** Polni naslov podjetja
- **Telefon Podjetja:** Kontaktna telefonska številka
- **Email Podjetja:** Kontaktni email naslov

#### Email Nastavitve
- **Email Od (From Address):** Email naslov pošiljatelja za sistemske emaile
- **Ime Pošiljatelja (From Name):** Ime, ki se prikaže kot pošiljatelj
- **Email za Obvestila:** Email naslov za prejemanje sistemskih obvestil

#### Nastavitve Obvestil
- **Omogoči Obvestila:** Vklop/izklop avtomatskih obvestil
- **Čas Pošiljanja Obvestil:** Ura, ob kateri se pošiljajo dnevna obvestila (npr. 08:00)
- **Dnevi pred Potekom za Opozorilo:** Število dni pred potekom merila, ko se pošlje opozorilo

### 4. Nastavitve Modula Meril

**Pot:** Super Admin > Nastavitve > Nastavitve Meril

#### Dobavnica Nastavitve
- **Ime Pošiljatelja:** Privzeto ime pošiljatelja na dobavnicah
- **Naslov Pošiljatelja:** Privzeti naslov pošiljatelja
- **Ime Prejemnika:** Privzeto ime prejemnika (kontrolnega laboratorija)
- **Naslov Prejemnika:** Privzeti naslov prejemnika

#### Email Obvestila
- **Pošiljaj Email Obvestila:** Vklop/izklop email obvestil za merila
- **Prejemniki Obvestil:** Email naslovi prejemnikov, ločeni z vejico

#### Opozorila in Statusi
- **Dnevi za "Opozorilo" Status:** Število dni pred potekom, ko merilo dobi rumeno oznako
- **Dnevi za "Poteklo" Status:** Število dni pred potekom, ko merilo dobi rdečo oznako

#### Arhiviranje
- **Avtomatsko Arhiviraj Potekla Merila:** Vklop/izklop avtomatskega arhiviranja
- **Arhiviraj Po (dnevi):** Število dni po poteku, ko se merilo avtomatsko arhivira

### 5. Dnevnik Aktivnosti

**Pot:** Super Admin > Sistem > Dnevnik Aktivnosti

#### Pregled Aktivnosti
- **Tip:** Vrsta aktivnosti (Merila, Dobavnice, Uporabniki)
- **Opis:** Opis dogodka
- **Uporabnik:** Kdo je izvedel akcijo
- **Dogodek:** Vrsta dogodka (Ustvarjeno, Posodobljeno, Izbrisano)
- **Datum:** Kdaj se je dogodek zgodil

#### Filtriranje
- **Po Tipu:** Prikaz samo določene vrste aktivnosti
- **Po Dogodku:** Prikaz samo določenega tipa dogodka
- **Po Uporabniku:** Prikaz samo aktivnosti določenega uporabnika
- **Po Datumu:** Prikaz aktivnosti v določenem časovnem obdobju

#### Ogled Podrobnosti
1. Kliknite ikono za ogled pri posamezni aktivnosti
2. Prikaže se modal z:
   - Osnovnimi podatki
   - Novimi podatki (po spremembi)
   - Starimi podatki (pred spremembo)
   - Povezanim objektom

**Opomba:** Dnevnik se osveži vsakih 30 sekund

## Navodila za Prvo Uporabo

### 1. Namestitev Paketov
```bash
# Namestite pakete
docker compose exec laravel.test composer install

# Objavite vendor assets
docker compose exec laravel.test php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
docker compose exec laravel.test php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
docker compose exec laravel.test php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider"

# Zaženite migracije
docker compose exec laravel.test php artisan migrate
```

### 2. Ustvarjanje Super Admin Uporabnika

#### Možnost A: Uporaba create-admin-user.php
```bash
docker compose exec laravel.test php create-admin-user.php
```
To bo ustvarilo uporabnika:
- Email: `admin@example.com`
- Geslo: `password`
- Vloga: `super_admin`

#### Možnost B: Ročna Posodobitev v Bazi
```sql
UPDATE users SET role = 'super_admin' WHERE email = 'vas@email.com';
```

### 3. Prvi Dostop
1. Obiščite http://localhost/super-admin
2. Prijavite se z super admin računom
3. Posodobite globalne nastavitve
4. Posodobite nastavitve modula meril
5. Ustvarite dodatne uporabnike po potrebi

## Pogosta Vprašanja

### Kako resetiram geslo uporabnika?
1. Odprite Super Admin > Uporabniki
2. Kliknite "Uredi" pri uporabniku
3. Vnesite novo geslo
4. Kliknite "Shrani"

### Kako spremenim email nastavitve?
1. Odprite Super Admin > Globalne Nastavitve
2. Uredite Email Nastavitve sekcijo
3. Kliknite "Shrani"

### Kaj se zgodi, če izbrišem uporabnika?
- Uporabnik ne more več dostopati do sistema
- Njegova merila in dobavnice ostanejo v sistemu
- Aktivnosti v dnevniku ostanejo zabeležene

### Kako spremenimi privzete podatke za dobavnice?
1. Odprite Super Admin > Nastavitve Meril
2. Uredite Dobavnica Nastavitve sekcijo
3. Kliknite "Shrani"
4. Nove dobavnice bodo uporabljale te podatke kot privzete

### Kako lahko sledim, kdo je spremenil določeno merilo?
1. Odprite Super Admin > Dnevnik Aktivnosti
2. Filtrirajte po:
   - Tip: "Merila"
   - Dogodek: "Posodobljeno"
3. Kliknite ikono za ogled pri aktivnosti
4. Prikazale se bodo podrobnosti spremembe

## Varnostna Opozorila

⚠️ **Pomembno:**
- Vloge `super_admin` ne dodajajte vsem uporabnikom
- Redno spreminjajte gesla
- Spremljajte dnevnik aktivnosti za nenavadno vedenje
- Nastavitve sistema lahko občutno vplivajo na delovanje aplikacije

## Podpora

Za tehnično podporo ali vprašanja kontaktirajte administratorja sistema.
