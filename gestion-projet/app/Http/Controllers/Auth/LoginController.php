<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\JournalAudit;
use App\Models\Parametre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function showLoginForm(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect(Auth::user()->routeTableauBord());
        }

        $maintenanceActive = Parametre::isMaintenanceActive();
        $maintenanceReason = Parametre::get('maintenance_reason');
        $maintenanceUntil = Parametre::get('maintenance_until');

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
        if (! $user->utilisateur_actif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'utilisateur_email' => 'Votre compte a été désactivé. Contactez l\'administrateur.',
            ]);
        }

        // Block non-admin users during maintenance
        if (Parametre::isMaintenanceActive() && ! $user->estAdministrateur()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('maintenance');
        }

        JournalAudit::log('login', $user, description: "Connexion de {$user->utilisateur_nom}");

        return redirect($user->routeTableauBord());
    }

    public function logout(): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            JournalAudit::log('logout', $user, description: "Déconnexion de {$user->utilisateur_nom}");
        }

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
