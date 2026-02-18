<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            $r = \Illuminate\Support\Facades\Route::getFacadeRoot();
            if (!$r->has('filament.admin.auth.profile')) {
                \Illuminate\Support\Facades\Route::get('/admin/profile', \App\Filament\Pages\Auth\EditProfile::class)
                    ->middleware(['web', 'auth'])
                    ->name('filament.admin.auth.profile');
            }
            if (!$r->has('filament.merila.auth.profile')) {
                \Illuminate\Support\Facades\Route::get('/merila/profile', \App\Filament\Pages\Auth\EditProfile::class)
                    ->middleware(['web', 'auth'])
                    ->name('filament.merila.auth.profile');
            }
            if (!$r->has('filament.super-admin.auth.profile')) {
                \Illuminate\Support\Facades\Route::get('/super-admin/profile', \App\Filament\SuperAdmin\Pages\Auth\EditProfile::class)
                    ->middleware(['web', 'auth'])
                    ->name('filament.super-admin.auth.profile');
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\TrackUserActivity::class,
        ]);
        
        // Izključi logout route iz CSRF preverjanja (prepreči 419 napako pri odjavi)
        $middleware->validateCsrfTokens(except: [
            'admin/logout',
            '/admin/logout',
            'super-admin/logout',
            '/super-admin/logout',
            'merila/logout',
            '/merila/logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
