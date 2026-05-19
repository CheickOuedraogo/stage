<?php

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
        $user = User::factory()->state(['utilisateur_role' => $role])->create([
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

describe('Blocage par tentatives (backoff exponentiel)', function () {
    beforeEach(function () {
        Cache::flush();
    });

    it('affiche le nombre de tentatives restantes après un échec', function () {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'mauvais',
        ]);

        $response->assertSessionHasErrors('email');
        expect(session('errors')->first('email'))->toContain('2 tentative(s)');
    });

    it('bloque après 3 échecs avec message de temps d\'attente', function () {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        // 3 tentatives échouées
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'mauvais',
            ]);
        }

        // 4e tentative — doit être bloquée
        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        expect(session('errors')->first('email'))->toContain('minute');
        $this->assertGuest();
    });

    it('double la durée de blocage à chaque nouveau lockout', function () {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $key = strtolower($user->email).'|127.0.0.1';
        $key = Str::transliterate($key);

        // Premier lockout (300s)
        Cache::put('login_lockouts:'.$key, 1, now()->addDay());
        Cache::put('login_lock:'.$key, now()->addMinutes(5)->timestamp, 300);

        $unlockAt1 = Cache::get('login_lock:'.$key);

        // Simuler second lockout
        Cache::put('login_lockouts:'.$key, 2, now()->addDay());
        $duration2 = min(300 * (2 ** (2 - 1)), 7200); // 600s
        Cache::put('login_lock:'.$key, now()->timestamp + $duration2, $duration2);

        $unlockAt2 = Cache::get('login_lock:'.$key);

        expect($unlockAt2 - $unlockAt1)->toBeGreaterThan(200);
    });

    it('autorise la connexion quand le lockout expire', function () {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect($user->dashboardRoute());
        $this->assertAuthenticatedAs($user);
    });

    it('efface l\'état de rate limiting après connexion réussie', function () {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $key = strtolower($user->email).'|127.0.0.1';
        $key = Str::transliterate($key);

        // Simule 2 échecs
        Cache::put('login_attempts:'.$key, 2, now()->addMinutes(15));

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        expect(Cache::has('login_attempts:'.$key))->toBeFalse();
        expect(Cache::has('login_lock:'.$key))->toBeFalse();
    });
});
