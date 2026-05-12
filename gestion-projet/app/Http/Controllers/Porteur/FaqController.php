<?php

namespace App\Http\Controllers\Porteur;

use App\Http\Controllers\Controller;
use App\Models\FaqItem;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        $items = FaqItem::active()
            ->get(['id', 'question', 'reponse', 'ordre'])
            ->map(fn (FaqItem $f) => [
                'id' => $f->id,
                'question' => $f->question,
                'reponse' => $f->reponse,
            ]);

        return Inertia::render('porteur/Faq', [
            'items' => $items,
        ]);
    }
}
