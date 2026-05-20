<?php

namespace App\Http\Controllers\Daf;

use App\Enums\ConventionStatus;
use App\Enums\VersementType;
use App\Http\Controllers\Controller;
use App\Models\Convention;
use App\Models\Projet;
use App\Models\Rubrique;
use App\Models\Versement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ConventionController extends Controller
{
    public function show(Projet $projet, Convention $convention): Response
    {
        abort_unless($convention->id_projet === $projet->id, 404);

        $convention->load([
            'bailleur:id_bailleur,bailleur_nom,bailleur_sigle,bailleur_type,bailleur_pays',
            'rubriques',
            'versements' => fn ($q) => $q->orderByDesc('versement_date_reception'),
            'paiementsDirects' => fn ($q) => $q->with('rubrique:id_rubrique,rubrique_libelle')->orderByDesc('paiement_direct_date'),
        ]);

        return Inertia::render('daf/Projets/Convention', [
            'projet' => ['id' => $projet->id, 'titre' => $projet->projet_titre],
            'convention' => $this->formatConvention($convention),
        ]);
    }

    public function storeRubrique(Request $request, Projet $projet, Convention $convention): RedirectResponse
    {
        abort_unless($convention->id_projet === $projet->id, 404);

        $validated = $request->validate($this->rubriqueRules());

        $this->assertBudgetOk($projet, $convention, $validated['montant_prevu']);

        $convention->rubriques()->create([
            'rubrique_libelle' => $validated['libelle'],
            'rubrique_montant_prevu' => $validated['montant_prevu'],
            'rubrique_description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Rubrique ajoutée.');
    }

    public function updateRubrique(Request $request, Projet $projet, Convention $convention, Rubrique $rubrique): RedirectResponse
    {
        abort_unless($convention->id_projet === $projet->id, 404);
        abort_unless($rubrique->id_convention === $convention->id, 404);

        $validated = $request->validate($this->rubriqueRules());

        $this->assertBudgetOk($projet, $convention, $validated['montant_prevu'], $rubrique->id);

        $rubrique->update([
            'rubrique_libelle' => $validated['libelle'],
            'rubrique_montant_prevu' => $validated['montant_prevu'],
            'rubrique_description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Rubrique mise à jour.');
    }

    public function terminer(Projet $projet, Convention $convention): RedirectResponse
    {
        abort_unless($convention->id_projet === $projet->id, 404);

        if (! in_array($convention->convention_statut, [ConventionStatus::Active, ConventionStatus::Suspendue])) {
            throw ValidationException::withMessages([
                'status' => 'Seule une convention active ou suspendue peut être terminée.',
            ]);
        }

        $convention->update(['convention_statut' => ConventionStatus::Terminee]);

        return back()->with('success', "La convention « {$convention->convention_titre} » est marquée comme terminée.");
    }

    public function annuler(Projet $projet, Convention $convention): RedirectResponse
    {
        abort_unless($convention->id_projet === $projet->id, 404);

        if (! in_array($convention->convention_statut, [ConventionStatus::Active, ConventionStatus::Suspendue])) {
            throw ValidationException::withMessages([
                'status' => 'Seule une convention active ou suspendue peut être annulée.',
            ]);
        }

        $convention->update(['convention_statut' => ConventionStatus::Annulee]);

        return back()->with('success', "La convention « {$convention->convention_titre} » a été annulée.");
    }

    public function destroyRubrique(Projet $projet, Convention $convention, Rubrique $rubrique): RedirectResponse
    {
        abort_unless($convention->id_projet === $projet->id, 404);
        abort_unless($rubrique->id_convention === $convention->id, 404);

        $rubrique->delete();

        return back()->with('success', 'Rubrique supprimée.');
    }

    public function storeVersement(Request $request, Projet $projet, Convention $convention): RedirectResponse
    {
        abort_unless($convention->id_projet === $projet->id, 404);

        $validated = $request->validate([
            'montant' => ['required', 'integer', 'min:1'],
            'date_reception' => ['required', 'date', 'before_or_equal:today'],
            'type' => ['required', Rule::enum(VersementType::class)],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $convention->versements()->create([
            'versement_montant' => $validated['montant'],
            'versement_date_reception' => $validated['date_reception'],
            'versement_type' => $validated['type'],
            'versement_reference' => $validated['reference'] ?? null,
            'versement_description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Versement enregistré.');
    }

    public function updateVersement(Request $request, Projet $projet, Convention $convention, Versement $versement): RedirectResponse
    {
        abort_unless($convention->id_projet === $projet->id, 404);
        abort_unless($versement->id_convention === $convention->id, 404);

        $validated = $request->validate([
            'montant' => ['required', 'integer', 'min:1'],
            'date_reception' => ['required', 'date', 'before_or_equal:today'],
            'type' => ['required', Rule::enum(VersementType::class)],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $versement->update([
            'versement_montant' => $validated['montant'],
            'versement_date_reception' => $validated['date_reception'],
            'versement_type' => $validated['type'],
            'versement_reference' => $validated['reference'] ?? null,
            'versement_description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Versement mis à jour.');
    }

    public function destroyVersement(Projet $projet, Convention $convention, Versement $versement): RedirectResponse
    {
        abort_unless($convention->id_projet === $projet->id, 404);
        abort_unless($versement->id_convention === $convention->id, 404);

        $versement->delete();

        return back()->with('success', 'Versement supprimé.');
    }

    private function rubriqueRules(): array
    {
        return [
            'libelle' => ['required', 'string', 'max:255'],
            'montant_prevu' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Vérifie que le montant d'une rubrique ne dépasse pas le budget autorisé.
     *
     * Si le total des conventions du projet dépasse le budget initial estimé,
     * la contrainte rubrique ≤ convention est levée (financement supplémentaire
     * mobilisé au-delà du budget initial).
     */
    private function assertBudgetOk(Projet $projet, Convention $convention, int $montant, ?int $excludeRubriqueId = null): void
    {
        $totalConventions = $projet->conventions()->sum(\DB::raw('convention_montant * convention_taux_conversion'));
        $financement_depasse_budget_initial = $totalConventions > $projet->projet_montant_estime;

        if ($financement_depasse_budget_initial) {
            return;
        }

        $total = $convention->rubriques()
            ->when($excludeRubriqueId, fn ($q) => $q->where('id_rubrique', '!=', $excludeRubriqueId))
            ->sum('rubrique_montant_prevu') + $montant;

        if ($total > $convention->montant_fcfa) {
            throw ValidationException::withMessages([
                'montant_prevu' => sprintf(
                    'Le total des rubriques (%s FCFA) dépasserait le montant de la convention (%s FCFA). Pour lever cette contrainte, le total des conventions doit dépasser le budget initial du projet (%s FCFA).',
                    number_format($total, 0, ',', ' '),
                    number_format($convention->montant_fcfa, 0, ',', ' '),
                    number_format($projet->projet_montant_estime, 0, ',', ' ')
                ),
            ]);
        }
    }

    private function formatConvention(Convention $convention): array
    {
        return [
            'id' => $convention->id,
            'titre' => $convention->convention_titre,
            'description' => $convention->convention_description,
            'montant' => $convention->convention_montant,
            'montant_fcfa' => $convention->montant_fcfa,
            'devise_origine' => $convention->convention_devise,
            'taux_conversion' => $convention->convention_taux_conversion,
            'forme' => $convention->convention_forme->value,
            'forme_label' => $convention->convention_forme->label(),
            'status' => $convention->convention_statut->value,
            'status_label' => $convention->convention_statut->label(),
            'date_signature' => $convention->convention_date_signature?->toDateString(),
            'date_debut' => $convention->convention_date_debut?->toDateString(),
            'date_fin' => $convention->convention_date_fin?->toDateString(),
            'bailleur' => [
                'nom' => $convention->bailleur->bailleur_nom,
                'sigle' => $convention->bailleur->bailleur_sigle,
                'type' => $convention->bailleur->bailleur_type,
                'pays' => $convention->bailleur->bailleur_pays,
            ],
            'total_rubriques' => $convention->rubriques->sum('rubrique_montant_prevu'),
            'total_versements' => $convention->versements->sum('versement_montant'),
            'rubriques' => $convention->rubriques->map(fn ($r) => [
                'id' => $r->id,
                'libelle' => $r->rubrique_libelle,
                'montant_prevu' => $r->rubrique_montant_prevu,
                'montant_depense' => 0,
                'description' => $r->rubrique_description,
            ])->values(),
            'versements' => $convention->versements->map(fn ($v) => [
                'id' => $v->id,
                'montant' => $v->versement_montant,
                'date_reception' => $v->versement_date_reception->toDateString(),
                'type' => $v->versement_type->value,
                'type_label' => $v->versement_type->label(),
                'reference' => $v->versement_reference,
                'description' => $v->versement_description,
            ])->values(),
            'paiements_directs' => $convention->paiementsDirects->map(fn ($p) => [
                'id' => $p->id,
                'montant' => $p->paiement_direct_montant,
                'objet_depense' => $p->paiement_direct_objet,
                'date_paiement' => $p->paiement_direct_date->toDateString(),
                'rubrique' => $p->rubrique ? ['libelle' => $p->rubrique->rubrique_libelle] : null,
            ])->values(),
        ];
    }
}
