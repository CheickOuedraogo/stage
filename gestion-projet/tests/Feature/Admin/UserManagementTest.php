<?php

use App\Enums\RoleUtilisateur;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

describe('Gestion des utilisateurs (Administrateur)', function () {
    beforeEach(function () {
        $this->admin = Utilisateur::factory()->admin()->create();
    });

    it('liste les utilisateurs', function () {
        Utilisateur::factory()->count(5)->porteur()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('admin/Users/Index'));
    });

    it('un non-admin ne peut pas accéder à la liste', function () {
        $porteur = Utilisateur::factory()->porteur()->create();

        $this->actingAs($porteur)
            ->get(route('admin.users.index'))
            ->assertStatus(403);
    });

    it('crée un utilisateur', function () {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'utilisateur_nom' => 'Nouveau Utilisateur',
                'utilisateur_email' => 'nouveau@ujkz.bf',
                'utilisateur_mot_de_passe' => 'Password123',
                'role_key' => RoleUtilisateur::Porteur->value,
                'utilisateur_telephone' => '+226 70 00 00 00',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('utilisateurs', [
            'utilisateur_email' => 'nouveau@ujkz.bf',
            'role_key' => RoleUtilisateur::Porteur->value,
        ]);
    });

    it('valide les données lors de la création', function () {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'utilisateur_nom' => '',
                'utilisateur_email' => 'pas-un-email',
                'role_key' => 'role-invalide',
            ])
            ->assertSessionHasErrors(['utilisateur_nom', 'utilisateur_email', 'role_key', 'utilisateur_mot_de_passe']);
    });

    it('rejette un email déjà utilisé', function () {
        Utilisateur::factory()->create(['utilisateur_email' => 'existant@test.bf']);

        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'utilisateur_nom' => 'Test',
                'utilisateur_email' => 'existant@test.bf',
                'utilisateur_mot_de_passe' => 'Password123',
                'role' => RoleUtilisateur::Porteur->value,
            ])
            ->assertSessionHasErrors('utilisateur_email');
    });

    it('modifie un utilisateur', function () {
        $user = Utilisateur::factory()->porteur()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.users.update', $user), [
                'utilisateur_nom' => 'Nouveau Nom',
                'utilisateur_email' => $user->utilisateur_email,
                'role_key' => RoleUtilisateur::Porteur->value,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('utilisateurs', ['id_utilisateur' => $user->id_utilisateur, 'utilisateur_nom' => 'Nouveau Nom']);
    });

    it('active et désactive un utilisateur', function () {
        $user = Utilisateur::factory()->porteur()->create(['utilisateur_actif' => true]);

        $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-active', $user))
            ->assertRedirect();

        expect($user->fresh()->utilisateur_actif)->toBeFalse();

        $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-active', $user))
            ->assertRedirect();

        expect($user->fresh()->utilisateur_actif)->toBeTrue();
    });

    it('l\'admin ne peut pas se désactiver lui-même', function () {
        $this->actingAs($this->admin)
            ->patch(route('admin.users.toggle-active', $this->admin))
            ->assertSessionHasErrors('error');

        expect($this->admin->fresh()->utilisateur_actif)->toBeTrue();
    });
});
