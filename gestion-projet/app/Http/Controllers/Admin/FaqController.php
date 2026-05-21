<?php

namespace App\Http\Controllers\Administrateur;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        $items = Faq::orderBy('id_faq')->get(['id_faq', 'faq_question', 'faq_reponse', 'faq_actif', 'visible_porteur', 'visible_daf', 'visible_ac']);

        return Inertia::render('admin/Faq/Index', [
            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'reponse' => ['required', 'string'],
            'visible_porteur' => ['boolean'],
            'visible_daf' => ['boolean'],
            'visible_ac' => ['boolean'],
        ]);

        Faq::create([
            'faq_question' => $data['question'],
            'faq_reponse' => $data['reponse'],
            'visible_porteur' => $data['visible_porteur'] ?? true,
            'visible_daf' => $data['visible_daf'] ?? true,
            'visible_ac' => $data['visible_ac'] ?? true,
        ]);

        return back()->with('success', 'Question ajoutée.');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'reponse' => ['required', 'string'],
            'is_active' => ['boolean'],
            'visible_porteur' => ['boolean'],
            'visible_daf' => ['boolean'],
            'visible_ac' => ['boolean'],
        ]);

        $faq->update([
            'faq_question' => $data['question'],
            'faq_reponse' => $data['reponse'],
            'faq_actif' => $data['is_active'] ?? $faq->faq_actif,
            'visible_porteur' => $data['visible_porteur'] ?? $faq->visible_porteur,
            'visible_daf' => $data['visible_daf'] ?? $faq->visible_daf,
            'visible_ac' => $data['visible_ac'] ?? $faq->visible_ac,
        ]);

        return back()->with('success', 'Question mise à jour.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('success', 'Question supprimée.');
    }
}
