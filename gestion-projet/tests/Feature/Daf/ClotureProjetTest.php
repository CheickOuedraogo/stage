<?php

use App\Enums\StatutConvention;
use App\Models\Convention;
use App\Models\Projet;
use App\Models\Rubrique;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

// ── Helpers ─────────────────────────────────────────────────────────────────

function makeProjetClotureNotificationable(): array
{
    $daf = Utilisateur::factory()->daf()->create();
    $porteur = Utilisateur::factory()->porteur()->create();
    $projet = Projet::factory()->enCours()->for($porteur, 'porteur')->create();

    $convention = Convention::factory()->for($projet)->create([
        'convention_statut' => StatutConvention::Terminee,
    ]);

    $rubrique = Rubrique::factory()->for($convention)->create();

    return compact('daf', 'porteur', 'projet', 'convention', 'rubrique');
}

// ── Bilan ─────────────────────────────────────────────────────────────────────

describe('Bilan de clôture', function () {
    it('la DAF peut consulter le bilan d\'un projet terminé', function () {
        ['daf' => $daf, 'porteur' => $porteur] = makeProjetClotureNotificationable();

        $projet = Projet::factory()->termine()->for($porteur, 'porteur')->create();
        Convention::factory()->for($projet)->create(['convention_statut' => StatutConvention::Terminee]);

        $this->actingAs($daf)
            ->get(route('daf.projets.bilan', $projet))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('daf/Projets/Bilan')
                ->has('bilan')
                ->has('pdf_url')
            );
    });

    it('le porteur peut consulter le bilan de son propre projet', function () {
        $porteur = Utilisateur::factory()->porteur()->create();
        $projet = Projet::factory()->termine()->for($porteur, 'porteur')->create();
        Convention::factory()->for($projet)->create(['convention_statut' => StatutConvention::Terminee]);

        $this->actingAs($porteur)
            ->get(route('porteur.projets.bilan', $projet))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('daf/Projets/Bilan'));
    });

    it('le porteur ne peut pas consulter le bilan d\'un autre projet', function () {
        $porteur = Utilisateur::factory()->porteur()->create();
        $autrePorteur = Utilisateur::factory()->porteur()->create();
        $projet = Projet::factory()->termine()->for($autrePorteur, 'porteur')->create();

        $this->actingAs($porteur)
            ->get(route('porteur.projets.bilan', $projet))
            ->assertForbidden();
    });

    it('génère le PDF sans erreur HTTP', function () {
        ['daf' => $daf, 'porteur' => $porteur] = makeProjetClotureNotificationable();

        $projet = Projet::factory()->termine()->for($porteur, 'porteur')->create();
        Convention::factory()->for($projet)->create(['convention_statut' => StatutConvention::Terminee]);

        $this->actingAs($daf)
            ->get(route('daf.projets.bilan.pdf', $projet))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    });

    it('génère l\'Excel pour la DAF sans erreur HTTP', function () {
        ['daf' => $daf, 'porteur' => $porteur] = makeProjetClotureNotificationable();

        $projet = Projet::factory()->termine()->for($porteur, 'porteur')->create();
        Convention::factory()->for($projet)->create(['convention_statut' => StatutConvention::Terminee]);

        $this->actingAs($daf)
            ->get(route('daf.projets.bilan.excel', $projet))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    });

    it('le porteur peut télécharger son Excel de bilan', function () {
        $porteur = Utilisateur::factory()->porteur()->create();
        $projet = Projet::factory()->termine()->for($porteur, 'porteur')->create();
        Convention::factory()->for($projet)->create(['convention_statut' => StatutConvention::Terminee]);

        $this->actingAs($porteur)
            ->get(route('porteur.projets.bilan.excel', $projet))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    });

    it('le porteur ne peut pas télécharger l\'Excel de bilan d\'un autre projet', function () {
        $porteur = Utilisateur::factory()->porteur()->create();
        $autrePorteur = Utilisateur::factory()->porteur()->create();
        $projet = Projet::factory()->termine()->for($autrePorteur, 'porteur')->create();

        $this->actingAs($porteur)
            ->get(route('porteur.projets.bilan.excel', $projet))
            ->assertForbidden();
    });

    it('l\'AC peut consulter le bilan et télécharger l\'Excel de bilan d\'un projet terminé', function () {
        $ac = Utilisateur::factory()->ac()->create();
        $porteur = Utilisateur::factory()->porteur()->create();
        $projet = Projet::factory()->termine()->for($porteur, 'porteur')->create();
        Convention::factory()->for($projet)->create(['convention_statut' => StatutConvention::Terminee]);

        $this->actingAs($ac)
            ->get(route('ac.projets.bilan', $projet))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('daf/Projets/Bilan')
                ->has('bilan')
                ->has('pdf_url')
                ->has('excel_url')
            );

        $this->actingAs($ac)
            ->get(route('ac.projets.bilan.excel', $projet))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    });
});
