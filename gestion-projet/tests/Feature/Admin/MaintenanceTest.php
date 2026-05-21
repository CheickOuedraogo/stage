<?php

use App\Models\Parametre;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

describe('Mode maintenance', function () {
    beforeEach(function () {
        $this->admin = Utilisateur::factory()->admin()->create();
        Parametre::set('maintenance_mode', 'false');
    });

    it('active le mode maintenance', function () {
        $this->actingAs($this->admin)
            ->patch(route('admin.maintenance.update'), [
                'active' => true,
                'reason' => 'Mise à jour du système',
                'until' => now()->addHours(2)->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect();

        expect(Parametre::isMaintenanceActive())->toBeTrue();
        expect(Parametre::get('maintenance_reason'))->toBe('Mise à jour du système');
    });

    it('désactive le mode maintenance', function () {
        Parametre::set('maintenance_mode', 'true');

        $this->actingAs($this->admin)
            ->patch(route('admin.maintenance.update'), [
                'active' => false,
                'reason' => null,
            ])
            ->assertRedirect();

        expect(Parametre::isMaintenanceActive())->toBeFalse();
    });

    it('redirige les non-admin vers la maintenance', function () {
        Parametre::set('maintenance_mode', 'true');
        Parametre::set('maintenance_reason', 'Maintenance test');

        $porteur = Utilisateur::factory()->porteur()->create();

        $this->actingAs($porteur)
            ->get(route('porteur.dashboard'))
            ->assertStatus(503);
    });

    it('laisse passer l\'admin en maintenance', function () {
        Parametre::set('maintenance_mode', 'true');

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200);
    });

    it('la raison est obligatoire lors de l\'activation', function () {
        $this->actingAs($this->admin)
            ->patch(route('admin.maintenance.update'), [
                'active' => true,
                'reason' => '',
            ])
            ->assertSessionHasErrors('reason');
    });
});
