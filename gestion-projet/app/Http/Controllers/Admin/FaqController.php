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
        $items = FaqItem::orderBy('id_faq')->get(['id_faq', 'faq_question', 'faq_reponse', 'faq_actif', 'roles_cibles']);

        return Inertia::render('admin/Faq/Index', [
            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:300'],
            'reponse' => ['required', 'string'],
            'roles_cibles' => ['nullable', 'array'],
            'roles_cibles.*' => ['string', 'in:all,porteur,daf,ac,admin'],
        ]);

        FaqItem::create([
            'faq_question' => $data['question'],
            'faq_reponse' => $data['reponse'],
            'roles_cibles' => $data['roles_cibles'] ?? null,
        ]);

        return back()->with('success', 'Question ajoutée.');
    }

    public function update(Request $request, FaqItem $faqItem): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:300'],
            'reponse' => ['required', 'string'],
            'is_active' => ['boolean'],
            'roles_cibles' => ['nullable', 'array'],
            'roles_cibles.*' => ['string', 'in:all,porteur,daf,ac,admin'],
        ]);

        $faqItem->update([
            'faq_question' => $data['question'],
            'faq_reponse' => $data['reponse'],
            'faq_actif' => $data['is_active'] ?? $faqItem->faq_actif,
            'roles_cibles' => $data['roles_cibles'] ?? null,
        ]);

        return back()->with('success', 'Question mise à jour.');
    }

    public function destroy(FaqItem $faqItem): RedirectResponse
    {
        $faqItem->delete();

        return back()->with('success', 'Question supprimée.');
    }
}
