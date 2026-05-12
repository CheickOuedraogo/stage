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
        'status' => ConventionStatus::Terminee,
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
            ])
            ->assertRedirect();

        $projet->refresh();

        expect($projet->status)->toBe(ProjectStatus::Termine)
            ->and($projet->date_fin_reelle->toDateString())->toBe('2025-12-31');
    });

    it('notifie le porteur à la clôture', function () {
        Notification::fake();

        ['daf' => $daf, 'porteur' => $porteur, 'projet' => $projet] = makeProjetClotureable();

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
            ]);

        Notification::assertSentTo($porteur, ProjetCloture::class);
    });

    it('refuse si une demande est encore en cours', function () {
        ['daf' => $daf, 'porteur' => $porteur, 'projet' => $projet, 'convention' => $convention, 'rubrique' => $rubrique] = makeProjetClotureable();

        DemandeDepense::factory()->for($convention)->for($rubrique)->create([
            'porteur_id' => $porteur->id,
            'status' => DemandeStatus::Soumise,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
            ])
            ->assertSessionHasErrors('projet');

        expect($projet->fresh()->status)->toBe(ProjectStatus::EnCours);
    });

    it('refuse si une convention est encore active', function () {
        ['daf' => $daf, 'projet' => $projet, 'convention' => $convention] = makeProjetClotureable();

        $convention->update(['status' => ConventionStatus::Active]);

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
            ])
            ->assertSessionHasErrors('projet');

        expect($projet->fresh()->status)->toBe(ProjectStatus::EnCours);
    });

    it('refuse si le projet n\'est pas en cours', function () {
        ['daf' => $daf, 'porteur' => $porteur] = makeProjetClotureable();

        $projet = Projet::factory()->for($porteur, 'porteur')->create([
            'status' => ProjectStatus::Suspendu,
        ]);
        Convention::factory()->for($projet)->create(['status' => ConventionStatus::Terminee]);

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
            ])
            ->assertSessionHasErrors('projet');
    });

    it('refuse à un porteur (403)', function () {
        ['porteur' => $porteur, 'projet' => $projet] = makeProjetClotureable();

        $this->actingAs($porteur)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
            ])
            ->assertForbidden();
    });

    it('refuse sans date_fin_reelle (validation)', function () {
        ['daf' => $daf, 'projet' => $projet] = makeProjetClotureable();

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [])
            ->assertSessionHasErrors('date_fin_reelle');
    });
});

// ── Bilan ─────────────────────────────────────────────────────────────────────

describe('Bilan de clôture', function () {
    it('la DAF peut consulter le bilan d\'un projet terminé', function () {
        ['daf' => $daf, 'porteur' => $porteur] = makeProjetClotureable();

        $projet = Projet::factory()->termine()->for($porteur, 'porteur')->create();
        Convention::factory()->for($projet)->create(['status' => ConventionStatus::Terminee]);

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
        Convention::factory()->for($projet)->create(['status' => ConventionStatus::Terminee]);

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
        Convention::factory()->for($projet)->create(['status' => ConventionStatus::Terminee]);

        $this->actingAs($daf)
            ->get(route('daf.projets.bilan.pdf', $projet))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    });
});
