# 📦 Vodnik za Dodajanje Novih Modulov

## 🎯 Arhitektura Modulov

Sistem je zasnovan modularno z možnostjo enostavnega dodajanja novih funkcionalnosti kot ločenih modulov.

### Trenutna Struktura

```
📂 SISTEM
├─ 🏠 /admin → Module Dashboard (prikaz vseh modulov)
├─ ⚙️ /super-admin → Super Admin Panel (upravljanje sistema)
└─ 📦 /merila → Modul Merila (prvi modul)
```

## 🚀 Kako Dodati Nov Modul

### Korak 1: Ustvari Panel Provider

```bash
php artisan make:provider Filament/NoviModulPanelProvider
```

### Korak 2: Konfigurira Panel Provider

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

class NoviModulPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('novi-modul')  // Unikatni ID
            ->path('novi-modul')  // URL pot
            ->colors([
                'primary' => Color::Green,  // Barva modula
            ])
            ->login()
            ->topNavigation()
            ->maxContentWidth('full')
            ->brandName('SET Trade - Novi Modul')
            ->discoverResources(in: app_path('Filament/NoviModul/Resources'), for: 'App\\Filament\\NoviModul\\Resources')
            ->discoverPages(in: app_path('Filament/NoviModul/Pages'), for: 'App\\Filament\\NoviModul\\Pages')
            ->discoverWidgets(in: app_path('Filament/NoviModul/Widgets'), for: 'App\\Filament\\NoviModul\\Widgets')
            ->middleware([
                // Middleware tukaj
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

### Korak 3: Registriraj Provider

V `bootstrap/providers.php` dodaj:

```php
return [
    // ... obstoječi providerji
    App\Providers\Filament\NoviModulPanelProvider::class,
];
```

### Korak 4: Ustvari Strukturo Map

```bash
mkdir app/Filament/NoviModul
mkdir app/Filament/NoviModul/Pages
mkdir app/Filament/NoviModul/Resources
mkdir app/Filament/NoviModul/Widgets
```

### Korak 5: Dodaj Modul v Dashboard

V `app/Filament/Main/Pages/ModulesDashboard.php` dodaj nov modul v `getModules()` metodo:

```php
$modules[] = [
    'id' => 'novi-modul',
    'name' => 'Novi Modul',
    'description' => 'Opis novega modula',
    'icon' => 'heroicon-o-document',
    'color' => 'green',
    'url' => '/novi-modul',
    'enabled' => true,
    'badge' => null,
    'stats' => [
        [
            'label' => 'Statistic 1',
            'value' => 0,
        ],
    ],
];
```

### Korak 6: Konfiguriraj Dostop

V `app/Models/User.php` dodaj pravila dostopa v `canAccessPanel()` metodo:

```php
// Novi modul - dostop za vse
if ($panel->getId() === 'novi-modul') {
    return true; // ali druga logika
}
```

### Korak 7: Ustvari Module Settings (Opcijsko)

```bash
php artisan make:class Settings/Modules/NoviModulSettings
```

```php
<?php

namespace App\Settings\Modules;

use Spatie\LaravelSettings\Settings;

class NoviModulSettings extends Settings
{
    public string $setting1;
    public bool $setting2;
    
    public static function group(): string
    {
        return 'novi_modul';
    }
}
```

Dodaj Settings Page v Super Admin Panel.

## 📋 Primer: Modul "Protokoli"

### 1. Panel Provider

```php
class ProtocolsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('protokoli')
            ->path('protokoli')
            ->colors(['primary' => Color::Blue])
            ->brandName('SET Trade - Kalibracijski Protokoli')
            // ... ostale konfiguracije
    }
}
```

### 2. Registracija

```php
// bootstrap/providers.php
App\Providers\Filament\ProtocolsPanelProvider::class,
```

### 3. Dashboard Card

```php
[
    'id' => 'protokoli',
    'name' => 'Kalibracijski Protokoli',
    'description' => 'Ustvarjanje in upravljanje kalibracijskih protokolov',
    'icon' => 'heroicon-o-document-text',
    'color' => 'blue',
    'url' => '/protokoli',
    'enabled' => true,
    'stats' => [
        [
            'label' => 'Protokoli',
            'value' => \App\Models\Protocol::count(),
        ],
    ],
],
```

### 4. Dostop

```php
if ($panel->getId() === 'protokoli') {
    return in_array($this->role, ['super_admin', 'admin', 'user']);
}
```

## 🎨 Priporočila

### Barve Modulov
- **Merila**: Amber (rumena)
- **Protokoli**: Blue (modra)
- **Poročila**: Green (zelena)
- **Kalibra

cije**: Purple (vijolična)
- **Super Admin**: Red (rdeča)

### Ikone (Heroicons)
- `heroicon-o-wrench-screwdriver` - Merila
- `heroicon-o-document-text` - Protokoli
- `heroicon-o-chart-bar` - Poročila
- `heroicon-o-beaker` - Kalibracije
- `heroicon-o-shield-check` - Super Admin

### Module ID Konvencija
- Uporabljaj kebab-case: `novi-modul`
- Brez šumnikov: `merila`, ne `mêrila`
- Kratko in opisno

## 🔒 Pravila Dostopa (Roles)

### Obstoječe Vloge
- `super_admin` - Polni dostop do vsega
- `admin` - Dostop do vseh modulov (brez Super Admin)
- `user` - Dostop do določenih modulov
- `viewer` - Samo branje

### Primer Konfiguracije

```php
public function canAccessPanel(Panel $panel): bool
{
    // Super Admin vidi vse
    if ($this->isSuperAdmin()) {
        return true;
    }
    
    // Določi dostop po modulih
    return match($panel->getId()) {
        'admin' => true,  // Vsi
        'merila' => true,  // Vsi
        'protokoli' => in_array($this->role, ['admin', 'user']),  // Admin in User
        'porocila' => in_array($this->role, ['admin']),  // Samo Admin
        'super-admin' => false,  // Samo Super Admin
        default => false,
    };
}
```

## 📁 Struktura Direktorija Modula

```
app/Filament/NazivModula/
├── Resources/
│   ├── ModelResource.php
│   └── ModelResource/
│       └── Pages/
│           ├── ListModels.php
│           ├── CreateModel.php
│           └── EditModel.php
├── Pages/
│   └── Dashboard.php
└── Widgets/
    └── StatsWidget.php
```

## ✅ Checklist za Nov Modul

- [ ] Ustvari Panel Provider
- [ ] Registriraj Provider v `bootstrap/providers.php`
- [ ] Ustvari strukturo map
- [ ] Dodaj modul v Module Dashboard
- [ ] Konfiguriraj dostop v `User.php`
- [ ] Ustvari Module Settings (če potrebno)
- [ ] Dodaj Settings Page v Super Admin (če potrebno)
- [ ] Ustvari Resources, Pages, Widgets
- [ ] Testiraj dostop za različne vloge
- [ ] Posodobi dokumentacijo

## 🎓 Primeri Uporabe

### Preprosto Dodajanje

Za preprost modul brez special

nih pravic:
1. Kopiraj `AdminPanelProvider` in preimenuj
2. Spremeni `id`, `path`, in `brandName`
3. Registriraj v `bootstrap/providers.php`
4. Dodaj kartico v Dashboard
5. Dodaj pravico v `User.php`

### Napredno Dodajanje

Za modul s custom pravicami, nastavitvami, itd.:
1. Sledi vsem korakom zgoraj
2. Ustvari Settings class
3. Dodaj Settings Page v Super Admin
4. Implementiraj custom middleware
5. Dodaj permissions logiko

---

**Datum:** 14.01.2026  
**Verzija:** 1.0  
**Avtor:** SET Trade Development Team
