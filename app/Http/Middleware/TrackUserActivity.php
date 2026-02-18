<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            // Posodobi zadnjo aktivnost samo če je pretekla več kot 1 minuta
            // da ne obremenjujemo baze pri vsakem requestu
            $user = $request->user();
            
            if (!$user->last_activity_at || 
                $user->last_activity_at->diffInMinutes(now()) >= 1) {
                $user->updateLastActivity();
            }
        }
        
        return $next($request);
    }
}
