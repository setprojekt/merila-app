# MUS Matrika Usposobljenosti – Načrt Implementacije

**Modul:** MUS Matrika usposobljenosti  
**Številka modula:** SET 40.013  
**Zahteva:** ISO prosejevalec, pregledna matrika kot v Excelu

---

## 1. Pregled Zahtev

| Zahteva | Opis |
|---------|------|
| **Preglednost** | Matrika v obliki kot na sliki – vrstice = zaposleni, stolpci = kategorije usposabljanja |
| **Podatki o zaposlenih** | Ime, priimek, Zap. št., funkcija – iz seznama uporabnikov Super Admin |
| **Statusi** | Prazno, U (usposabljanje potrebno), O (usposobljen), T (lahko prenaša znanja) |
| **Zakonsko predpisano** | Stolpci "velja do" za vsako zakonsko usposobljenost |
| **Obveščanje** | Email ob preteku zakonskih usposobljenosti |
| **Nastavitve** | Dan obveščanja, interval, koliko dni pred potekom se začne obveščanje |

---

## 2. Koraki Implementacije

### Korak 1: Razširitev Uporabnikov (Super Admin)

**2.1 Migracija – dodaj polji Zap. št. in Funkcija**

```php
// database/migrations/2026_02_18_000001_add_employee_fields_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->string('employee_number', 20)->nullable()->after('surname')->comment('Zap. št.');
    $table->string('function', 100)->nullable()->after('employee_number')->comment('Funkcija');
});
```

**2.2 Model User** – dodaj v `$fillable`:
- `employee_number`
- `function`

**2.3 UserResource (Super Admin)** – v form dodaj v sekcijo "Osnovni podatki":
- `TextInput::make('employee_number')->label('Zap. št.')`
- `TextInput::make('function')->label('Funkcija')`

V tabelo dodaj stolpca za prikaz.

---

### Korak 2: Baza Podatkov za Matriko

**2.1 Kategorije usposabljanja** (glavne skupine kot na sliki)

| Tabela | Opis |
|--------|------|
| `competency_categories` | IZDELAVA OBROČEV, BRIZGANJE PLASTIKE, SPLOŠNA ZNANJA, MERILNICA, ZAKONSKO PREDPISANA USPOSOBLJENOST |
| `competency_items` | Posamezni elementi (npr. "Varstvo pri delu in požarna varnost (na 2 leti)") |
| `competency_matrix_entries` | Vnos: user_id, competency_item_id, status (T/U/O/empty), valid_until (za zakonske) |

**2.2 Migracije**

```
competency_categories:
  - id, name, sort_order

competency_items:
  - id, competency_category_id, name, requires_validity (bool), validity_years (nullable)

competency_matrix_entries:
  - id, user_id, competency_item_id, status (enum: T,U,O), valid_until (nullable)
```

**2.3 Seeder** – vnese kategorije in elemente iz slike (izdelava obročev, brizganje, splošna znanja, merilnica, zakonsko predpisano).

---

### Korak 3: Nastavitve Modula (Super Admin)

**3.1 CompetencyMatrixSettings** (podobno InstrumentsSettings)

```php
// app/Settings/Modules/CompetencyMatrixSettings.php
- module_name = 'MUS Matrika usposobljenosti'
- module_number = 'SET 40.013'
- send_email_notifications (bool)
- notification_recipients (string, comma-separated)
- notification_time (HH:MM)
- notification_day_of_week (1-7)
- notification_interval_days (int) – npr. 7 = enkrat na teden
- notification_days_before_expiry (int) – npr. 60 = začni 60 dni pred potekom
```

**3.2 ManageCompetencyMatrixSettings** – stran v Super Admin z vsemi polji.

---

### Korak 4: Nov Filament Panel za MUS

**4.1 MUSPanelProvider**

- `id('mus')`
- `path('mus')`
- `brandName('MUS Matrika usposobljenosti - SET 40.013')`
- Resources: CompetencyMatrix (custom page, ne klasičen Resource)

**4.2 Registracija** v `bootstrap/providers.php`

**4.3 Dostop** v `User::canAccessPanel()` – dodaj `mus` panel.

**4.4 ModulesDashboard** – dodaj kartico:

```php
[
    'id' => 'mus',
    'name' => 'MUS Matrika usposobljenosti',
    'module_number' => 'SET 40.013',
    'description' => 'Matrika usposobljenosti zaposlenih in zakonsko predpisana usposabljanja',
    'icon' => 'heroicon-o-academic-cap',
    'color' => 'emerald',
    'url' => '/mus',
    ...
]
```

