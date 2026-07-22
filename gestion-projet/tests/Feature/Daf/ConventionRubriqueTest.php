<?php

use App\Models\Convention;
use App\Models\Projet;
use App\Models\Rubrique;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

describe('RG09 – Rubriques ≤ Convention', function () {
    it('refuse une rubrique dont le montant dépasse la convention', function () {
        $daf = Utilisateur::factory()->daf()->create();
        $projet = Projet::factory()->enCours()->create([
            'projet_montant_estime' => 50_000_000,
        ]);
        $convention = Convention::factory()->for($projet)->create([
            'convention_montant' => 10_000_000,
            'convention_taux_conversion' => 1.0,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.projets.conventions.rubriques.store', [$projet, $convention]), [
                'libelle' => 'Rubrique trop chère',
                'montant_prevu' => 15_000_000,
            ])
            ->assertSessionHasErrors('montant_prevu');
    });

    it('accepte une rubrique dans la limite de la convention', function () {
        $daf = Utilisateur::factory()->daf()->create();
        $projet = Projet::factory()->enCours()->create([
            'projet_montant_estime' => 50_000_000,
        ]);
        $convention = Convention::factory()->for($projet)->create([
            'convention_montant' => 10_000_000,
            'convention_taux_conversion' => 1.0,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.projets.conventions.rubriques.store', [$projet, $convention]), [
                'libelle' => 'Rubrique dans les limites',
                'montant_prevu' => 8_000_000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('rubriques', [
            'id_convention' => $convention->id_convention,
            'rubrique_montant' => 8_000_000,
        ]);
    });

    it('refuse une deuxième rubrique qui ferait dépasser le total', function () {
        $daf = Utilisateur::factory()->daf()->create();
        $projet = Projet::factory()->enCours()->create([
            'projet_montant_estime' => 50_000_000,
        ]);
        $convention = Convention::factory()->for($projet)->create([
            'convention_montant' => 10_000_000,
            'convention_taux_conversion' => 1.0,
        ]);

        Rubrique::factory()->for($convention)->create([
            'rubrique_montant' => 7_000_000,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.projets.conventions.rubriques.store', [$projet, $convention]), [
                'libelle' => 'Deuxième rubrique',
                'montant_prevu' => 5_000_000,
            ])
            ->assertSessionHasErrors('montant_prevu');
    });

    it('accepte si le budget total du projet dépasse l\'estimé initial (bypass de la contrainte)', function () {
        $daf = Utilisateur::factory()->daf()->create();
        $projet = Projet::factory()->enCours()->create([
            'projet_montant_estime' => 10_000_000,
        ]);
        $convention = Convention::factory()->for($projet)->create([
            'convention_montant' => 10_000_000,
            'convention_taux_conversion' => 1.0,
        ]);

        $convention2 = Convention::factory()->for($projet)->create([
            'convention_montant' => 10_000_000,
            'convention_taux_conversion' => 1.0,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.projets.conventions.rubriques.store', [$projet, $convention]), [
                'libelle' => 'Rubrique > convention mais budget total dépassé',
                'montant_prevu' => 12_000_000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('rubriques', [
            'id_convention' => $convention->id_convention,
            'rubrique_montant' => 12_000_000,
        ]);
    });
});
