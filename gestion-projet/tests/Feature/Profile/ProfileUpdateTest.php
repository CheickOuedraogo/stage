<?php

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

describe('Profil utilisateur', function () {
    beforeEach(function () {
        $this->user = Utilisateur::factory()->porteur()->create([
            'utilisateur_nom' => 'Test Utilisateur',
            'utilisateur_telephone' => '+226 70 00 00 00',
        ]);
    });

    it('affiche la page de profil', function () {
        $this->actingAs($this->user)
            ->get(route('profile.edit'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('profile/Edit'));
    });

    it('met à jour le nom et le téléphone', function () {
        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'utilisateur_nom' => 'Nouveau Nom',
                'utilisateur_telephone' => '+226 71 11 11 11',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id_utilisateur' => $this->user->id_utilisateur,
            'utilisateur_nom' => 'Nouveau Nom',
            'utilisateur_telephone' => '+226 71 11 11 11',
        ]);
    });

    it('ne peut pas modifier l\'utilisateur_email', function () {
        $originalEmail = $this->user->utilisateur_email;

        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'utilisateur_nom' => $this->user->utilisateur_nom,
                'utilisateur_email' => 'nouveau@email.bf', // Should be ignored
            ]);

        expect($this->user->fresh()->utilisateur_email)->toBe($originalEmail);
    });

    it('uploade un avatar', function () {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'utilisateur_nom' => $this->user->utilisateur_nom,
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        expect($this->user->fresh()->avatar_path)->not->toBeNull();
        Storage::disk('public')->assertExists($this->user->fresh()->avatar_path);
    });

    it('change le mot de passe', function () {
        $this->actingAs($this->user)
            ->patch(route('profile.password'), [
                'current_password' => 'utilisateur_mot_de_passe',
                'utilisateur_mot_de_passe' => 'NouveauMdp123',
                'password_confirmation' => 'NouveauMdp123',
            ])
            ->assertRedirect();
    });

    it('rejette un ancien mot de passe incorrect', function () {
        $this->actingAs($this->user)
            ->patch(route('profile.password'), [
                'current_password' => 'mauvais-mdp',
                'utilisateur_mot_de_passe' => 'NouveauMdp123',
                'password_confirmation' => 'NouveauMdp123',
            ])
            ->assertSessionHasErrors('current_password');
    });
});
