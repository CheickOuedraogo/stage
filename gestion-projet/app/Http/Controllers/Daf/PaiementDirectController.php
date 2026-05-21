<?php

namespace App\Http\Controllers\Daf;

use App\Http\Controllers\Controller;
use App\Models\Convention;
use App\Models\PaiementDirect;
use App\Models\Projet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaiementDirectController extends Controller
{
    public function store(Request $request, Projet $projet, Convention $convention): RedirectResponse
    {
        abort_unless($convention->id_utilisateur_projet === $projet->id_utilisateur, 404);

        $validated = $request->validate([
            'rubrique_id' => ['nullable', 'integer', Rule::exists('rubriques', 'id_rubrique')->where('id_convention', $convention->id_utilisateur)],
            'montant' => ['required', 'integer', 'min:1'],
            'objet_depense' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'date_paiement' => ['required', 'date', 'before_or_equal:today'],
        ]);

        PaiementDirect::create([
            'id_convention' => $convention->id_utilisateur,
            'id_rubrique' => $validated['rubrique_id'] ?? null,
            'paiement_direct_montant' => $validated['montant'],
            'paiement_direct_objet' => $validated['objet_depense'],
            'paiement_direct_description' => $validated['description'] ?? null,
            'paiement_direct_date' => $validated['date_paiement'],
            'id_enregistreur_paiement_direct' => $request->user()->id_utilisateur,
        ]);

        return back()->with('success', 'Paiement direct enregistré avec succès.');
    }

    public function destroy(Projet $projet, Convention $convention, PaiementDirect $paiementDirect): RedirectResponse
    {
        abort_unless($convention->id_utilisateur_projet === $projet->id_utilisateur, 404);
        abort_unless($paiementDirect->id_utilisateur_convention === $convention->id_utilisateur, 404);

        $paiementDirect->delete();

        return back()->with('success', 'Paiement direct supprimé.');
    }
}
