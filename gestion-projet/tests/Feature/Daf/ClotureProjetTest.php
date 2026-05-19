<?php

use App\Enums\ConventionStatus;
use App\Enums\DemandeStatus;
use App\Enums\ProjectStatus;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Projet;
use App\Models\Rubrique;
use App\Models\User;
use App\Notifications\ProjetCloture;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(LazilyRefreshDatabase::class);

// ── Helpers ─────────────────────────────────────────────────────────────────

function makeProjetClotureable(): array
{
    $daf = User::factory()->daf()->create();
    $porteur = User::factory()->porteur()->create();
    $projet = Projet::factory()->enCours()->for($porteur, 'porteur')->create();

    $convention = Convention::factory()->for($projet)->create([
        'convention_statut' => ConventionStatus::Terminee,
    ]);

    $rubrique = Rubrique::factory()->for($convention)->create();

    return compact('daf', 'porteur', 'projet', 'convention', 'rubrique');
}

// ── Clôture réussie ──────────────────────────────────────────────────────────

describe('Clôture de projet', function () {
    it('clôture un projet éligible : statut passe à terminé et date_fin_reelle est remplie', function () {
        ['daf' => $daf, 'projet' => $projet] = makeProjetClotureable();

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ])
            ->assertRedirect();

        $projet->refresh();

        expect($projet->projet_statut)->toBe(ProjectStatus::Termine)
            ->and($projet->projet_date_fin_reelle->toDateString())->toBe('2025-12-31');
    });

    it('notifie le porteur à la clôture', function () {
        Notification::fake();

        ['daf' => $daf, 'porteur' => $porteur, 'projet' => $projet] = makeProjetClotureable();

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ]);

        Notification::assertSentTo($porteur, ProjetCloture::class);
    });

    it('refuse si une demande est encore en cours', function () {
        ['daf' => $daf, 'porteur' => $porteur, 'projet' => $projet, 'convention' => $convention, 'rubrique' => $rubrique] = makeProjetClotureable();

        DemandeDepense::factory()->for($convention)->for($rubrique)->create([
            'id_porteur' => $porteur->id,
            'demande_statut' => DemandeStatus::Soumise,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ])
            ->assertSessionHasErrors('projet');

        expect($projet->fresh()->projet_statut)->toBe(ProjectStatus::EnCours);
    });

    it('refuse si une convention est encore active', function () {
        ['daf' => $daf, 'projet' => $projet, 'convention' => $convention] = makeProjetClotureable();

        $convention->update(['convention_statut' => ConventionStatus::Active]);

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ])
            ->assertSessionHasErrors('projet');

        expect($projet->fresh()->projet_statut)->toBe(ProjectStatus::EnCours);
    });

    it('refuse si le projet n\'est pas en cours', function () {
        ['daf' => $daf, 'porteur' => $porteur] = makeProjetClotureable();

        $projet = Projet::factory()->for($porteur, 'porteur')->create([
            'projet_statut' => ProjectStatus::Suspendu,
        ]);
        Convention::factory()->for($projet)->create(['convention_statut' => ConventionStatus::Terminee]);

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ])
            ->assertSessionHasErrors('projet');
    });

    it('refuse à un porteur (403)', function () {
        ['porteur' => $porteur, 'projet' => $projet] = makeProjetClotureable();

        $this->actingAs($porteur)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ])
            ->assertForbidden();
    });

    it('refuse sans date_fin_reelle (validation)', function () {
        ['daf' => $daf, 'projet' => $projet] = makeProjetClotureable();

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), ['statut_final' => 'succes'])
            ->assertSessionHasErrors('date_fin_reelle');
    });

    it('refuse sans statut_final (validation)', function () {
        ['daf' => $daf, 'projet' => $projet] = makeProjetClotureable();

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), ['date_fin_reelle' => '2025-12-31'])
            ->assertSessionHasErrors('statut_final');
    });

    it('enregistre le statut_final lors de la clôture', function () {
        ['daf' => $daf, 'projet' => $projet] = makeProjetClotureable();

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'echec',
            ]);

        expect($projet->fresh()->statut_final->value)->toBe('echec');
    });
});

// ── Bilan ─────────────────────────────────────────────────────────────────────

describe('Bilan de clôture', function () {
    it('la DAF peut consulter le bilan d\'un projet terminé', function () {
        ['daf' => $daf, 'porteur' => $porteur] = makeProjetClotureable();

        $projet = Projet::factory()->termine()->for($porteur, 'porteur')->create();
        Convention::factory()->for($projet)->create(['convention_statut' => ConventionStatus::Terminee]);

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
        $porteur = User::factory()->porteur()->create();
        $projet = Projet::factory()->termine()->for($porteur, 'porteur')->create();
        Convention::factory()->for($projet)->create(['convention_statut' => ConventionStatus::Terminee]);

        $this->actingAs($porteur)
            ->get(route('porteur.projets.bilan', $projet))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('daf/Projets/Bilan'));
    });

    it('le porteur ne peut pas consulter le bilan d\'un autre projet', function () {
        $porteur = User::factory()->porteur()->create();
        $autrePorteur = User::factory()->porteur()->create();
        $projet = Projet::factory()->termine()->for($autrePorteur, 'porteur')->create();

        $this->actingAs($porteur)
            ->get(route('porteur.projets.bilan', $projet))
            ->assertForbidden();
    });

    it('génère le PDF sans erreur HTTP', function () {
        ['daf' => $daf, 'porteur' => $porteur] = makeProjetClotureable();

        $projet = Projet::factory()->termine()->for($porteur, 'porteur')->create();
        Convention::factory()->for($projet)->create(['convention_statut' => ConventionStatus::Terminee]);

        $this->actingAs($daf)
            ->get(route('daf.projets.bilan.pdf', $projet))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    });
});
