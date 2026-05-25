<?php

namespace App\Http\Controllers\Daf;

use App\Http\Controllers\Controller;
use App\Models\Convention;
use App\Models\Paiement;
use App\Models\Projet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaiementDirectController extends Controller
{
    public function store(Request $request, Projet $projet, Convention $convention): RedirectResponse
    {
        abort_unless($convention->id_projet === $projet->id_projet, 404);

        $validated = $request->validate([
            'rubrique_id' => ['nullable', 'integer', Rule::exists('rubriques', 'id_rubrique')->where('id_convention', $convention->id_convention)],
            'montant' => ['required', 'integer', 'min:1'],
            'objet_depense' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'date_paiement' => ['required', 'date', 'before_or_equal:today'],
        ]);

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

        $paiementDirect->delete();

        return back()->with('success', 'Paiement direct supprimé.');
    }
}
