<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\SuperAdmin\Pages\Auth\EditProfile;
use App\Http\Middleware\CheckForForcedRenews;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SuperAdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Dodaj avtomatsko odjavo v footer
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => Blade::render('<x-auto-logout />')
        );
        
        // Dodaj handler za 419 napake
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => Blade::render('<x-handle-419 />')
        );
    }
    
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('super-admin')
            ->path('super-admin')
            ->colors([
                'primary' => Color::Red,
            ])
            ->login(Login::class)
            ->passwordReset()
            ->profile(EditProfile::class)
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->brandName(fn () => app(\App\Settings\GlobalSettings::class)->app_name ?? 'SET Intranet')
            ->favicon(asset('favicon.ico?v=2'))
            ->resources([
                \App\Filament\SuperAdmin\Resources\UserResource::class,
                \App\Filament\SuperAdmin\Resources\ActivityLogResource::class,
            ])
                    ->discoverPages(in: app_path('Filament/SuperAdmin/Pages'), for: 'App\\Filament\\SuperAdmin\\Pages')
                    ->pages([
                        \App\Filament\SuperAdmin\Pages\Dashboard::class,
                        \App\Filament\SuperAdmin\Pages\ManageGlobalSettings::class,
                        \App\Filament\SuperAdmin\Pages\ManageInstrumentsSettings::class,
                        \App\Filament\SuperAdmin\Pages\ManageCompetencyMatrixSettings::class,
                        \App\Filament\SuperAdmin\Pages\ChangePasswordRequired::class,
                        \App\Filament\SuperAdmin\Pages\ChangePinRequired::class,
                    ])
            ->navigationItems([
                NavigationItem::make('Nazaj na Module')
                    ->url('/admin')
                    ->icon('heroicon-o-arrow-left-circle')
                    ->sort(-1)
                    ->group(''),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                CheckForForcedRenews::class,
            ]);
    }
}
