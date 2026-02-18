# Super Admin Panel - Implementacijski Načrt

## Status Implementacije

### ✅ Dokončano
1. **Composer.json posodobljen** - Dodani paketi:
   - `spatie/laravel-permission` (za RBAC)
   - `spatie/laravel-activitylog` (za audit logging)
   - `spatie/laravel-settings` (že nameščen)

2. **Super Admin Panel Provider** - Ustvarjen
   - Lokacija: `app/Providers/Filament/SuperAdminPanelProvider.php`
   - Registriran v `bootstrap/providers.php`
   - Path: `/super-admin`
   - Barva: Rdeča (za razliko od admin panela)

3. **User Model posodobljen**
   - Dodana metoda `isSuperAdmin()`
   - Posodobljena `canAccessPanel()` - Super Admin panel dostopen samo za `super_admin` uporabnike

4. **UserResource za Super Admin Panel** - Ustvarjen
   - Lokacija: `app/Filament/SuperAdmin/Resources/UserResource.php`
   - CRUD operacije za uporabnike
   - Vloge: super_admin, admin, user, viewer
   - Geslo se hashira avtomatično
   - Pages: ListUsers, CreateUser, EditUser

5. **Dashboard za Super Admin Panel** - Ustvarjen
   - Lokacija: `app/Filament/SuperAdmin/Pages/Dashboard.php`

6. **Users migracija posodobljena**
   - Dodana podpora za vloge: super_admin, admin, user, viewer

7. **Password handling popravljen**
   - Geslo se hashira avtomatično preko Laravel cast-a (`'password' => 'hashed'`)
   - Ni potrebno hashirati v formi

8. **Settings Sistem** - Implementiran ✅
   - **GlobalSettings** (`app/Settings/GlobalSettings.php`):
     - Email nastavitve (from_address, from_name, notification_email)
     - Podatki podjetja (company_name, address, phone, email)
     - Nastavitve obvestil (enable_notifications, notification_time, warning_days)
   - **InstrumentsSettings** (`app/Settings/Modules/InstrumentsSettings.php`):
     - Dobavnica nastavitve (sender/recipient podatki)
     - Email obvestila (recipients, send_notifications)
     - Opozorila (expiry_warning_days, expiry_alert_days)
     - Arhiviranje (auto_archive_expired, auto_archive_after_days)
   - **Settings Pages**:
     - `ManageGlobalSettings` - Globalne nastavitve
     - `ManageInstrumentsSettings` - Nastavitve modula meril
   - **Migracije**:
     - `2024_01_15_000001_create_settings_table.php` - GlobalSettings z privzetimi vrednostmi
     - `2024_01_15_000002_create_instruments_settings.php` - InstrumentsSettings z privzetimi vrednostmi

9. **Audit Logging Sistem** - Implementiran ✅
   - **ActivityLogResource** (`app/Filament/SuperAdmin/Resources/ActivityLogResource.php`):
     - Pregled vseh aktivnosti v sistemu
     - Filtriranje po tipu, dogodku, uporabniku, datumu
     - Prikaz podrobnosti sprememb (stari/novi podatki)
     - Avtomatsko osveževanje vsakih 30 sekund
   - **View Template** (`resources/views/filament/resources/activity-log/view-activity.blade.php`):
     - Modal za prikaz podrobnosti aktivnosti
     - Prikaz starih in novih vrednosti
   - **LogsActivity Trait**:
     - Dodano v `Instrument` model - sledenje vsem spremembam meril
     - Dodano v `DeliveryNote` model - sledenje vsem spremembam dobavnic
     - Dodano v `User` model - sledenje spremembam uporabnikov (brez gesel)

### 📋 Naslednji Koraki (vrstni red implementacije)

#### Korak 1: Namestitev paketov in migracije
```bash
# Namesti pakete (če še niso nameščeni)
docker compose exec laravel.test composer install

# Publish vendor assets
docker compose exec laravel.test php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
docker compose exec laravel.test php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
docker compose exec laravel.test php artisan vendor:publish --provider="Spatie\LaravelSettings\LaravelSettingsServiceProvider"

# Zaženi migracije
docker compose exec laravel.test php artisan migrate
```

