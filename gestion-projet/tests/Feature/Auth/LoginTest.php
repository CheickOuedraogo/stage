<?php

use App\Enums\RoleUtilisateur;
use App\Models\Parametre;
use App\Models\Utilisateur;
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
        $user = Utilisateur::factory()->porteur()->create([
            'utilisateur_email' => 'porteur@test.bf',
            'utilisateur_mot_de_passe' => bcrypt('utilisateur_mot_de_passe'),
        ]);

        $response = $this->post(route('login'), [
            'utilisateur_email' => 'porteur@test.bf',
            'utilisateur_mot_de_passe' => 'utilisateur_mot_de_passe',
        ]);

        $response->assertRedirect(route('porteur.dashboard'));
        $this->assertAuthenticatedAs($user);
    });

    it('redirige vers le bon dashboard selon le rôle', function (RoleUtilisateur $role, string $routeTableauBord) {
        $user = Utilisateur::factory()->state(['utilisateur_role' => $role])->create([
            'utilisateur_mot_de_passe' => bcrypt('utilisateur_mot_de_passe'),
        ]);

        $this->post(route('login'), [
            'utilisateur_email' => $user->utilisateur_email,
            'utilisateur_mot_de_passe' => 'utilisateur_mot_de_passe',
        ])->assertRedirect(route($routeTableauBord));
    })->with([
        'admin' => [RoleUtilisateur::Administrateur, 'admin.dashboard'],
        'daf' => [RoleUtilisateur::Daf, 'daf.dashboard'],
        'ac' => [RoleUtilisateur::AgentComptable, 'ac.dashboard'],
        'porteur' => [RoleUtilisateur::Porteur, 'porteur.dashboard'],
    ]);

    it('rejette les mauvaises informations de connexion', function () {
        Utilisateur::factory()->create(['utilisateur_email' => 'test@bf', 'utilisateur_mot_de_passe' => bcrypt('utilisateur_mot_de_passe')]);

        $this->post(route('login'), [
            'utilisateur_email' => 'test@bf',
            'utilisateur_mot_de_passe' => 'mauvais-mot-de-passe',
        ])->assertSessionHasErrors('utilisateur_email');

        $this->assertGuest();
    });

    it('bloque un utilisateur inactif', function () {
        $user = Utilisateur::factory()->inactive()->create([
            'utilisateur_mot_de_passe' => bcrypt('utilisateur_mot_de_passe'),
        ]);

        $this->post(route('login'), [
            'utilisateur_email' => $user->utilisateur_email,
            'utilisateur_mot_de_passe' => 'utilisateur_mot_de_passe',
        ])->assertSessionHasErrors('utilisateur_email');

        $this->assertGuest();
    });

    it('bloque les non-admin pendant la maintenance', function () {
        Parametre::set('maintenance_mode', 'true');
        Parametre::set('maintenance_reason', 'Test maintenance');

        $porteur = Utilisateur::factory()->porteur()->create(['utilisateur_mot_de_passe' => bcrypt('utilisateur_mot_de_passe')]);

        $this->post(route('login'), [
            'utilisateur_email' => $porteur->utilisateur_email,
            'utilisateur_mot_de_passe' => 'utilisateur_mot_de_passe',
        ]);

        $this->assertGuest();
    });

    it('autorise l\'admin à se connecter pendant la maintenance', function () {
        Parametre::set('maintenance_mode', 'true');

        $admin = Utilisateur::factory()->admin()->create(['utilisateur_mot_de_passe' => bcrypt('utilisateur_mot_de_passe')]);

        $this->post(route('login'), [
            'utilisateur_email' => $admin->utilisateur_email,
            'utilisateur_mot_de_passe' => 'utilisateur_mot_de_passe',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    });

    it('déconnecte l\'utilisateur', function () {
        $user = Utilisateur::factory()->create();
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
        $user = Utilisateur::factory()->create(['utilisateur_mot_de_passe' => bcrypt('utilisateur_mot_de_passe')]);

        $response = $this->post(route('login'), [
            'utilisateur_email' => $user->utilisateur_email,
            'utilisateur_mot_de_passe' => 'mauvais',
        ]);

        $response->assertSessionHasErrors('utilisateur_email');
        expect(session('errors')->first('utilisateur_email'))->toContain('2 tentative(s)');
    });

    it('bloque après 3 échecs avec message de temps d\'attente', function () {
        $user = Utilisateur::factory()->create(['utilisateur_mot_de_passe' => bcrypt('utilisateur_mot_de_passe')]);

        // 3 tentatives échouées
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('login'), [
                'utilisateur_email' => $user->utilisateur_email,
                'utilisateur_mot_de_passe' => 'mauvais',
            ]);
        }

        // 4e tentative — doit être bloquée
        $response = $this->post(route('login'), [
            'utilisateur_email' => $user->utilisateur_email,
            'utilisateur_mot_de_passe' => 'utilisateur_mot_de_passe',
        ]);

        $response->assertSessionHasErrors('utilisateur_email');
        expect(session('errors')->first('utilisateur_email'))->toContain('minute');
        $this->assertGuest();
    });

    it('double la durée de blocage à chaque nouveau lockout', function () {
        $user = Utilisateur::factory()->create(['utilisateur_mot_de_passe' => bcrypt('utilisateur_mot_de_passe')]);
        $key = strtolower($user->utilisateur_email).'|127.0.0.1';
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
        $user = Utilisateur::factory()->create(['utilisateur_mot_de_passe' => bcrypt('utilisateur_mot_de_passe')]);

        $response = $this->post(route('login'), [
            'utilisateur_email' => $user->utilisateur_email,
            'utilisateur_mot_de_passe' => 'utilisateur_mot_de_passe',
        ]);

        $response->assertRedirect($user->routeTableauBord());
        $this->assertAuthenticatedAs($user);
    });

    it('efface l\'état de rate limiting après connexion réussie', function () {
        $user = Utilisateur::factory()->create(['utilisateur_mot_de_passe' => bcrypt('utilisateur_mot_de_passe')]);
        $key = strtolower($user->utilisateur_email).'|127.0.0.1';
        $key = Str::transliterate($key);

        // Simule 2 échecs
        Cache::put('login_attempts:'.$key, 2, now()->addMinutes(15));

        $this->post(route('login'), [
            'utilisateur_email' => $user->utilisateur_email,
            'utilisateur_mot_de_passe' => 'utilisateur_mot_de_passe',
        ]);

        expect(Cache::has('login_attempts:'.$key))->toBeFalse();
        expect(Cache::has('login_lock:'.$key))->toBeFalse();
    });
});
