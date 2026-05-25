<?php

namespace App\Http\Controllers\Porteur;

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

        $contacts = Utilisateur::whereIn('role_key', [
            RoleUtilisateur::Administrateur,
            RoleUtilisateur::Daf,
            RoleUtilisateur::AgentComptable,
        ])
            ->get()
            ->map(function (Utilisateur $u) use ($user) {
                $lastMsg = MessageChat::where(fn ($q) => $q
                    ->where('id_expediteur', $user->id_utilisateur)->where('id_destinataire', $u->id_utilisateur)
                    ->orWhere('id_expediteur', $u->id_utilisateur)->where('id_destinataire', $user->id_utilisateur)
                )
                    ->latest()
                    ->first();

                $unread = MessageChat::where('id_expediteur', $u->id_utilisateur)
                    ->where('id_destinataire', $user->id_utilisateur)
                    ->where('message_lu', false)
                    ->count();

                return [
                    'user_id' => $u->id_utilisateur,
                    'utilisateur_nom' => $u->utilisateur_nom,
                    'utilisateur_email' => $u->utilisateur_email,
                    'role' => $u->role_key->label(),
                    'role_key' => $u->role_key->value,
                    'unread' => $unread,
                    'last_message' => $lastMsg?->message_contenu,
                    'last_at' => $lastMsg?->cree_le?->diffForHumans(),
                ];
            })
            ->values();

        $faqItems = Faq::active()
            ->get()
            ->filter(fn (Faq $f) => $f->isVisibleFor(RoleUtilisateur::Porteur))
            ->map(fn (Faq $f) => ['id' => $f->id_faq, 'question' => $f->faq_question, 'reponse' => $f->faq_reponse])
            ->values();

        return Inertia::render('porteur/Chat', [
            'contacts' => $contacts,
            'faq_items' => $faqItems,
        ]);
    }

    public function show(Request $request, Utilisateur $user): Response
    {
        $currentUser = $request->user();

        $messages = MessageChat::where(fn ($q) => $q
            ->where('id_expediteur', $currentUser->id_utilisateur)->where('id_destinataire', $user->id_utilisateur)
            ->orWhere('id_expediteur', $user->id_utilisateur)->where('id_destinataire', $currentUser->id_utilisateur)
        )
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (MessageChat $m) => [
                'id' => $m->id_message,
                'message' => $m->message_contenu,
                'is_mine' => $m->id_expediteur === $currentUser->id_utilisateur,
                'sender_name' => $m->id_expediteur === $currentUser->id_utilisateur ? 'Moi' : $user->utilisateur_nom,
                'created_at' => $m->cree_le->toIso8601String(),
            ]);

        MessageChat::where('id_expediteur', $user->id_utilisateur)
            ->where('id_destinataire', $currentUser->id_utilisateur)
            ->where('message_lu', false)
            ->update(['message_lu' => true]);

        return Inertia::render('porteur/ChatConversation', [
            'contact' => [
                'id' => $user->id_utilisateur,
                'utilisateur_nom' => $user->utilisateur_nom,
                'utilisateur_email' => $user->utilisateur_email,
                'role' => $user->role_key->label(),
            ],
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, Utilisateur $user): RedirectResponse
    {
        $request->validate(['message' => ['required', 'string', 'max:2000']]);

        MessageChat::create([
            'id_expediteur' => $request->user()->id_utilisateur,
            'id_destinataire' => $user->id_utilisateur,
            'message_contenu' => $request->message,
        ]);

        return back();
    }

    public function poll(Request $request, Utilisateur $user): JsonResponse
    {
        $currentUser = $request->user();
        $since = (int) $request->query('since', 0);

        $messages = MessageChat::where('id_expediteur', $user->id_utilisateur)
            ->where('id_destinataire', $currentUser->id_utilisateur)
            ->where('id_message', '>', $since)
            ->get()
            ->map(fn (MessageChat $m) => [
                'id' => $m->id_message,
                'message' => $m->message_contenu,
                'is_mine' => false,
                'sender_name' => $user->utilisateur_nom,
                'created_at' => $m->cree_le->toIso8601String(),
            ]);

        if ($messages->isNotEmpty()) {
            MessageChat::where('id_expediteur', $user->id_utilisateur)
                ->where('id_destinataire', $currentUser->id_utilisateur)
                ->where('id_message', '>', $since)
                ->update(['message_lu' => true]);
        }

        return response()->json(['messages' => $messages]);
    }
}
