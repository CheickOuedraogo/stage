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
        $until = Parametre::getMaintenanceUntil();

        if ($until && $until->isPast() && Parametre::isMaintenanceActive()) {
            Parametre::set('maintenance_mode', 'false');
            Parametre::set('maintenance_reason', '');
            Parametre::set('maintenance_until', '');
        }

        // Check if maintenance is still active after potential auto-disable
        if (! Parametre::isMaintenanceActive()) {
            return $next($request);
        }

        // Administrateur is always allowed through
        if (Auth::check() && Auth::user()->estAdministrateur()) {
            return $next($request);
        }

        // Allow access to the login routes (GET + POST) to show maintenance message and allow admin login
        if ($request->routeIs('login') || $request->is('connexion')) {
            return $next($request);
        }

        $reason = Parametre::get('maintenance_reason', 'Maintenance en cours.');
        $maintenanceUntil = Parametre::get('maintenance_until');

        return inertia('auth/Maintenance', [
            'reason' => $reason,
            'until' => $maintenanceUntil,
        ])->toResponse($request)->setStatusCode(503);
    }
}
