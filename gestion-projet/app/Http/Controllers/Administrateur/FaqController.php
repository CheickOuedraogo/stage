<?php

namespace App\Http\Controllers\Administrateur;

use App\Enums\RoleUtilisateur;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        $items = Faq::orderBy('id_faq')->get(['id_faq', 'faq_question', 'faq_reponse', 'role_key']);

        return Inertia::render('admin/Faq/Index', [
            'items' => $items,
            'roles' => collect(RoleUtilisateur::cases())->map(fn ($r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'reponse' => ['required', 'string'],
            'role_key' => ['required', Rule::enum(RoleUtilisateur::class)],
        ]);

        Faq::create([
            'faq_question' => $data['question'],
            'faq_reponse' => $data['reponse'],
            'role_key' => $data['role_key'],
        ]);

        return back()->with('success', 'Question ajoutée.');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'reponse' => ['required', 'string'],
            'role_key' => ['nullable', Rule::enum(RoleUtilisateur::class)],
        ]);

        $faq->update([
            'faq_question' => $data['question'],
            'faq_reponse' => $data['reponse'],
            'role_key' => $data['role_key'] ?? null,
        ]);

        return back()->with('success', 'Question mise à jour.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('success', 'Question supprimée.');
    }
}
