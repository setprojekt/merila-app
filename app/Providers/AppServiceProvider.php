<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Login;
use App\Auth\PinUserProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Nastavi lokalizacijo na slovenščino
        App::setLocale('sl');
        
        // Registriraj custom User Provider za PIN avtentikacijo
        Auth::provider('pin', function ($app, array $config) {
            return new PinUserProvider($app['hash'], $config['model']);
        });
        
        // Registriraj event listener za tracking prijav
        Event::listen(
            Login::class,
            \App\Listeners\UpdateUserLastLogin::class,
        );

        // From naslov/ime iz GlobalSettings; geslo in uporabnik iz .env (MAIL_PASSWORD, MAIL_USERNAME)
        $this->configureMailFromSettings();
    }

    protected function configureMailFromSettings(): void
    {
        try {
            $settings = app(\App\Settings\GlobalSettings::class);
            Config::set('mail.from.address', $settings->mail_from_address);
            Config::set('mail.from.name', $settings->mail_from_name);
        } catch (\Throwable $e) {
            // Med migracijami ali če settings še ne obstajajo
        }
    }
}
