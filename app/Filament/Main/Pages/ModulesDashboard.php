<?php

namespace App\Filament\Main\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class ModulesDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string $view = 'filament.main.pages.modules-dashboard';
    
    protected static ?string $title = 'Moduli';
    
    protected static ?string $navigationLabel = 'Moduli';

    /** Prikaži v navigaciji samo na strani modulov, ne v drugih modulih */
    public static function shouldRegisterNavigation(): bool
    {
        $path = request()->path();
        return $path === 'admin' || str_ends_with($path, 'modules-dashboard');
    }
    
    public function getModules(): array
    {
        $user = auth()->check() ? auth()->user() : null;
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        
        // Cache statistike za 5 minut
        $instrumentsCount = Cache::remember('stats.instruments.active', 300, function () {
            return \App\Models\Instrument::where('archived', false)->count();
        });
        
        $deliveryNotesCount = Cache::remember('stats.delivery_notes.active', 300, function () {
            return \App\Models\DeliveryNote::where('archived', false)->count();
        });
        
        // Pridobi nastavitve modulov
        $instrumentsSettings = app(\App\Settings\Modules\InstrumentsSettings::class);
        $moduleName = $instrumentsSettings->module_name ?? 'Merila';
        $moduleNumber = $instrumentsSettings->module_number ?? '70.0001';
        
        // Nastavitve MUS
        $musModuleName = 'MUS Matrika usposobljenosti';
        $musModuleNumber = 'SET 40.013';
        try {
            $competencySettings = app(\App\Settings\Modules\CompetencyMatrixSettings::class);
            $musModuleName = $competencySettings->module_name ?? $musModuleName;
            $musModuleNumber = $competencySettings->module_number ?? $musModuleNumber;
        } catch (\Throwable $e) {}
        $competencyExpiringCount = 0;
        try {
            $competencyExpiringCount = Cache::remember('stats.competency.expiring', 300, function () {
                return \App\Models\CompetencyMatrixEntry::whereNotNull('valid_until')
                    ->where('valid_until', '<=', now()->addDays(60))
                    ->count();
            });
        } catch (\Throwable $e) {}

        // Vsi moduli
        $allModules = [
            [
                'id' => 'merila',
                'name' => $moduleName,
                'module_number' => $moduleNumber,
                'description' => 'Upravljanje meril, evidenca dobavnic in kalibracij',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'color' => 'amber',
                'url' => '/merila',
                'enabled' => true,
                'badge' => null,
                'stats' => [
                    [
                        'label' => 'Aktivna merila',
                        'value' => $instrumentsCount,
                    ],
                    [
                        'label' => 'Dobavnice',
                        'value' => $deliveryNotesCount,
                    ],
                ],
            ],
            [
                'id' => 'mus',
                'name' => $musModuleName,
                'module_number' => $musModuleNumber,
                'description' => 'Matrika usposobljenosti zaposlenih in zakonsko predpisana usposabljanja',
                'icon' => 'heroicon-o-academic-cap',
                'color' => 'emerald',
                'url' => '/admin/mus',
                'enabled' => true,
                'badge' => null,
                'stats' => [
                    [
                        'label' => 'Zaposleni',
                        'value' => \App\Models\User::count(),
                    ],
                    [
                        'label' => 'Kmalu poteče',
                        'value' => $competencyExpiringCount,
                    ],
                ],
            ],
        ];
        
        // Super Admin vidi vse module vključno z Super Admin panelom
        if ($isSuperAdmin) {
            $usersCount = Cache::remember('stats.users.total', 300, function () {
                return \App\Models\User::count();
            });
            
            $activitiesCount = Cache::remember('stats.activities.total', 300, function () {
                return \Spatie\Activitylog\Models\Activity::count();
            });
            
            $allModules[] = [
                'id' => 'super-admin',
                'name' => 'Super Admin',
                'module_number' => 'ADMIN',
                'description' => 'Upravljanje uporabnikov, modulov in globalnih nastavitev',
                'icon' => 'heroicon-o-shield-check',
                'color' => 'red',
                'url' => '/super-admin',
                'enabled' => true,
                'badge' => 'Admin',
                'stats' => [
                    [
                        'label' => 'Uporabniki',
                        'value' => $usersCount,
                    ],
                    [
                        'label' => 'Aktivnosti',
                        'value' => $activitiesCount,
                    ],
                ],
            ];
            
            return $allModules;
        }
        
        // Filtriraj module glede na uporabnikove pravice
        $modules = [];
        foreach ($allModules as $module) {
            if ($user && $user->canAccessModule($module['id'])) {
                $modules[] = $module;
            }
        }
        
        return $modules;
    }
}
