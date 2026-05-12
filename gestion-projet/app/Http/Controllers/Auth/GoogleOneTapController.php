<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleOneTapController extends Controller
{
    public function store(Request $request)
    {
        $credential = $request->input('credential');

        if (! $credential) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Aucun jeton Google fourni.']);
        }

        $clientId = config('services.google.client_id');
        $client = new Google_Client(['client_id' => $clientId]);

        $payload = $client->verifyIdToken($credential);

        if (! $payload) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Jeton Google invalide.']);
        }

        $email = $payload['email'] ?? null;

        if (! $email) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Impossible de récupérer l\'adresse email via Google.']);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Aucun compte CIFEU associé à cette adresse Google. Contactez votre administrateur.']);
        }

        if (! $user->is_active) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte est désactivé. Contactez votre administrateur.']);
        }

        Auth::login($user, remember: true);

        return redirect($user->dashboardRoute());
    }
}
