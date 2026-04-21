<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

describe('Profil utilisateur', function () {
    beforeEach(function () {
        $this->user = User::factory()->porteur()->create([
            'name' => 'Test User',
            'telephone' => '+226 70 00 00 00',
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
                'name' => 'Nouveau Nom',
                'telephone' => '+226 71 11 11 11',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Nouveau Nom',
            'telephone' => '+226 71 11 11 11',
        ]);
    });

    it('ne peut pas modifier l\'email', function () {
        $originalEmail = $this->user->email;

        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => $this->user->name,
                'email' => 'nouveau@email.bf', // Should be ignored
            ]);

        expect($this->user->fresh()->email)->toBe($originalEmail);
    });

    it('uploade un avatar', function () {
        Storage::fake('public');

        $this->actingAs($this->user)
            ->patch(route('profile.update'), [
                'name' => $this->user->name,
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        expect($this->user->fresh()->avatar_path)->not->toBeNull();
        Storage::disk('public')->assertExists($this->user->fresh()->avatar_path);
    });

    it('change le mot de passe', function () {
        $this->actingAs($this->user)
            ->patch(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'NouveauMdp123',
                'password_confirmation' => 'NouveauMdp123',
            ])
            ->assertRedirect();
    });

    it('rejette un ancien mot de passe incorrect', function () {
        $this->actingAs($this->user)
            ->patch(route('profile.password'), [
                'current_password' => 'mauvais-mdp',
                'password' => 'NouveauMdp123',
                'password_confirmation' => 'NouveauMdp123',
            ])
            ->assertSessionHasErrors('current_password');
    });
});