#### Korak 2: Posodobitev User Model
- Dodati trait `HasRoles` iz spatie/laravel-permission
- Dodati trait `LogsActivity` iz spatie/laravel-activitylog
- Implementirati `canAccessPanel()` za Super Admin panel
- Dodati metodo `isSuperAdmin()`

#### Korak 3: Migracije za Permissions
- Roles tabela (super_admin, admin, user, viewer)
- Permissions tabela
- Pivot tabele (role_user, permission_role)
- Default roles in permissions

#### Korak 4: UserResource za Super Admin Panel
- Lokacija: `app/Filament/SuperAdmin/Resources/UserResource.php`
- CRUD operacije za uporabnike
- Dodeljevanje vlog
- Aktivacijski/deaktivacijski uporabniki

#### ~~Korak 5: Settings Sistem~~ ✅ DOKONČANO
- ✅ Globalne nastavitve (email, sistem)
- ✅ Modulske nastavitve (za vsak modul)
- ✅ Settings Pages v Super Admin panelu

#### ~~Korak 6: Audit Logging~~ ✅ DOKONČANO
- ✅ Activity Log Resource
- ✅ Pregled aktivnosti uporabnikov
- ✅ Filtriranje po uporabniku/datumu/modulu
- ✅ LogsActivity trait dodan v vse ključne modele

## Struktura

```
app/
├── Filament/
│   ├── SuperAdmin/
│   │   ├── Resources/
│   │   │   ├── UserResource.php ✅
│   │   │   │   └── Pages/
│   │   │   │       ├── ListUsers.php ✅
│   │   │   │       ├── CreateUser.php ✅
│   │   │   │       └── EditUser.php ✅
│   │   │   └── ActivityLogResource.php ✅
│   │   │       └── Pages/
│   │   │           └── ListActivityLogs.php ✅
│   │   └── Pages/
│   │       ├── Dashboard.php ✅
│   │       ├── ManageGlobalSettings.php ✅
│   │       └── ManageInstrumentsSettings.php ✅
│   │
│   └── Admin/ (trenutni modulski panel)
│
├── Settings/ ✅
│   ├── GlobalSettings.php ✅
│   └── Modules/
│       └── InstrumentsSettings.php ✅
│
└── Models/
    ├── User.php (posodobljen) ✅ + LogsActivity
    ├── Instrument.php (posodobljen) ✅ + LogsActivity
    └── DeliveryNote.php (posodobljen) ✅ + LogsActivity
```

## Paketi

### spatie/laravel-permission
- RBAC sistem
- Roles in Permissions
- Trait: `HasRoles`

### spatie/laravel-activitylog
- Audit logging
- Sledenje sprememb
- Trait: `LogsActivity`

### spatie/laravel-settings
- Settings sistem
- Global in Module scope
- Settings Resource (Filament plugin že nameščen)

## Dokumentacija

- **[SUPER-ADMIN-NAVODILA.md](SUPER-ADMIN-NAVODILA.md)** - Podrobna navodila za uporabo Super Admin panela
  - Dostop in prijava
  - Upravljanje uporabnikov
  - Konfiguracija nastavitev
  - Pregled dnevnika aktivnosti
  - Pogosta vprašanja

## Opombe

- Super Admin panel je dostopen samo za uporabnike z vlogo `super_admin`
- Modulski panel (`/admin`) ostane za module
- Settings lahko delijo moduli ali so specifični za modul
- Vse spremembe se beležijo v dnevnik aktivnosti (Activity Log)
- Settings se shranjujejo v bazi podatkov (tabela `settings`)

## Kaj je potrebno po namestitvi paketov

1. ✅ Zagnati migracije (`php artisan migrate`)
2. ✅ Ustvariti super admin uporabnika (uporabite `create-admin-user.php`)
3. ✅ Konfigurirati globalne nastavitve v Super Admin panelu
4. ✅ Konfigurirati nastavitve modula meril
5. 🔄 OPCIJSKO: Implementirati Permissions sistem (RBAC) z spatie/laravel-permission