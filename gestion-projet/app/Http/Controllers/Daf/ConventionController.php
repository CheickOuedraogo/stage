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
        abort_unless($convention->projet_id === $projet->id, 404);

        $convention->load([
            'bailleur:id,nom,sigle,type,pays',
            'rubriques',
            'versements' => fn ($q) => $q->orderByDesc('date_reception'),
            'paiementsDirects' => fn ($q) => $q->with('rubrique:id,libelle')->orderByDesc('date_paiement'),
        ]);

        return Inertia::render('daf/Projets/Convention', [
            'projet' => ['id' => $projet->id, 'titre' => $projet->titre],
            'convention' => $this->formatConvention($convention),
        ]);
    }

    public function storeRubrique(Request $request, Projet $projet, Convention $convention): RedirectResponse
    {
        abort_unless($convention->projet_id === $projet->id, 404);

        $validated = $request->validate($this->rubriqueRules());

        $this->assertBudgetOk($projet, $convention, $validated['montant_prevu']);

        $convention->rubriques()->create($validated);

        return back()->with('success', 'Rubrique ajoutée.');
    }

    public function updateRubrique(Request $request, Projet $projet, Convention $convention, Rubrique $rubrique): RedirectResponse
    {
        abort_unless($convention->projet_id === $projet->id, 404);
        abort_unless($rubrique->convention_id === $convention->id, 404);

        $validated = $request->validate($this->rubriqueRules());

        $this->assertBudgetOk($projet, $convention, $validated['montant_prevu'], $rubrique->id);

        $rubrique->update($validated);

        return back()->with('success', 'Rubrique mise à jour.');
    }

    public function terminer(Projet $projet, Convention $convention): RedirectResponse
    {
        abort_unless($convention->projet_id === $projet->id, 404);

        if (! in_array($convention->status, [ConventionStatus::Active, ConventionStatus::Suspendue])) {
            throw ValidationException::withMessages([
                'status' => 'Seule une convention active ou suspendue peut être terminée.',
            ]);
        }

        $convention->update(['status' => ConventionStatus::Terminee]);

        return back()->with('success', "La convention « {$convention->titre} » est marquée comme terminée.");
    }

    public function annuler(Projet $projet, Convention $convention): RedirectResponse
    {
        abort_unless($convention->projet_id === $projet->id, 404);

        if (! in_array($convention->status, [ConventionStatus::Active, ConventionStatus::Suspendue])) {
            throw ValidationException::withMessages([
                'status' => 'Seule une convention active ou suspendue peut être annulée.',
            ]);
        }

        $convention->update(['status' => ConventionStatus::Annulee]);

        return back()->with('success', "La convention « {$convention->titre} » a été annulée.");
    }

    public function destroyRubrique(Projet $projet, Convention $convention, Rubrique $rubrique): RedirectResponse
    {
        abort_unless($convention->projet_id === $projet->id, 404);
        abort_unless($rubrique->convention_id === $convention->id, 404);

        $rubrique->delete();

        return back()->with('success', 'Rubrique supprimée.');
    }

    public function storeVersement(Request $request, Projet $projet, Convention $convention): RedirectResponse
    {
        abort_unless($convention->projet_id === $projet->id, 404);

        $validated = $request->validate([
            'montant' => ['required', 'integer', 'min:1'],
            'date_reception' => ['required', 'date', 'before_or_equal:today'],
            'type' => ['required', Rule::enum(VersementType::class)],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $convention->versements()->create($validated);

        return back()->with('success', 'Versement enregistré.');
    }

    public function updateVersement(Request $request, Projet $projet, Convention $convention, Versement $versement): RedirectResponse
    {
        abort_unless($convention->projet_id === $projet->id, 404);
        abort_unless($versement->convention_id === $convention->id, 404);

        $validated = $request->validate([
            'montant' => ['required', 'integer', 'min:1'],
            'date_reception' => ['required', 'date', 'before_or_equal:today'],
            'type' => ['required', Rule::enum(VersementType::class)],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $versement->update($validated);

        return back()->with('success', 'Versement mis à jour.');
    }

    public function destroyVersement(Projet $projet, Convention $convention, Versement $versement): RedirectResponse
    {
        abort_unless($convention->projet_id === $projet->id, 404);
        abort_unless($versement->convention_id === $convention->id, 404);

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
        $totalConventions = $projet->conventions()->sum('montant_fcfa');
        $financement_depasse_budget_initial = $totalConventions > $projet->montant_estime;

        if ($financement_depasse_budget_initial) {
            return;
        }

        $total = $convention->rubriques()
            ->when($excludeRubriqueId, fn ($q) => $q->where('id', '!=', $excludeRubriqueId))
            ->sum('montant_prevu') + $montant;

        if ($total > $convention->montant_fcfa) {
            throw ValidationException::withMessages([
                'montant_prevu' => sprintf(
                    'Le total des rubriques (%s FCFA) dépasserait le montant de la convention (%s FCFA). Pour lever cette contrainte, le total des conventions doit dépasser le budget initial du projet (%s FCFA).',
                    number_format($total, 0, ',', ' '),
                    number_format($convention->montant_fcfa, 0, ',', ' '),
                    number_format($projet->montant_estime, 0, ',', ' ')
                ),
            ]);
        }
    }

    private function formatConvention(Convention $convention): array
    {
        return [
            'id' => $convention->id,
            'titre' => $convention->titre,
            'description' => $convention->description,
            'montant' => $convention->montant,
            'montant_fcfa' => $convention->montant_fcfa,
            'devise_origine' => $convention->devise_origine,
            'taux_conversion' => $convention->taux_conversion,
            'forme' => $convention->forme->value,
            'forme_label' => $convention->forme->label(),
            'status' => $convention->status->value,
            'status_label' => $convention->status->label(),
            'date_signature' => $convention->date_signature?->toDateString(),
            'date_debut' => $convention->date_debut?->toDateString(),
            'date_fin' => $convention->date_fin?->toDateString(),
            'bailleur' => [
                'nom' => $convention->bailleur->nom,
                'sigle' => $convention->bailleur->sigle,
                'type' => $convention->bailleur->type,
                'pays' => $convention->bailleur->pays,
            ],
            'total_rubriques' => $convention->rubriques->sum('montant_prevu'),
            'total_versements' => $convention->versements->sum('montant'),
            'rubriques' => $convention->rubriques->map(fn ($r) => [
                'id' => $r->id,
                'libelle' => $r->libelle,
                'montant_prevu' => $r->montant_prevu,
                'montant_depense' => 0,
                'description' => $r->description,
            ])->values(),
            'versements' => $convention->versements->map(fn ($v) => [
                'id' => $v->id,
                'montant' => $v->montant,
                'date_reception' => $v->date_reception->toDateString(),
                'type' => $v->type->value,
                'type_label' => $v->type->label(),
                'reference' => $v->reference,
                'description' => $v->description,
            ])->values(),
            'paiements_directs' => $convention->paiementsDirects->map(fn ($p) => [
                'id' => $p->id,
                'montant' => $p->montant,
                'objet_depense' => $p->objet_depense,
                'date_paiement' => $p->date_paiement->toDateString(),
                'rubrique' => $p->rubrique ? ['libelle' => $p->rubrique->libelle] : null,
            ])->values(),
        ];
    }
}