**4.5 allowed_modules** v UserResource – dodaj `'mus' => 'MUS Matrika usposobljenosti'`.

---

### Korak 5: Stran Matrike (pregledna oblika)

**5.1 Custom Page** – `CompetencyMatrixPage`

Namesto standardne tabele Filament uporabi **custom Livewire view** z:
- **Vrstice** = uporabniki (iz User, filtrirani – npr. samo tisti z `employee_number` ali vsi)
- **Stolpci** = kategorije in elementi (grouped headers kot na sliki)
- **Celice** = za običajne: Select (prazno/U/O/T), za zakonske: Select + DatePicker "velja do"

**5.2 Izbor zaposlenih**

- Dropdown/Select za izbor uporabnikov, ki se prikažejo v matriki
- Lahko: "Vsi uporabniki" ali multi-select
- Vrstni red po Zap. št.

**5.3 Legenda** (na dnu strani)

- (Prazno) – Usposabljanje ni potrebno
- U – Usposabljanje potrebno / Planirano
- O – Usposobljen za samostojno delo
- T – Usposobljen – lahko prenaša znanja

**5.4 Datum zadnjega pregleda** – polje v nastavitvah ali na strani (npr. "DATUM ZADNJEGA PREGLEDA: 5.01.2026").

---

### Korak 6: Obveščanje o Preteku

**6.1 Console Command** – `competency:send-expiry-reminders`

Logika (analogno `instruments:send-reminders`):
- Pridobi vse `competency_matrix_entries` kjer `valid_until` ni null
- Filtriraj tiste, kjer je `valid_until` v območju (npr. ≤ X dni)
- Uporabi nastavitve: `notification_day_of_week`, `notification_interval_days`, `notification_days_before_expiry`
- Pošlji email na `notification_recipients`

**6.2 Mail** – `CompetencyExpiryReminderMail` (podobno InstrumentReminderMail)

**6.3 Scheduler** v `routes/console.php`:

```php
Schedule::command('competency:send-expiry-reminders')
    ->dailyAt($competencyNotificationTime)
    ->timezone('Europe/Ljubljana');
```

---

### Korak 7: ISO Zahteve

- **Evidenca** – vsi vnosi v matriko so shranjeni v bazi (audit trail)
- **Datum zadnjega pregleda** – viden na matriki
- **Obveščanje** – dokumentirano v nastavitvah (dan, interval, dni pred potekom)
- **Preglednost** – struktura kot na sliki, z jasnimi oznakami (T, U, O) in datumi "velja do"

---

## 3. Predlagana Struktura Datotek

```
app/
├── Models/
│   ├── CompetencyCategory.php
│   ├── CompetencyItem.php
│   └── CompetencyMatrixEntry.php
├── Settings/Modules/
│   └── CompetencyMatrixSettings.php
├── Providers/Filament/
│   └── MUSPanelProvider.php
├── Filament/MUS/
│   ├── Pages/
│   │   └── CompetencyMatrixPage.php
│   └── Resources/  (opcijsko – za urejanje kategorij)
├── Console/Commands/
│   └── SendCompetencyExpiryReminders.php
├── Mail/
│   └── CompetencyExpiryReminderMail.php
database/
├── migrations/
│   ├── 2026_02_18_000001_add_employee_fields_to_users_table.php
│   ├── 2026_02_18_000002_create_competency_categories_table.php
│   ├── 2026_02_18_000003_create_competency_items_table.php
│   └── 2026_02_18_000004_create_competency_matrix_entries_table.php
└── seeders/
    └── CompetencyMatrixSeeder.php
```

---

## 4. Zaporedje Del

1. Migracije (users + competency tabele)
2. Modeli (User update, CompetencyCategory, CompetencyItem, CompetencyMatrixEntry)
3. Seeder za kategorije in elemente
4. CompetencyMatrixSettings + ManageCompetencyMatrixSettings
5. MUSPanelProvider + registracija
6. CompetencyMatrixPage (custom view z matriko)
7. SendCompetencyExpiryReminders + Mail
8. Scheduler
9. UserResource – Zap. št., funkcija, mus v allowed_modules
10. ModulesDashboard – kartica MUS

---

## 5. Tehnične Opombe

- **Filament Repeater/Grid** – za dinamično matriko lahko uporabite `Filament\Forms\Components\Repeater` ali custom Livewire komponento z `wire:model` za vsako celico.
- **Performance** – pri velikem številu zaposlenih in elementov uporabite paginacijo vrstic ali virtualizacijo.
- **Export** – za ISO lahko dodate gumb "Izvozi v Excel" (Laravel Excel / PhpSpreadsheet).

---

**Datum:** 18.02.2026
