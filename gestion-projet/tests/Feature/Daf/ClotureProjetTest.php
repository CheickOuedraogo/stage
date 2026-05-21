<?php

use App\Enums\StatutConvention;
use App\Enums\StatutDemande;
use App\Enums\StatutProjet;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Projet;
use App\Models\Rubrique;
use App\Models\Utilisateur;
use App\Notifications\ProjetClotureNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;

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

// ── Clôture réussie ──────────────────────────────────────────────────────────

describe('Clôture de projet', function () {
    it('clôture un projet éligible : statut passe à terminé et date_fin_reelle est remplie', function () {
        ['daf' => $daf, 'projet' => $projet] = makeProjetClotureNotificationable();

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ])
            ->assertRedirect();

        $projet->refresh();

        expect($projet->projet_statut)->toBe(StatutProjet::Termine)
            ->and($projet->projet_date_fin_reelle->toDateString())->toBe('2025-12-31');
    });

    it('notifie le porteur à la clôture', function () {
        Notification::fake();

        ['daf' => $daf, 'porteur' => $porteur, 'projet' => $projet] = makeProjetClotureNotificationable();

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ]);

        Notification::assertSentTo($porteur, ProjetClotureNotification::class);
    });

    it('refuse si une demande est encore en cours', function () {
        ['daf' => $daf, 'porteur' => $porteur, 'projet' => $projet, 'convention' => $convention, 'rubrique' => $rubrique] = makeProjetClotureNotificationable();

        DemandeDepense::factory()->for($convention)->for($rubrique)->create([
            'id_porteur' => $porteur->id_utilisateur,
            'demande_statut' => StatutDemande::Soumise,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ])
            ->assertSessionHasErrors('projet');

        expect($projet->fresh()->projet_statut)->toBe(StatutProjet::EnCours);
    });

    it('refuse si une convention est encore active', function () {
        ['daf' => $daf, 'projet' => $projet, 'convention' => $convention] = makeProjetClotureNotificationable();

        $convention->update(['convention_statut' => StatutConvention::Active]);

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ])
            ->assertSessionHasErrors('projet');

        expect($projet->fresh()->projet_statut)->toBe(StatutProjet::EnCours);
    });

    it('refuse si le projet n\'est pas en cours', function () {
        ['daf' => $daf, 'porteur' => $porteur] = makeProjetClotureNotificationable();

        $projet = Projet::factory()->for($porteur, 'porteur')->create([
            'projet_statut' => StatutProjet::Suspendu,
        ]);
        Convention::factory()->for($projet)->create(['convention_statut' => StatutConvention::Terminee]);

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ])
            ->assertSessionHasErrors('projet');
    });

    it('refuse à un porteur (403)', function () {
        ['porteur' => $porteur, 'projet' => $projet] = makeProjetClotureNotificationable();

        $this->actingAs($porteur)
            ->post(route('daf.projets.cloturer', $projet), [
                'date_fin_reelle' => '2025-12-31',
                'statut_final' => 'succes',
            ])
            ->assertForbidden();
    });

    it('refuse sans date_fin_reelle (validation)', function () {
        ['daf' => $daf, 'projet' => $projet] = makeProjetClotureNotificationable();

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), ['statut_final' => 'succes'])
            ->assertSessionHasErrors('date_fin_reelle');
    });

    it('refuse sans statut_final (validation)', function () {
        ['daf' => $daf, 'projet' => $projet] = makeProjetClotureNotificationable();

        $this->actingAs($daf)
            ->post(route('daf.projets.cloturer', $projet), ['date_fin_reelle' => '2025-12-31'])
            ->assertSessionHasErrors('statut_final');
    });

    it('enregistre le statut_final lors de la clôture', function () {
        ['daf' => $daf, 'projet' => $projet] = makeProjetClotureNotificationable();

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
});
