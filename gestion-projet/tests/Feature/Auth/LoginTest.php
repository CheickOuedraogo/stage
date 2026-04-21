<?php

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

describe('Connexion', function () {
    it('affiche la page de connexion', function () {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('auth/Login'));
    });

    it('connecte un utilisateur avec les bonnes informations', function () {
        $user = User::factory()->porteur()->create([
            'email' => 'porteur@test.bf',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'porteur@test.bf',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('porteur.dashboard'));
        $this->assertAuthenticatedAs($user);
    });

    it('redirige vers le bon dashboard selon le rôle', function (UserRole $role, string $dashboardRoute) {
        $user = User::factory()->state(['role' => $role])->create([
            'password' => bcrypt('password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route($dashboardRoute));
    })->with([
        'admin' => [UserRole::Admin, 'admin.dashboard'],
        'daf' => [UserRole::Daf, 'daf.dashboard'],
        'ac' => [UserRole::Ac, 'ac.dashboard'],
        'porteur' => [UserRole::Porteur, 'porteur.dashboard'],
    ]);

    it('rejette les mauvaises informations de connexion', function () {
        User::factory()->create(['email' => 'test@bf', 'password' => bcrypt('password')]);

        $this->post(route('login'), [
            'email' => 'test@bf',
            'password' => 'mauvais-mot-de-passe',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    it('bloque un utilisateur inactif', function () {
        $user = User::factory()->inactive()->create([
            'password' => bcrypt('password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    it('bloque les non-admin pendant la maintenance', function () {
        Setting::set('maintenance_mode', 'true');
        Setting::set('maintenance_reason', 'Test maintenance');

        $porteur = User::factory()->porteur()->create(['password' => bcrypt('password')]);

        $this->post(route('login'), [
            'email' => $porteur->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    });

    it('autorise l\'admin à se connecter pendant la maintenance', function () {
        Setting::set('maintenance_mode', 'true');

        $admin = User::factory()->admin()->create(['password' => bcrypt('password')]);

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    });

    it('déconnecte l\'utilisateur', function () {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    });
});
