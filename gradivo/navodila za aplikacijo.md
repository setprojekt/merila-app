# Specifikacija Projekta: SET Merila (Intranet App)

## 1. Pregled Projekta
Gradimo modularno spletno aplikacijo za interno uporabo (Intranet) na TrueNAS strežniku.
* **Glavni cilj:** Centralizirano vodenje meril, opozarjanje na potek veljavnosti in generiranje dobavnic za kontrolo.
* [cite_start]**Prihodnost:** Aplikacija mora biti zasnovana modularno (kasneje se dodajo moduli: Vzdrževanje orodja, Delovni čas, Delovni nalogi)[cite: 5].
* **Vloge:** Admin (vse pravice), Uporabnik (omejene pravice).

## 2. Tehnološki Stack
* **Okolje:** Docker (Laravel Sail) – kasneje deploy na TrueNAS.
* **Framework:** Laravel 11.
* **Admin/UI:** FilamentPHP v3 (uporaba panelov, resources, widgets).
* **Baza:** MySQL ali SQLite (z uporabo Migrations).
* **Mail:** SMTP (Nodemailer/Symfony Mailer).

## 3. Podatkovni Model (Baza Podatkov)

### A. Tabela: `users` (Uporabniki)
* Standardni Laravel uporabniki + `role` (ali uporaba `filament-shield`).
* Povezava na ustvarjene dobavnice.

### B. Tabela: `instruments` (Merila)
[cite_start]Stolpci na podlagi obstoječih podatkov[cite: 3, 8]:
* `id`: Primary Key.
* `internal_id`: String (Unique) – npr. "TP 1647/01" ali "1".
* `name`: String – npr. "Mikrometer not.20-25 mm".
* `type`: String – Vrsta merila.
* `location`: String – npr. "Planina - kon. obročev".
* `frequency_years`: Decimal (4,2) – npr. [cite_start]1.5 ali 2.0[cite: 3].
* `last_check_date`: Date – Datum zadnjega pregleda.
* `next_check_date`: Date – *Izračunano:* `last_check_date` + `frequency_years`.
* `status`: Enum – ['USTREZA', 'NE_USTREZA', 'IZLOCENO', 'V_KONTROLI'].
    * `V_KONTROLI`: Merilo je poslano na kontrolo in je na aktivni dobavnici.
* `certificate_path`: String (Nullable) – Pot do PDF datoteke.
* [cite_start]`archived`: Boolean – Če je status IZLOCENO, je true[cite: 11].

### [cite_start]C. Tabela: `delivery_notes` (Dobavnice) [cite: 15]
* `id`: Primary Key.
* `number`: String/Int – Zaporedna številka.
* `sender_id`: User FK (kdo je ustvaril, avtomatsko prijavljen user).
* `recipient`: String – Prejemnik (zunanja kontrola).
* `status`: Enum – ['ODPRTA', 'POSLANA', 'ZAKLJUCENA'].
* `created_at`, `updated_at`.

### D. Tabela: `delivery_note_items` (Postavke dobavnice)
* `delivery_note_id`: FK.
* `instrument_id`: FK.
* `returned_status`: Enum (status po vrnitvi: USTREZA/NE USTREZA).
* `notes`: Opombe.

## 4. Funkcionalnosti in Logika

### [cite_start]Modul 1: Pregledna Plošča (Dashboard) [cite: 13]
Ob vstopu se prikažejo 3 widgeti (StatsOverview):
1.  **Veljavna merila:** Število meril, kjer je `next_check_date` > 30 dni.
2.  **Opozorilo (Kmalu poteče):** Število meril, kjer je `next_check_date` <= 30 dni in >= danes.
3.  **Pretečena merila:** Število meril, kjer je `next_check_date` < danes.

### Modul 2: Seznam Meril (Filament Resource)
* **Privzeti prikaz ob odprtju:** Seznam prikazuje samo merila, ki so v roku 30 dni do poteka ali pretečena (status != 'V_KONTROLI' in status != 'IZLOCENO').
* [cite_start]**Tabela:** Prikazuje stolpce: Št. merila, Vrsta, Uporabnik, Status, Velja do[cite: 12].
* [cite_start]**Semafor (Traffic Light Logic):** Stolpec "Velja do" ali "Dni do poteka" [cite: 14] se barva:
    * 🟢 **Zelena:** > 30 dni do poteka.
    * 🟡 **Rumena:** <= 30 dni do poteka.
    * [cite_start]🔴 **Rdeča:** Pretečeno (datum v preteklosti)[cite: 10].
