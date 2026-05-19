<?php

namespace App\Http\Controllers\Ac;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\FaqItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $admin = User::byRole(UserRole::Admin)->first();

        $messages = $admin
            ? ChatMessage::where(fn ($q) => $q
                ->where('id_expediteur', $user->id)->where('id_destinataire', $admin->id)
                ->orWhere('id_expediteur', $admin->id)->where('id_destinataire', $user->id)
            )
                ->latest()
                ->limit(50)
                ->get()
                ->reverse()
                ->values()
                ->map(fn (ChatMessage $m) => [
                    'id' => $m->id,
                    'message' => $m->message_contenu,
                    'is_mine' => $m->id_expediteur === $user->id,
                    'sender_name' => $m->id_expediteur === $user->id ? 'Moi' : 'Admin',
                    'created_at' => $m->created_at->toIso8601String(),
                ])
            : collect();

        if ($admin) {
            ChatMessage::where('id_expediteur', $admin->id)
                ->where('id_destinataire', $user->id)
                ->where('message_lu', false)
                ->update(['message_lu' => true]);
        }

        $faqItems = FaqItem::active()
            ->get()
            ->filter(fn (FaqItem $f) => $f->isVisibleFor(UserRole::Ac))
            ->map(fn (FaqItem $f) => ['id' => $f->id, 'question' => $f->faq_question, 'reponse' => $f->faq_reponse])
            ->values();

        return Inertia::render('ac/Chat', [
            'messages' => $messages,
            'admin_online' => false,
            'faq_items' => $faqItems,
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate(['message' => ['required', 'string', 'max:2000']]);

        $admin = User::byRole(UserRole::Admin)->firstOrFail();

        ChatMessage::create([
            'id_expediteur' => $request->user()->id,
            'id_destinataire' => $admin->id,
            'message_contenu' => $request->message,
        ]);

        return back();
    }

    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();
        $admin = User::byRole(UserRole::Admin)->first();
        $since = (int) $request->query('since', 0);

        $messages = $admin
            ? ChatMessage::where('id_expediteur', $admin->id)
                ->where('id_destinataire', $user->id)
                ->where('id', '>', $since)
                ->get()
                ->map(fn (ChatMessage $m) => [
                    'id' => $m->id,
                    'message' => $m->message_contenu,
                    'is_mine' => false,
                    'sender_name' => 'Admin',
                    'created_at' => $m->created_at->toIso8601String(),
                ])
            : collect();

        if ($admin && $messages->isNotEmpty()) {
            ChatMessage::where('id_expediteur', $admin->id)
                ->where('id_destinataire', $user->id)
                ->where('id', '>', $since)
                ->update(['message_lu' => true]);
        }

        return response()->json(['messages' => $messages]);
    }
}
