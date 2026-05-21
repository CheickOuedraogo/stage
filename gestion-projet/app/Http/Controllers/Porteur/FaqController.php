<?php

namespace App\Http\Controllers\Porteur;

use App\Enums\RoleUtilisateur;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        $items = Faq::active()
            ->get()
            ->filter(fn (Faq $f) => $f->isVisibleFor(RoleUtilisateur::Porteur))
            ->map(fn (Faq $f) => [
                'id' => $f->id_utilisateur_faq,
                'question' => $f->faq_question,
                'reponse' => $f->faq_reponse,
            ])
            ->values();

        return Inertia::render('porteur/Faq', [
            'items' => $items,
        ]);
    }
}
