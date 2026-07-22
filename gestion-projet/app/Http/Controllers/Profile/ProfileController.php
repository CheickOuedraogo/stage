<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('profile/Edit', [
            'user' => auth()->user()->only(['id_utilisateur', 'utilisateur_nom', 'utilisateur_email', 'utilisateur_telephone', 'url_avatar', 'role_key']),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->safe()->only(['utilisateur_nom', 'utilisateur_telephone']);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->utilisateur_avatar_chemin) {
                Storage::disk('public')->delete($user->utilisateur_avatar_chemin);
            }

            $data['utilisateur_avatar_chemin'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        auth()->user()->update([
            'utilisateur_mot_de_passe' => Hash::make($request->validated('utilisateur_mot_de_passe')),
        ]);

        return back()->with('success', 'Mot de passe mis à jour avec succès.');
    }
}
