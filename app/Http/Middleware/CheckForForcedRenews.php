<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckForForcedRenews
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Preveri, ali je uporabnik prijavljen
        if (!Auth::check()) {
            return $next($request);
        }
        
        $user = Auth::user();
        $currentPath = $request->path();
        $routeName = $request->route()?->getName();
        
        // Dovoljene poti in route-i (da preprečimo redirect loop)
        $allowedPaths = [
            'merila/change-password-required',
            'merila/change-pin-required',
            'super-admin/change-password-required',
            'super-admin/change-pin-required',
            'admin/change-password-required',
            'admin/change-pin-required',
            'livewire/update',
            'livewire/message/',
        ];
        
        $allowedRoutes = [
            'filament.merila.pages.change-password-required',
            'filament.merila.pages.change-pin-required',
            'filament.merila.auth.logout',
            'filament.super-admin.pages.change-password-required',
            'filament.super-admin.pages.change-pin-required',
            'filament.super-admin.auth.logout',
            'filament.admin.pages.change-password-required',
            'filament.admin.pages.change-pin-required',
            'filament.admin.auth.logout',
        ];
        
        // Preveri ali je uporabnik že na dovoljeni poti
        $isAllowed = false;
        
        if ($routeName && in_array($routeName, $allowedRoutes)) {
            $isAllowed = true;
        }
        
        foreach ($allowedPaths as $allowedPath) {
            if (str_contains($currentPath, $allowedPath)) {
                $isAllowed = true;
                break;
            }
        }
        
        // Če je uporabnik že na dovoljeni poti, pusti ga naprej
        if ($isAllowed) {
            return $next($request);
        }
        
        // POMEMBNO: Vrstni red preverjanja (najprej geslo, potem PIN)
        
        // Določi pravilen panel za preusmeritev
        $panelPath = 'merila'; // default
        if (str_starts_with($currentPath, 'super-admin')) {
            $panelPath = 'super-admin';
        } elseif (str_starts_with($currentPath, 'admin')) {
            $panelPath = 'admin';
        }
        
        // 1. Preveri force_renew_password
        if ($user->force_renew_password) {
            return redirect("/{$panelPath}/change-password-required");
        }
        
        // 2. Preveri force_renew_pin
        if ($user->force_renew_pin) {
            return redirect("/{$panelPath}/change-pin-required");
        }
        
        return $next($request);
    }
}
