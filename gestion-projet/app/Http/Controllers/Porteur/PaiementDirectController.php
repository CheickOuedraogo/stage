<?php

namespace App\Http\Controllers\Porteur;

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
        $porteur = $request->user();
        abort_unless($projet->porteur_id === $porteur->id, 403);
        abort_unless($convention->projet_id === $projet->id, 404);

        $validated = $request->validate([
            'rubrique_id' => ['nullable', 'integer', Rule::exists('rubriques', 'id')->where('convention_id', $convention->id)],
            'montant' => ['required', 'integer', 'min:1'],
            'objet_depense' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'date_paiement' => ['required', 'date', 'before_or_equal:today'],
        ]);

        PaiementDirect::create([
            ...$validated,
            'convention_id' => $convention->id,
            'enregistre_par' => $porteur->id,
        ]);

        return back()->with('success', 'Paiement direct enregistré avec succès.');
    }

    public function destroy(Projet $projet, Convention $convention, PaiementDirect $paiementDirect): RedirectResponse
    {
        abort_unless($convention->projet_id === $projet->id, 404);
        abort_unless($paiementDirect->convention_id === $convention->id, 404);
        abort_unless($paiementDirect->enregistre_par === request()->user()->id, 403);

        $paiementDirect->delete();

        return back()->with('success', 'Paiement direct supprimé.');
    }
}