* [cite_start]**Filter:** Privzeto skrij merila s statusom "IZLOCENO" (prikaz le v Arhiv tabu)[cite: 11].
* **Bulk Action - "Pošlji na kontrolo":** 
    * Uporabnik izbere merila iz seznama (checkbox ali bulk selection).
    * Klikne na akcijo "Pošlji na kontrolo".
    * Sistem avtomatsko:
        1. Spremeni status izbranih meril v `V_KONTROLI`.
        2. Ustvari novo dobavnico z izbranimi merili.
        3. Prikaže formo za vnos prejemnika (recipient).

### Modul 3: Dobavnice (Workflow)

**Workflow kreiranja dobavnice:**
1.  **Avtomatsko kreiranje:** 
    * Ko uporabnik v seznamu meril izbere merila in klikne "Pošlji na kontrolo", se avtomatsko kreira nova dobavnica.
    * Status izbranih meril se spremeni v `V_KONTROLI`.
    * Uporabnik vnese prejemnika (recipient) - to je edini obvezen podatek ob kreiranju.
    * Dobavnica dobi status `ODPRTA`.

2.  **Ročno kreiranje (alternativa):**
    * Uporabnik lahko tudi ročno ustvari novo dobavnico.
    * [cite_start]**Izbor Meril (Pametno sortiranje):** Ko dodajaš merila na dobavnico, mora seznam ponuditi vrstni red[cite: 15]:
        1.  Pretečena merila (Rdeča).
        2.  Merila v opozorilu (Rumena, <= 30 dni).
        3.  Veljavna merila, sortirana po datumu poteka (najbližja 30 dnem prva).
    * Merila, ki so že v statusu `V_KONTROLI`, se ne prikažejo v seznamu (razen če so že na tej dobavnici).

3.  **Zaključek (Vračilo meril):**
    * Ko merila pridejo nazaj, uporabnik odpre dobavnico.
    * Za vsako merilo na dobavnici:
        * Označi checkbox "Vrnjeno".
        * Vnese nov status (USTREZA/NE USTREZA) v polje `returned_status`.
        * Vnese nov `last_check_date` -> sistem avtomatsko preračuna nov `next_check_date`.
        * [cite_start]Naloži nov certifikat -> stari se arhivira (ostane v zgodovini), novi postane aktiven[cite: 19].
        * Status merila se spremeni iz `V_KONTROLI` v `USTREZA` ali `NE_USTREZA` (glede na rezultat kontrole).
    * [cite_start]Ko so vsa merila na dobavnici obdelana (označena kot vrnjena), se dobavnica avtomatsko zaključi/arhivira (status `ZAKLJUCENA`)[cite: 18].

### [cite_start]Modul 4: Obveščanje (Email Scheduler) [cite: 20, 21, 22]
Nastavi Laravel Scheduler (`console/kernel.php` ali nov način v L11), ki teče vsak dan ob 08:00.

**Logika pošiljanja:**
1.  Preveri vsa aktivna merila.
2.  **Pogoj 1 (Tedensko):** Če je do poteka med 30 in 5 dni -> Pošlji mail **samo ob ponedeljkih**.
3.  **Pogoj 2 (Dnevno):** Če je do poteka manj kot 5 dni -> Pošlji mail **vsak dan**.
4.  **Vsebina:** Tabela meril, ki ustrezajo kriterijem.

[cite_start]**SMTP Nastavitve (.env):** [cite: 27-30]
* Host: `mail.set-trade.si`
* Port: `4465` (SSL)
* Username: `opomnik@set-trade.si`
* Encryption: `SSL/TLS`
* From Address: `opomnik@set-trade.si`

## 5. UI Navodila
* [cite_start]Uporabi `filament/spatie-laravel-settings-plugin` za stran z nastavitvami (Email naslovi prejemnikov, meje opozarjanja - 30/5 dni)[cite: 23].
* Design naj bo čist, profesionalen, prilagojen za namizno uporabo.

## 5.1 Tiskanje Dokumentov

**Problem:** PDF se generira, vendar format besedila ni pravilen. Tiskanje iz brskalnika ne ohranja formata.

