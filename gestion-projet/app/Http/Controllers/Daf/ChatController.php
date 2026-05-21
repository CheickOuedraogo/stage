<?php

namespace App\Http\Controllers\Daf;

use App\Enums\RoleUtilisateur;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\MessageChat;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessageChatControleur extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $admin = Utilisateur::parRole(RoleUtilisateur::Administrateur)->first();

        $messages = $admin
            ? MessageChat::where(fn ($q) => $q
                ->where('id_expediteur', $user->id_utilisateur_utilisateur)->where('id_destinataire', $admin->id_utilisateur_utilisateur)
                ->orWhere('id_expediteur', $admin->id_utilisateur_utilisateur)->where('id_destinataire', $user->id_utilisateur_utilisateur)
            )
                ->latest()
                ->limit(50)
                ->get()
                ->reverse()
                ->values()
                ->map(fn (MessageChat $m) => [
                    'id' => $m->id_utilisateur_message,
                    'message' => $m->message_contenu,
                    'is_mine' => $m->id_utilisateur_expediteur === $user->id_utilisateur_utilisateur,
                    'sender_name' => $m->id_utilisateur_expediteur === $user->id_utilisateur_utilisateur ? 'Moi' : 'Administrateur',
                    'created_at' => $m->created_at->toIso8601String(),
                ])
            : collect();

        // Mark admin's messages as read
        if ($admin) {
            MessageChat::where('id_expediteur', $admin->id_utilisateur_utilisateur)
                ->where('id_destinataire', $user->id_utilisateur_utilisateur)
                ->where('message_lu', false)
                ->update(['message_lu' => true]);
        }

        $faqItems = Faq::active()
            ->get()
            ->filter(fn (Faq $f) => $f->isVisibleFor(RoleUtilisateur::Daf))
            ->map(fn (Faq $f) => ['id' => $f->id_utilisateur_faq, 'question' => $f->faq_question, 'reponse' => $f->faq_reponse])
            ->values();

        return Inertia::render('daf/Chat', [
            'messages' => $messages,
            'admin_online' => false,
            'faq_items' => $faqItems,
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate(['message' => ['required', 'string', 'max:2000']]);

        $admin = Utilisateur::parRole(RoleUtilisateur::Administrateur)->firstOrFail();

        MessageChat::create([
            'id_expediteur' => $request->user()->id_utilisateur_utilisateur,
            'id_destinataire' => $admin->id_utilisateur_utilisateur,
            'message_contenu' => $request->message,
        ]);

        return back();
    }

    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();
        $admin = Utilisateur::parRole(RoleUtilisateur::Administrateur)->first();
        $since = $request->query('since', 0);

        $messages = $admin
            ? MessageChat::where('id_expediteur', $admin->id_utilisateur_utilisateur)
                ->where('id_destinataire', $user->id_utilisateur_utilisateur)
                ->where('id_message', '>', $since)
                ->get()
                ->map(fn (MessageChat $m) => [
                    'id' => $m->id_utilisateur_message,
                    'message' => $m->message_contenu,
                    'is_mine' => false,
                    'sender_name' => 'Administrateur',
                    'created_at' => $m->created_at->toIso8601String(),
                ])
            : collect();

        // Mark new messages as read
        if ($admin && $messages->isNotEmpty()) {
            MessageChat::where('id_expediteur', $admin->id_utilisateur_utilisateur)
                ->where('id_destinataire', $user->id_utilisateur_utilisateur)
                ->where('id_message', '>', $since)
                ->update(['message_lu' => true]);
        }

        return response()->json(['messages' => $messages]);
    }
}
