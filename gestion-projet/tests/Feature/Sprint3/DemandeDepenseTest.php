<?php

use App\Enums\ModePaiement;
use App\Enums\StatutDemande;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\Rubrique;
use App\Models\Utilisateur;
use App\Models\Versement;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

// ── Helpers ────────────────────────────────────────────────────────────────────

function makeConventionWithRubrique(int $montantRubrique = 10_000_000): array
{
    $porteur = Utilisateur::factory()->porteur()->create();
    $projet = Projet::factory()->enCours()->for($porteur, 'porteur')->create();
    $convention = Convention::factory()->for($projet)->create(['convention_montant' => 20_000_000, 'convention_taux_conversion' => 1.0]);
    $rubrique = Rubrique::factory()->for($convention)->create(['rubrique_montant' => $montantRubrique]);

    Versement::create([
        'id_convention' => $convention->id_convention,
        'versement_montant' => 20_000_000,
        'versement_date_reception' => today()->toDateString(),
    ]);

    return compact('porteur', 'projet', 'convention', 'rubrique');
}

// ── Porteur: liste des demandes ────────────────────────────────────────────────

describe('Porteur – liste des demandes', function () {
    it('affiche la liste des demandes du porteur', function () {
        ['porteur' => $porteur, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->create([
            'id_porteur' => $porteur->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($porteur)
            ->get(route('porteur.demandes.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('porteur/Demandes/Index')
                ->has('demandes', 1)
            );
    });

    it('ne voit pas les demandes des autres porteurs', function () {
        $autrePorteur = Utilisateur::factory()->porteur()->create();
        ['porteur' => $porteur, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        DemandeDepense::factory()->create([
            'id_porteur' => $autrePorteur->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($porteur)
            ->get(route('porteur.demandes.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('demandes', 0));
    });
});

// ── Porteur: créer une demande ─────────────────────────────────────────────────

describe('Porteur – créer une demande', function () {
    it('affiche le formulaire de création', function () {
        ['porteur' => $porteur, 'projet' => $projet, 'convention' => $convention] = makeConventionWithRubrique();

        $this->actingAs($porteur)
            ->get(route('porteur.projets.conventions.demandes.create', [$projet, $convention]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('porteur/Demandes/Create'));
    });

    it('refuse l\'accès si le porteur ne possède pas le projet', function () {
        $autrePorteur = Utilisateur::factory()->porteur()->create();
        ['projet' => $projet, 'convention' => $convention] = makeConventionWithRubrique();

        $this->actingAs($autrePorteur)
            ->get(route('porteur.projets.conventions.demandes.create', [$projet, $convention]))
            ->assertForbidden();
    });

    it('refuse une demande si la convention n\'a pas de rubrique (RG08)', function () {
        Storage::fake('private');

        $porteur = Utilisateur::factory()->porteur()->create();
        $projet = Projet::factory()->enCours()->for($porteur, 'porteur')->create();
        $convention = Convention::factory()->for($projet)->create();

        $this->actingAs($porteur)
            ->get(route('porteur.projets.conventions.demandes.create', [$projet, $convention]))
            ->assertSessionHasErrors('convention_id');
    });

    it('soumet une demande avec justificatif PDF', function () {
        Storage::fake('private');
        Notification::fake();

        ['porteur' => $porteur, 'projet' => $projet, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique(10_000_000);

        $this->actingAs($porteur)
            ->post(route('porteur.projets.conventions.demandes.store', [$projet, $convention]), [
                'rubrique_id' => $rubrique->id_rubrique,
                'montant' => 2_000_000,
                'objet' => 'AgentComptablehat de matériel',
                'description' => 'Description détaillée',
                'justificatif' => UploadedFile::fake()->create('justif.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('porteur.demandes.index'));

        $this->assertDatabaseHas('demandes_depense', [
            'id_convention' => $convention->id_convention,
            'id_porteur' => $porteur->id_utilisateur,
            'demande_montant' => 2_000_000,
            'demande_statut' => StatutDemande::Soumise->value,
        ]);
    });

    it('refuse si le montant dépasse le solde de la rubrique', function () {
        Storage::fake('private');

        ['porteur' => $porteur, 'projet' => $projet, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique(3_000_000);

        $this->actingAs($porteur)
            ->post(route('porteur.projets.conventions.demandes.store', [$projet, $convention]), [
                'rubrique_id' => $rubrique->id_rubrique,
                'montant' => 5_000_000,
                'objet' => 'Dépassement',
                'justificatif' => UploadedFile::fake()->create('justif.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('montant');
    });

    it('refuse une deuxième demande active sur la même convention', function () {
        Storage::fake('private');

        ['porteur' => $porteur, 'projet' => $projet, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        DemandeDepense::factory()->create([
            'id_porteur' => $porteur->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
            'demande_statut' => StatutDemande::Soumise,
        ]);

        $this->actingAs($porteur)
            ->post(route('porteur.projets.conventions.demandes.store', [$projet, $convention]), [
                'rubrique_id' => $rubrique->id_rubrique,
                'montant' => 500_000,
                'objet' => 'Doublon',
                'justificatif' => UploadedFile::fake()->create('justif.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('convention_id');
    });

    it('refuse un fichier non-PDF', function () {
        Storage::fake('private');

        ['porteur' => $porteur, 'projet' => $projet, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $this->actingAs($porteur)
            ->post(route('porteur.projets.conventions.demandes.store', [$projet, $convention]), [
                'rubrique_id' => $rubrique->id_rubrique,
                'montant' => 500_000,
                'objet' => 'Test',
                'justificatif' => UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg'),
            ])
            ->assertSessionHasErrors('justificatif');
    });
});

// ── Porteur: voir une demande ──────────────────────────────────────────────────

describe('Porteur – voir une demande', function () {
    it('affiche le détail d\'une demande', function () {
        ['porteur' => $porteur, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->create([
            'id_porteur' => $porteur->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($porteur)
            ->get(route('porteur.demandes.show', $demande))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('porteur/Demandes/Show'));
    });

    it('interdit l\'accès à la demande d\'un autre porteur', function () {
        $autrePorteur = Utilisateur::factory()->porteur()->create();
        ['porteur' => $porteur, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->create([
            'id_porteur' => $autrePorteur->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($porteur)
            ->get(route('porteur.demandes.show', $demande))
            ->assertForbidden();
    });
});

// ── Porteur: rapport d'exécution ───────────────────────────────────────────────

describe('Porteur – soumettre un rapport', function () {
    it('soumet un rapport après paiement', function () {
        Storage::fake('private');
        Notification::fake();

        ['porteur' => $porteur, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->payee()->create([
            'id_porteur' => $porteur->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($porteur)
            ->post(route('porteur.demandes.rapport', $demande), [
                'rapport' => UploadedFile::fake()->create('rapport.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::RapportSoumis);
        expect($demande->demande_rapport)->not->toBeNull();
    });

    it('refuse le rapport si la demande n\'est pas payée', function () {
        Storage::fake('private');

        ['porteur' => $porteur, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->soumise()->create([
            'id_porteur' => $porteur->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($porteur)
            ->post(route('porteur.demandes.rapport', $demande), [
                'rapport' => UploadedFile::fake()->create('rapport.pdf', 200, 'application/pdf'),
            ])
            ->assertForbidden();
    });
});

// ── DAF: liste et détail ──────────────────────────────────────────────────────

describe('DAF – liste et détail', function () {
    it('affiche la liste des demandes', function () {
        $daf = Utilisateur::factory()->daf()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        DemandeDepense::factory()->soumise()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($daf)
            ->get(route('daf.demandes.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('daf/Demandes/Index')
                ->has('en_attente', 1)
            );
    });

    it('affiche le détail d\'une demande', function () {
        $daf = Utilisateur::factory()->daf()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->soumise()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($daf)
            ->get(route('daf.demandes.show', $demande))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('daf/Demandes/Show'));
    });
});

// ── DAF: validation ───────────────────────────────────────────────────────────

describe('DAF – valider une demande', function () {
    it('valide une demande soumise', function () {
        Notification::fake();

        $daf = Utilisateur::factory()->daf()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->soumise()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.demandes.valider', $demande))
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::ValideeDaf);
        expect($demande->id_validateur_daf)->toBe($daf->id_utilisateur);
    });

    it('rejette une demande avec un motif', function () {
        Notification::fake();

        $daf = Utilisateur::factory()->daf()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->soumise()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.demandes.rejeter', $demande), ['motif' => 'Justificatif manquant'])
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::RejeteeDaf);
        expect($demande->demande_motif_rejet)->toBe('Justificatif manquant');
    });

    it('refuse le rejet sans motif', function () {
        $daf = Utilisateur::factory()->daf()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->soumise()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.demandes.rejeter', $demande))
            ->assertSessionHasErrors('motif');
    });

    it('refuse la validation d\'une demande déjà validée par DAF', function () {
        $daf = Utilisateur::factory()->daf()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->valideeDaf()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.demandes.valider', $demande))
            ->assertForbidden();
    });
});

// ── DAF: rapport ─────────────────────────────────────────────────────────────

describe('DAF – valider un rapport', function () {
    it('valide un rapport soumis et termine la demande', function () {
        Notification::fake();

        $daf = Utilisateur::factory()->daf()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->rapportSoumis()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($daf)
            ->post(route('daf.demandes.valider-rapport', $demande))
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::Terminee);
    });
});

// ── AC: validation et paiement ────────────────────────────────────────────────

describe('AC – valider une demande', function () {
    it('valide une demande après DAF', function () {
        Notification::fake();

        $ac = Utilisateur::factory()->ac()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->valideeDaf()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($ac)
            ->post(route('ac.demandes.valider', $demande))
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::ValideeAgentComptable);
        expect($demande->id_validateur_ac)->toBe($ac->id_utilisateur);
    });

    it('refuse la validation si la demande n\'est pas au statut validee_daf', function () {
        $ac = Utilisateur::factory()->ac()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->soumise()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($ac)
            ->post(route('ac.demandes.valider', $demande))
            ->assertForbidden();
    });

    it('rejette une demande avec un motif', function () {
        Notification::fake();

        $ac = Utilisateur::factory()->ac()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->valideeDaf()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($ac)
            ->post(route('ac.demandes.rejeter', $demande), ['motif' => 'Budget insuffisant'])
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::RejeteeAgentComptable);
    });
});

describe('AC – enregistrer un paiement', function () {
    it('enregistre un paiement après validation AC', function () {
        Notification::fake();

        $ac = Utilisateur::factory()->ac()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->valideAgentComptable()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
            'demande_montant' => 2_000_000,
        ]);

        $this->actingAs($ac)
            ->post(route('ac.demandes.paiement', $demande), [
                'montant' => 2_000_000,
                'date_paiement' => today()->toDateString(),
                'mode_paiement' => ModePaiement::Virement->value,
                'reference' => 'VIR-2026-001',
            ])
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::Payee);
        $this->assertDatabaseHas('paiements', [
            'id_demande' => $demande->id_demande,
            'paiement_montant' => 2_000_000,
            'paiement_mode' => ModePaiement::Virement->value,
        ]);
    });

    it('refuse le paiement si la demande n\'est pas validée par AC', function () {
        $ac = Utilisateur::factory()->ac()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->valideeDaf()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($ac)
            ->post(route('ac.demandes.paiement', $demande), [
                'montant' => 1_000_000,
                'date_paiement' => today()->toDateString(),
                'mode_paiement' => ModePaiement::Cheque->value,
            ])
            ->assertForbidden();
    });

    it('refuse une date de paiement dans le futur', function () {
        $ac = Utilisateur::factory()->ac()->create();
        ['convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $demande = DemandeDepense::factory()->valideAgentComptable()->create([
            'id_porteur' => Utilisateur::factory()->porteur()->create()->id_utilisateur,
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
        ]);

        $this->actingAs($ac)
            ->post(route('ac.demandes.paiement', $demande), [
                'montant' => 1_000_000,
                'date_paiement' => today()->addDay()->toDateString(),
                'mode_paiement' => ModePaiement::Cheque->value,
            ])
            ->assertSessionHasErrors('date_paiement');
    });
});

// ── AC: paiements directs ─────────────────────────────────────────────────────

describe('AC – paiements directs', function () {
    it('enregistre un paiement direct', function () {
        $ac = Utilisateur::factory()->ac()->create();
        ['projet' => $projet, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $this->actingAs($ac)
            ->post(route('ac.projets.conventions.paiements-directs.store', [$projet, $convention]), [
                'rubrique_id' => $rubrique->id_rubrique,
                'montant' => 1_000_000,
                'objet_depense' => 'Paiement direct',
                'description' => 'Paiement effectué par le bailleur',
                'date_paiement' => today()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('paiements', [
            'type_paiement' => 'direct',
            'id_convention' => $convention->id_convention,
            'id_rubrique' => $rubrique->id_rubrique,
            'paiement_montant' => 1_000_000,
            'id_enregistreur_paiement' => $ac->id_utilisateur,
        ]);
    });

    it('enregistre un paiement direct sans rubrique', function () {
        $ac = Utilisateur::factory()->ac()->create();
        ['projet' => $projet, 'convention' => $convention] = makeConventionWithRubrique();

        $this->actingAs($ac)
            ->post(route('ac.projets.conventions.paiements-directs.store', [$projet, $convention]), [
                'rubrique_id' => null,
                'montant' => 2_000_000,
                'objet_depense' => 'Divers',
                'date_paiement' => today()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('paiements', [
            'type_paiement' => 'direct',
            'id_convention' => $convention->id_convention,
            'id_rubrique' => null,
            'paiement_montant' => 2_000_000,
        ]);
    });

    it('supprime un paiement direct', function () {
        $ac = Utilisateur::factory()->ac()->create();
        ['projet' => $projet, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique();

        $paiementDirect = Paiement::factory()->create([
            'type_paiement' => 'direct',
            'id_convention' => $convention->id_convention,
            'id_projet' => $projet->id_projet,
            'id_rubrique' => $rubrique->id_rubrique,
            'id_enregistreur_paiement' => $ac->id_utilisateur,
            'id_demande' => null,
            'paiement_montant' => 1_000_000,
            'paiement_objet' => 'Test paiement direct',
            'paiement_date' => now(),
        ]);

        $this->actingAs($ac)
            ->delete(route('ac.projets.conventions.paiements-directs.destroy', [$projet, $convention, $paiementDirect]))
            ->assertRedirect();

        $this->assertModelMissing($paiementDirect);
    });

    it('refuse une date de paiement dans le futur', function () {
        $ac = Utilisateur::factory()->ac()->create();
        ['projet' => $projet, 'convention' => $convention] = makeConventionWithRubrique();

        $this->actingAs($ac)
            ->post(route('ac.projets.conventions.paiements-directs.store', [$projet, $convention]), [
                'montant' => 500_000,
                'objet_depense' => 'Test',
                'date_paiement' => today()->addDay()->toDateString(),
            ])
            ->assertSessionHasErrors('date_paiement');
    });
});

// ── Circuit complet ──────────────────────────────────────────────────────────

describe('Circuit complet Porteur → DAF → AC → Paiement → Rapport → Terminée', function () {
    it('parcourt tout le circuit de validation', function () {
        Storage::fake('private');
        Notification::fake();

        $daf = Utilisateur::factory()->daf()->create();
        $ac = Utilisateur::factory()->ac()->create();
        ['porteur' => $porteur, 'projet' => $projet, 'convention' => $convention, 'rubrique' => $rubrique] = makeConventionWithRubrique(10_000_000);

        // 1. Porteur soumet une demande
        $this->actingAs($porteur)
            ->post(route('porteur.projets.conventions.demandes.store', [$projet, $convention]), [
                'rubrique_id' => $rubrique->id_rubrique,
                'montant' => 3_000_000,
                'objet' => 'Mission terrain',
                'justificatif' => UploadedFile::fake()->create('justif.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $demande = DemandeDepense::latest()->first();
        expect($demande->demande_statut)->toBe(StatutDemande::Soumise);

        // 2. DAF valide
        $this->actingAs($daf)
            ->post(route('daf.demandes.valider', $demande))
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::ValideeDaf);

        // 3. AC valide
        $this->actingAs($ac)
            ->post(route('ac.demandes.valider', $demande))
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::ValideeAgentComptable);

        // 4. AC enregistre le paiement
        $this->actingAs($ac)
            ->post(route('ac.demandes.paiement', $demande), [
                'montant' => 3_000_000,
                'date_paiement' => today()->toDateString(),
                'mode_paiement' => ModePaiement::Virement->value,
                'reference' => 'VIR-TEST-001',
            ])
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::Payee);

        // 5. Porteur soumet le rapport
        $this->actingAs($porteur)
            ->post(route('porteur.demandes.rapport', $demande), [
                'rapport' => UploadedFile::fake()->create('rapport.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::RapportSoumis);

        // 6. DAF valide le rapport → terminée
        $this->actingAs($daf)
            ->post(route('daf.demandes.valider-rapport', $demande))
            ->assertRedirect();

        $demande->refresh();
        expect($demande->demande_statut)->toBe(StatutDemande::Terminee);
    });
});

// ── AgentComptablecès refusé pour mauvais rôle ───────────────────────────────────────────

describe('Contrôle d\'accès par rôle', function () {
    it('interdit à un porteur d\'accéder aux routes DAF', function () {
        ['porteur' => $porteur] = makeConventionWithRubrique();

        $this->actingAs($porteur)
            ->get(route('daf.demandes.index'))
            ->assertForbidden();
    });

    it('interdit à un porteur d\'accéder aux routes AC', function () {
        ['porteur' => $porteur] = makeConventionWithRubrique();

        $this->actingAs($porteur)
            ->get(route('ac.demandes.index'))
            ->assertForbidden();
    });

    it('interdit à un DAF d\'accéder aux routes AC', function () {
        $daf = Utilisateur::factory()->daf()->create();

        $this->actingAs($daf)
            ->get(route('ac.demandes.index'))
            ->assertForbidden();
    });
});
