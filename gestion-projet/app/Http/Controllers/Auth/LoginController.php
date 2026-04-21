<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function showLoginForm(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect($this->dashboardRoute());
        }

        $maintenanceActive = Setting::isMaintenanceActive();
        $maintenanceReason = Setting::get('maintenance_reason');
        $maintenanceUntil = Setting::get('maintenance_until');

        return Inertia::render('auth/Login', [
            'maintenanceActive' => $maintenanceActive,
            'maintenanceReason' => $maintenanceReason,
            'maintenanceUntil' => $maintenanceUntil,
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Block inactive users immediately
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Votre compte a été désactivé. Contactez l\'administrateur.',
            ]);
        }

        // Block non-admin users during maintenance
        if (Setting::isMaintenanceActive() && ! $user->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('maintenance', true);
        }

        AuditLog::log('login', $user, description: "Connexion de {$user->name}");

        return redirect($this->dashboardRoute());
    }

    public function logout(): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditLog::log('logout', $user, description: "Déconnexion de {$user->name}");
        }

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function dashboardRoute(): string
    {
        return match (Auth::user()->role) {
            UserRole::Admin => route('admin.dashboard'),
            UserRole::Daf => route('daf.dashboard'),
            UserRole::Ac => route('ac.dashboard'),
            UserRole::Porteur => route('porteur.dashboard'),
        };
    }
}
