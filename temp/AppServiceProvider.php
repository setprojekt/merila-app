<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
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
    }
}
