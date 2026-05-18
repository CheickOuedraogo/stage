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
        $items = FaqItem::orderBy('ordre')->get(['id', 'question', 'reponse', 'ordre', 'is_active', 'roles_cibles']);

        return Inertia::render('admin/Faq/Index', [
            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:300'],
            'reponse' => ['required', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'roles_cibles' => ['nullable', 'array'],
            'roles_cibles.*' => ['string', 'in:all,porteur,daf,ac,admin'],
        ]);

        $data['ordre'] ??= FaqItem::max('ordre') + 1;

        FaqItem::create($data);

        return back()->with('success', 'Question ajoutée.');
    }

    public function update(Request $request, FaqItem $faqItem): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:300'],
            'reponse' => ['required', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'roles_cibles' => ['nullable', 'array'],
            'roles_cibles.*' => ['string', 'in:all,porteur,daf,ac,admin'],
        ]);

        $faqItem->update($data);

        return back()->with('success', 'Question mise à jour.');
    }

    public function destroy(FaqItem $faqItem): RedirectResponse
    {
        $faqItem->delete();

        return back()->with('success', 'Question supprimée.');
    }
}
