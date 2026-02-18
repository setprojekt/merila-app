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
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
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
            ->id('merila')
            ->path('merila')
            ->topNavigation() // <--- DODANO: Premakne menije na vrh
            ->colors([
                'primary' => Color::Amber,
            ])
            ->login(Login::class)
            ->passwordReset()
            ->profile(EditProfile::class)
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->brandName(fn () => (app(\App\Settings\GlobalSettings::class)->app_name ?? 'SET Intranet') . ' - Merila (70.0001)')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\ArchivedInstruments::class,
                \App\Filament\Pages\ChangePasswordRequired::class,
                \App\Filament\Pages\ChangePinRequired::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\InstrumentsStatsOverview::class,
            ])
            ->navigationGroups([
                'Merila',
                'Dobavnice',
                'Nastavitve',
            ])
            ->navigationItems([
                NavigationItem::make('Nazaj na Module')
                    ->url('/admin')
                    ->icon('heroicon-o-arrow-left-circle')
                    ->sort(-1)
                    ->group(''),
            ])
            ->middleware(