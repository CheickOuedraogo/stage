<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /** Redirect user to Google OAuth page. */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /** Handle Google callback — only allow pre-existing active accounts. */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Authentification Google échouée. Réessayez.']);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Aucun compte CIFEU associé à cette adresse Google. Contactez votre administrateur.']);
        }

        if (! $user->is_active) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte est désactivé. Contactez votre administrateur.']);
        }

        Auth::login($user, remember: true);

        return redirect(match ($user->role) {
            UserRole::Admin => route('admin.dashboard'),
            UserRole::Daf => route('daf.dashboard'),
            UserRole::Ac => route('ac.dashboard'),
            UserRole::Porteur => route('porteur.dashboard'),
        });
    }
}