**Rešitev:**
* **PDF Generiranje:** Uporaba `spatie/laravel-pdf` (boljša podpora za CSS kot dompdf)
* **Print CSS:** Ločena datoteka `resources/css/print.css` z `@media print` pravili
* **Print Views:** Ločene Blade komponente za tiskanje (`resources/views/print/`)
* **Filament Actions:** 
    * PDF Download Action za generiranje PDF-jev
    * Print Preview Action za predogled pred tiskom
* **Print Routes:** Ločene routes za print preview strani

**Implementacija:**
* Vsi dokumenti (dobavnice, certifikati, poročila) morajo imeti:
    1. Print-friendly CSS stile
    2. PDF generiranje preko `spatie/laravel-pdf`
    3. Print preview možnost
    4. Direktno tiskanje iz brskalnika

**Podrobnosti:** Glej `gradivo/print-strategija.md`

---

## 6. Predlogi za Izboljšave in Razširitve

### 6.1 Modularna Arhitektura

**Predlog strukture:**
* Uporaba Laravel paketov za vsak modul (npr. `Modules/Instruments`, `Modules/DeliveryNotes`, `Modules/Notifications`)
* Vsak modul naj ima svojo strukturo:
  ```
  Modules/
    ├── Instruments/
    │   ├── Models/
    │   ├── Resources/
    │   ├── Policies/
    │   ├── Migrations/
    │   └── Routes/
    ├── DeliveryNotes/
    └── Settings/ (ločen modul za nastavitve)
  ```
* Uporaba `nwidart/laravel-modules` ali podobnega paketa za modularno arhitekturo
* Vsak modul naj ima svoj `ServiceProvider` za registracijo v glavni aplikaciji
* Moduli naj bodo neodvisni, vendar lahko komunicirajo preko Events/Listeners

**Ločitev nastavitev od modula:**
* Ustvari poseben modul `Settings` z uporabo `spatie/laravel-settings`
* Nastavitve naj bodo v ločeni tabeli `settings` ali JSON konfiguraciji
* Vsak modul lahko registrira svoje nastavitve, vendar so shranjene centralno
* Filament Settings stran naj omogoča upravljanje vseh nastavitev na enem mestu

### 6.2 Avtorizacija in Pravice (RBAC)

**Podrobnejša specifikacija vlog:**
* **Super Admin:** Vse pravice, vključno z upravljanjem uporabnikov in nastavitev
* **Admin:** Upravljanje meril, dobavnic, pregled poročil
* **Uporabnik:** Ogled meril, kreiranje dobavnic, vračilo meril
* **Gost (Viewer):** Samo ogled, brez možnosti urejanja

**Implementacija:**
* Uporaba `bezhad/laravel-filament-shield` ali `spatie/laravel-permission` za podrobnejše pravice
* Pravice na nivoju:
  * **Resource level:** `instruments.view`, `instruments.create`, `instruments.edit`, `instruments.delete`
  * **Action level:** `delivery_notes.create`, `delivery_notes.close`, `instruments.archive`
* Policies za vsak model (InstrumentPolicy, DeliveryNotePolicy)
* Middleware za zaščito routes

**Dodatne tabele:**
* `roles` - Vloge (Super Admin, Admin, User, Viewer)
* `permissions` - Pravice (view, create, edit, delete, archive, itd.)
* `role_user` - Pivot tabela za povezavo uporabnikov in vlog
* `permission_role` - Pivot tabela za povezavo pravic in vlog

### 6.3 Docker Konfiguracija

**Predlog docker-compose.yml strukture:**
```yaml
services:
  app:
    build: .
    volumes:
      - .:/var/www/html
    depends_on:
      - mysql
      - redis
  
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: merila_db
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
  
  redis:
    image: redis:alpine
    volumes:
      - redis_data:/data
```

**Dodatne storitve:**
* Redis za cache in queues
* Mailhog/Mailpit za lokalno testiranje emailov
* phpMyAdmin za upravljanje baze (samo za development)

**Laravel Sail optimizacije:**
* Uporaba `.env.sail` za produkcijske nastavitve
* Volume mapping za persistent storage (certifikati, uploads)
* Health checks za vse storitve

### 6.4 Podatkovni Model - Razširitve

