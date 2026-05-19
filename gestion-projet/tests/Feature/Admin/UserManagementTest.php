<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

describe('Gestion des utilisateurs (Admin)', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('liste les utilisateurs', function () {
        User::factory()->count(5)->porteur()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('admin/Users/Index'));
    });

    it('un non-admin ne peut pas accéder à la liste', function () {
        $porteur = User::factory()->porteur()->create();

        $this->actingAs($porteur)
            ->get(route('admin.users.index'))
            ->assertStatus(403);
    });

    it('crée un utilisateur', function () {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Nouveau Utilisateur',
                'email' => 'nouveau@ujkz.bf',
                'password' => 'Password123',
                'utilisateur_role' => UserRole::Porteur->value,
                'telephone' => '+226 70 00 00 00',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'nouveau@ujkz.bf',
            'utilisateur_role' => UserRole::Porteur->value,
        ]);
    });

    it('valide les données lors de la création', function () {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => '',
                'email' => 'pas-un-email',
                'utilisateur_role' => 'role-invalide',
            ])
            ->assertSessionHasErrors(['name', 'email', 'utilisateur_role', 'password']);
    });

    it('rejette un email déjà utilisé', function () {
        User::factory()->create(['email' => 'existant@test.bf']);

        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Test',
                'email' => 'existant@test.bf',
                'password' => 'Password123',
                'role' => UserRole::Porteur->value,
            ])
            ->assertSessionHasErrors('email');
    });

    it('modifie un utilisateur', function () {
        $user = User::factory()->porteur()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.users.update', $user), [
                'name' => 'Nouveau Nom',
                'email' => $user->email,
                'utilisateur_role' => UserRole::Porteur->value,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id_utilisateur' => $user->id, 'name' => 'Nouveau Nom']);
    });

    it('active et désactive un utilisateur', function () {
        $user = User::factory()->porteur()->create(['utilisateur_actif' => true]);

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
