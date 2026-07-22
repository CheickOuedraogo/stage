<?php

namespace App\Http\Middleware;

use App\Models\Parametre;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Auto-disable maintenance if scheduled end time has passed
        $until = Parametre::getMaintenanceJusqua();

        if ($until && $until->isPast() && Parametre::estMaintenanceActive()) {
            app(\App\Services\MaintenanceService::class)->desactiver();
        }

        // Check if maintenance is still active after potential auto-disable
        if (! Parametre::estMaintenanceActive()) {
            return $next($request);
        }

        // Administrateur is always allowed through
        if (Auth::check() && Auth::user()->estAdministrateur()) {
            return $next($request);
        }

        // Permettre la connexion des administrateurs et la page publique de maintenance.
        if ($request->routeIs('login', 'maintenance') || $request->is('connexion')) {
            return $next($request);
        }

        return redirect()->route('maintenance');
    }
}
