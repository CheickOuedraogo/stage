<?php

namespace App\Http\Controllers\Porteur;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        $items = FaqItem::active()
            ->get()
            ->filter(fn (FaqItem $f) => $f->isVisibleFor(UserRole::Porteur))
            ->map(fn (FaqItem $f) => [
                'id' => $f->id,
                'question' => $f->faq_question,
                'reponse' => $f->faq_reponse,
            ])
            ->values();

        return Inertia::render('porteur/Faq', [
            'items' => $items,
        ]);
    }
}
