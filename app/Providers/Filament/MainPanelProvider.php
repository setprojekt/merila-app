<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\EditProfile;
use App\Http\Middleware\CheckForForcedRenews;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MainPanelProvider extends PanelProvider
{
    /** Ali je uporabnik na strani Moduli (dashboard modulov) */
    protected static function onModulesDashboard(): bool
    {
        $path = request()->path();
        return $path === 'admin' || str_ends_with($path, 'modules-dashboard');
    }

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
            ->default()
            ->id('admin')
            ->path('admin')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->login(Login::class)
            ->passwordReset()
            ->profile(EditProfile::class)
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->brandName(fn () => app(\App\Settings\GlobalSettings::class)->app_name ?? 'SET Intranet')
            ->favicon(asset('favicon.ico?v=2'))
            ->pages([
                \App\Filament\Main\Pages\ModulesDashboard::class,
                \App\Filament\MUS\Pages\CompetencyMatrixPage::class,
                \App\Filament\MUS\Pages\MUSSettingsPage::class,
                \App\Filament\Main\Pages\ChangePasswordRequired::class,
                \App\Filament\Main\Pages\ChangePinRequired::class,
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
            ])
            ->authGuard('web')
            ->databaseNotifications()
            ->navigationItems([
                NavigationItem::make('Merila')
                    ->url('/merila')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->sort(2)
                    ->group('Moduli')
                    ->visible(fn (): bool => auth()->check() && static::onModulesDashboard() && (auth()->user()->isSuperAdmin() || auth()->user()->canAccessModule('merila'))),
                NavigationItem::make('MUS Matrika usposobljenosti')
                    ->url('/admin/mus')
                    ->icon('heroicon-o-academic-cap')
                    ->sort(3)
                    ->group('Moduli')
                    ->visible(fn (): bool => auth()->check() && static::onModulesDashboard() && (auth()->user()->isSuperAdmin() || auth()->user()->canAccessModule('mus'))),
                NavigationItem::make('Super Admin')
                    ->url('/super-admin')
                    ->icon('heroicon-o-shield-check')
                    ->sort(4)
                    ->group('Moduli')
                    ->visible(fn (): bool => auth()->check() && static::onModulesDashboard() && auth()->user()->isSuperAdmin()),
                NavigationItem::make('Nazaj na Module')
                    ->url('/admin')
                    ->icon('heroicon-o-arrow-left-circle')
                    ->sort(-1)
                    ->group('')
                    ->visible(fn (): bool => auth()->check() && !static::onModulesDashboard()),
            ]);
    }
}
