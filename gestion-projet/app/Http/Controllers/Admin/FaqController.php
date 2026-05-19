<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        $items = FaqItem::orderBy('id_faq')->get(['id_faq', 'faq_question', 'faq_reponse', 'faq_actif', 'visible_porteur', 'visible_daf', 'visible_ac']);

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

        FaqItem::create([
            'faq_question' => $data['question'],
            'faq_reponse' => $data['reponse'],
            'visible_porteur' => $data['visible_porteur'] ?? true,
            'visible_daf' => $data['visible_daf'] ?? true,
            'visible_ac' => $data['visible_ac'] ?? true,
        ]);

        return back()->with('success', 'Question ajoutée.');
    }

    public function update(Request $request, FaqItem $faqItem): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'reponse' => ['required', 'string'],
            'is_active' => ['boolean'],
            'visible_porteur' => ['boolean'],
            'visible_daf' => ['boolean'],
            'visible_ac' => ['boolean'],
        ]);

        $faqItem->update([
            'faq_question' => $data['question'],
            'faq_reponse' => $data['reponse'],
            'faq_actif' => $data['is_active'] ?? $faqItem->faq_actif,
            'visible_porteur' => $data['visible_porteur'] ?? $faqItem->visible_porteur,
            'visible_daf' => $data['visible_daf'] ?? $faqItem->visible_daf,
            'visible_ac' => $data['visible_ac'] ?? $faqItem->visible_ac,
        ]);

        return back()->with('success', 'Question mise à jour.');
    }

    public function destroy(FaqItem $faqItem): RedirectResponse
    {
        $faqItem->delete();

        return back()->with('success', 'Question supprimée.');
    }
}
