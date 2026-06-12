<?php

use App\Enums\StatutConvention;
use App\Enums\StatutProjet;
use App\Models\Convention;
use App\Models\Projet;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

/**
 * Test pour reproduire le problème du clic du DAF pour mettre en cours un projet
 */
describe('Mise en cours de projet', function () {
    it('projet peut être mis en cours par le DAF', function () {
        $daf = Utilisateur::factory()->daf()->create();
        $porteur = Utilisateur::factory()->porteur()->create();
        $projet = Projet::factory()->create([
            'id_porteur' => $porteur->id_utilisateur,
            'projet_statut' => StatutProjet::EnAttenteFinancement->value,
        ]);

        $convention = Convention::factory()->for($projet)->create([
            'convention_statut' => StatutConvention::Active->value,
        ]);

        $response = $this->actingAs($daf)
            ->post(route('daf.projets.mettre-en-cours', $projet))
            ->assertRedirect()
            ->assertSessionHas('success');

        $projet->refresh();

        expect($projet->projet_statut)->toBe(StatutProjet::EnCours);
    });

    it('projet refuse si pas en attente de financement', function () {
        $daf = Utilisateur::factory()->daf()->create();
        $porteur = Utilisateur::factory()->porteur()->create();
        $projet = Projet::factory()->create([
            'id_porteur' => $porteur->id_utilisateur,
            'projet_statut' => StatutProjet::EnAttenteFinancement->value,
        ]);

        $convention = Convention::factory()->for($projet)->create([
            'convention_statut' => StatutConvention::Active->value,
        ]);

        // Mettre le projet en cours
        $projet->update(['projet_statut' => StatutProjet::EnCours->value]);

        $response = $this->actingAs($daf)
            ->post(route('daf.projets.mettre-en-cours', $projet))
            ->assertSessionHasErrors('projet');

        $projet->refresh();
        expect($projet->projet_statut)->toBe(StatutProjet::EnCours);
    });

    it('projet refuse si pas de convention active', function () {
        $daf = Utilisateur::factory()->daf()->create();
        $porteur = Utilisateur::factory()->porteur()->create();
        $projet = Projet::factory()->create([
            'id_porteur' => $porteur->id_utilisateur,
            'projet_statut' => StatutProjet::EnAttenteFinancement->value,
        ]);

        $convention = Convention::factory()->for($projet)->create([
            'convention_statut' => StatutConvention::Terminee->value,
        ]);

        $response = $this->actingAs($daf)
            ->post(route('daf.projets.mettre-en-cours', $projet))
            ->assertSessionHasErrors('projet');

        $projet->refresh();
        expect($projet->projet_statut)->toBe(StatutProjet::EnAttenteFinancement);
    });
});