**Tabela `instruments` - dodatni stolpci:**
* `user_id`: FK na uporabnika, ki je odgovoren za merilo
* `manufacturer`: Proizvajalec merila
* `serial_number`: Serijska številka
* `purchase_date`: Datum nakupa
* `purchase_price`: Cena nakupa (opcijsko)
* `notes`: Opombe (text)
* `created_by`: FK na uporabnika, ki je ustvaril zapis
* `updated_by`: FK na uporabnika, ki je zadnji posodobil zapis
* `deleted_at`: Soft delete (Laravel standard)

**Tabela `delivery_notes` - dodatni stolpci:**
* `delivery_date`: Datum odpreme
* `expected_return_date`: Pričakovan datum vrnitve
* `actual_return_date`: Dejanski datum vrnitve
* `notes`: Opombe (text)
* `total_instruments`: Število meril na dobavnici (cached za hitrost)

**Nova tabela `certificate_history`:**
* `id`: Primary Key
* `instrument_id`: FK na merilo
* `certificate_path`: Pot do PDF
* `check_date`: Datum pregleda
* `status`: Status pregleda
* `created_at`: Datum naložitve
* Omogoča zgodovino vseh certifikatov

**Nova tabela `instrument_logs`:**
* `id`: Primary Key
* `instrument_id`: FK na merilo
* `user_id`: FK na uporabnika, ki je izvedel akcijo
* `action`: Enum (CREATED, UPDATED, ARCHIVED, SENT, RETURNED)
* `old_values`: JSON (stare vrednosti)
* `new_values`: JSON (nove vrednosti)
* `created_at`: Timestamp
* Za audit trail in zgodovino sprememb

### 6.5 Funkcionalnosti - Razširitve

**Dashboard izboljšave:**
* Grafikon trenda meril (line chart) - prikaz zadnjih 12 mesecev
* Widget z najbližjimi potekli merili (naslednjih 7 dni)
* Widget z aktivnimi dobavnicami
* Export možnost za poročila (PDF/Excel)

**Seznam meril:**
* **Glavni workflow:** Privzeti prikaz meril v roku 30 dni in pretečenih, bulk action "Pošlji na kontrolo" za avtomatsko kreiranje dobavnice
* Bulk actions (masovne operacije): arhiviranje, dodajanje na dobavnico
* Napredno filtriranje (po lokaciji, tipu, statusu, uporabniku)
* Sortiranje po več stolpcih
* Export v Excel/CSV
* QR kode za merila (za skeniranje)

**Dobavnice:**
* PDF generiranje dobavnic (uporaba `barryvdh/laravel-dompdf` ali `spatie/laravel-pdf`)
* Email obvestilo ob kreiranju dobavnice
* Email obvestilo ob vračilu meril
* Možnost dodajanja opomb na nivoju dobavnice
* Print preview

**Obveščanje:**
* Možnost izbire prejemnikov emailov (iz nastavitev)
* Različni email template-i za različne scenarije
* Možnost testiranja emailov (test button v nastavitvah)
* Email zgodovina (tabela `email_logs`)
* Možnost on-demand pošiljanja (ne samo scheduler)

### 6.6 Varnost

**Predlogi:**
* CSRF zaščita (Laravel default)
* Rate limiting za API endpoints
* Password policy (minimalna dolžina, kompleksnost)
* Two-factor authentication (2FA) za admin uporabnike (opcijsko)
* Session timeout
* IP whitelist za admin panele (opcijsko)
* Logging vseh kritičnih akcij (uporaba `instrument_logs`)

**File upload varnost:**
* Validacija tipov datotek (samo PDF)
* Validacija velikosti datotek
* Scan za viruse (opcijsko)
* Shranjevanje zunaj public direktorija
* Generiranje unikatnih imen datotek

### 6.7 Performance Optimizacije

**Predlogi:**
* Redis cache za pogosto uporabljene podatke (dashboard statistike)
* Database indexing na pogosto uporabljene stolpce (`next_check_date`, `status`, `user_id`)
* Eager loading za N+1 probleme
* Queue za email pošiljanje (uporaba Laravel Queues)
* Image optimization (če bodo slike)
* CDN za statične datoteke (opcijsko)

**Laravel optimizacije:**
* `php artisan config:cache` za produkcijo
* `php artisan route:cache` za produkcijo
* `php artisan view:cache` za produkcijo
* Opcache za PHP

### 6.8 Testiranje

**Predlogi:**
* Unit testi za Models in Services
* Feature testi za kritične workflow-e (kreiranje dobavnice, vračilo meril)
* Browser testi za Filament resources (Laravel Dusk ali Pest)
* Test coverage naj bo vsaj 70% za kritične dele

