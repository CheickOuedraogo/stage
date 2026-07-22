<?php

namespace App\Http\Controllers\AgentComptable;

use App\Enums\StatutDemande;
use App\Enums\StatutProjet;
use App\Http\Controllers\Controller;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\Rubrique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaiementDirectController extends Controller
{
    public function store(Request $request, Projet $projet, Convention $convention): RedirectResponse
    {
        abort_unless($convention->id_projet === $projet->id_projet, 404);
        abort_unless($projet->projet_statut === StatutProjet::EnCours, 403, 'Impossible d\'enregistrer un paiement direct : le projet doit être en cours.');

        $validated = $request->validate([
            'rubrique_id' => ['nullable', 'integer', Rule::exists('rubriques', 'id_rubrique')->where('id_convention', $convention->id_convention)],
            'montant' => ['required', 'integer', 'min:1'],
            'objet_depense' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'date_paiement' => ['required', 'date', 'before_or_equal:today'],
        ]);

        if ($validated['rubrique_id'] ?? null) {
            $rubrique = Rubrique::findOrFail($validated['rubrique_id']);
            $consommeDemandes = $rubrique->demandesDepenses()
                ->whereIn('demande_statut', [
                    StatutDemande::Soumise->value,
                    StatutDemande::ValideeDaf->value,
                    StatutDemande::ValideeAgentComptable->value,
                    StatutDemande::Payee->value,
                    StatutDemande::RapportSoumis->value,
                    StatutDemande::Terminee->value,
                ])
                ->sum('demande_montant');
            $consommeDirects = $rubrique->paiementsDirects()->sum('paiement_montant');
            $solde = $rubrique->rubrique_montant - $consommeDemandes - $consommeDirects;

            if ($validated['montant'] > $solde) {
                throw ValidationException::withMessages([
                    'montant' => "Le montant du paiement direct ({$validated['montant']} FCFA) dépasse le solde disponible de la rubrique ({$solde} FCFA).",
                ]);
            }
        } else {
            $consommePaiements = Paiement::where('id_convention', $convention->id_convention)
                ->sum('paiement_montant');
            $consommeDemandes = DemandeDepense::where('id_convention', $convention->id_convention)
                ->whereIn('demande_statut', [
                    StatutDemande::Soumise->value,
                    StatutDemande::ValideeDaf->value,
                    StatutDemande::ValideeAgentComptable->value,
                ])
                ->sum('demande_montant');
            $solde = $convention->montant_fcfa - $consommePaiements - $consommeDemandes;

            if ($validated['montant'] > $solde) {
                throw ValidationException::withMessages([
                    'montant' => "Le montant du paiement direct ({$validated['montant']} FCFA) dépasse le montant disponible de la convention ({$solde} FCFA).",
                ]);
            }
        }

        Paiement::create([
            'type_paiement' => 'direct',
            'id_convention' => $convention->id_convention,
            'id_projet' => $projet->id_projet,
            'id_rubrique' => $validated['rubrique_id'] ?? null,
            'paiement_montant' => $validated['montant'],
            'paiement_objet' => $validated['objet_depense'],
            'paiement_description' => $validated['description'] ?? null,
            'paiement_date' => $validated['date_paiement'],
            'id_enregistreur_paiement' => $request->user()->id_utilisateur,
        ]);

        return back()->with('success', 'Paiement direct enregistré avec succès.');
    }

    public function destroy(Projet $projet, Convention $convention, Paiement $paiementDirect): RedirectResponse
    {
        abort_unless($convention->id_projet === $projet->id_projet, 404);
        abort_unless($paiementDirect->id_convention === $convention->id_convention, 404);
        abort_unless($projet->projet_statut === StatutProjet::EnCours, 403, 'Impossible de supprimer un paiement direct : le projet doit être en cours.');

        $paiementDirect->delete();

        return back()->with('success', 'Paiement direct supprimé.');
    }
}
