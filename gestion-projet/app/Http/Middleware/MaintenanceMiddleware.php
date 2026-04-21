<?php

namespace App\Http\Middleware;

use App\Models\Setting;
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
        $until = Setting::getMaintenanceUntil();

        if ($until && $until->isPast() && Setting::isMaintenanceActive()) {
            Setting::set('maintenance_mode', 'false');
            Setting::set('maintenance_reason', '');
            Setting::set('maintenance_until', '');
        }

        // Check if maintenance is still active after potential auto-disable
        if (! Setting::isMaintenanceActive()) {
            return $next($request);
        }

        // Admin is always allowed through
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request);
        }

        // Allow access to the login routes (GET + POST) to show maintenance message and allow admin login
        if ($request->routeIs('login') || $request->is('connexion')) {
            return $next($request);
        }

        $reason = Setting::get('maintenance_reason', 'Maintenance en cours.');
        $maintenanceUntil = Setting::get('maintenance_until');

        return inertia('auth/Maintenance', [
            'reason' => $reason,
            'until' => $maintenanceUntil,
        ])->toResponse($request)->setStatusCode(503);
    }
}