**Test struktura:**
```
tests/
  ├── Unit/
  │   ├── Models/
  │   └── Services/
  ├── Feature/
  │   ├── Instruments/
  │   ├── DeliveryNotes/
  │   └── Notifications/
  └── Browser/ (če uporabljamo Dusk)
```

### 6.9 Logging in Monitoring

**Predlogi:**
* Uporaba Laravel Logging za vse akcije
* Ločeni log kanali (daily, single file)
* Log levels: INFO za običajne akcije, WARNING za opozorila, ERROR za napake
* Monitoring dashboard (opcijsko: Laravel Pulse ali custom)
* Email obvestila za kritične napake

**Log struktura:**
```
storage/logs/
  ├── laravel.log (general)
  ├── instruments.log (modul specifični)
  ├── delivery_notes.log
  └── emails.log
```

### 6.10 Backup Strategija

**Predlogi:**
* Dnevni backup baze podatkov (Laravel Scheduler + `spatie/laravel-backup`)
* Backup certifikatov (PDF datoteke)
* Retention policy (zadnjih 30 dni)
* Možnost ročnega backupa preko admin panela
* Testiranje obnovitve (restore) vsaj mesečno

### 6.11 Deployment na TrueNAS

**Predlogi:**
* Docker Compose za produkcijo
* Environment variables v `.env.production`
* SSL certifikati (Let's Encrypt)
* Reverse proxy (Nginx ali Traefik)
* Health check endpoints
* Graceful shutdown
* Zero-downtime deployment strategija

**Deployment checklist:**
* [ ] Backup produkcijske baze
* [ ] Testiranje na staging okolju
* [ ] Migracije baze
* [ ] Cache clearing
* [ ] Queue restart
* [ ] Verifikacija funkcionalnosti

### 6.12 Dokumentacija

**Predlogi:**
* README.md z navodili za setup
* API dokumentacija (če bo API)
* User manual (navodila za uporabnike)
* Developer documentation (struktura modulov, konvencije)
* Changelog (CHANGELOG.md)
* Architecture decision records (ADR) za pomembne odločitve

### 6.13 Dodatni Moduli (Prihodnost)

**Struktura za prihodnje module:**
* **Vzdrževanje orodja:**
  * Tabele: `tools`, `maintenance_schedules`, `maintenance_logs`
  * Povezava z modulom Instruments (če je merilo tudi orodje)
  
* **Delovni čas:**
  * Tabele: `work_logs`, `projects`, `tasks`
  * Integracija z uporabniki
  
* **Delovni nalogi:**
  * Tabele: `work_orders`, `work_order_items`, `work_order_statuses`
  * Povezava z moduli Instruments in Vzdrževanje

**Načelo:**
* Vsak modul naj bo neodvisen paket
* Komunikacija med moduli preko Events/Listeners
* Skupne komponente (Settings, Users) v core modulu

### 6.14 API (Opcijsko)

**Če bo potreben API:**
* Laravel Sanctum za API avtentikacijo
* RESTful API endpoints
* API dokumentacija (Laravel API Resources)
* Rate limiting
* Versioning (v1/, v2/)

### 6.15 Code Quality

**Predlogi:**
* PSR-12 coding standard
* PHPStan ali Psalm za static analysis
* Laravel Pint za code formatting
* Pre-commit hooks (Husky + PHP Lint)
* Code review proces

---

## 7. Prioritetni Seznam Implementacije

### Faza 1 (MVP - Minimum Viable Product):
1. Osnovna modularna struktura
2. Modul Instruments (CRUD)
3. Modul DeliveryNotes (osnovni workflow)
4. Dashboard z osnovnimi widgeti
5. Email scheduler (osnovno)
6. Avtorizacija (Admin/User)

### Faza 2:
1. Napredna avtorizacija (RBAC)
2. Modul Settings (ločen)
3. PDF generiranje dobavnic
4. Certificate history
5. Audit logging

### Faza 3:
1. Performance optimizacije
2. Testiranje
3. Backup strategija
4. Dokumentacija
5. Deployment na TrueNAS

### Faza 4 (Prihodnost):
1. Dodatni moduli (Vzdrževanje, Delovni čas, Delovni nalogi)
2. API (če bo potreben)
3. Advanced features (QR kode, bulk actions, itd.)