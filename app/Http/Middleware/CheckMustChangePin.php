<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMustChangePin
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
        
        // Debug logging
        \Log::info('CheckMustChangePin: User ' . $user->id . ' force_renew_pin = ' . ($user->force_renew_pin ? 'true' : 'false'));
        
        // Če uporabnik mora spremeniti PIN
        if ($user->force_renew_pin) {
            // Dovoli dostop samo do strani za spremembo PIN-a, logout in Livewire zahteve
            $currentPath = $request->path();
            $routeName = $request->route()?->getName();
            
            // Dovoljene poti
            $allowedPaths = [
                'merila/change-pin-required',
                'super-admin/change-pin-required',
                'livewire/update',
                'livewire/message/',
            ];
            
            $allowedRoutes = [
                'filament.merila.pages.change-pin-required',
                'filament.merila.auth.logout',
                'filament.super-admin.pages.change-pin-required',
                'filament.super-admin.auth.logout',
            ];
            
            $isAllowed = false;
            
            // Preveri route name
            if ($routeName && in_array($routeName, $allowedRoutes)) {
                $isAllowed = true;
            }
            
            // Preveri path
            foreach ($allowedPaths as $allowedPath) {
                if (str_contains($currentPath, $allowedPath)) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                // Preusmeri na pravo stran glede na trenutni panel
                if (str_starts_with($currentPath, 'super-admin')) {
                    return redirect('/super-admin/change-pin-required');
                }
                return redirect('/merila/change-pin-required');
            }
        }
        
        return $next($request);
    }
}
