<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(): Response
    {
        $conversations = User::whereIn('utilisateur_role', [UserRole::Daf, UserRole::Ac])
            ->get()
            ->map(function (User $u) {
                $lastMsg = ChatMessage::where(fn ($q) => $q
                    ->where('id_expediteur', $u->id)
                    ->orWhere('id_destinataire', $u->id)
                )
                    ->latest()
                    ->first();

                $unread = ChatMessage::where('id_expediteur', $u->id)
                    ->where('message_lu', false)
                    ->count();

                return [
                    'user_id' => $u->id,
                    'name' => $u->name,
                    'role' => $u->utilisateur_role->label(),
                    'unread' => $unread,
                    'last_message' => $lastMsg?->message_contenu,
                    'last_at' => $lastMsg?->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('admin/Chat/Index', [
            'conversations' => $conversations,
        ]);
    }

    public function show(User $user): Response
    {
        $admin = auth()->user();

        $messages = ChatMessage::where(fn ($q) => $q
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
                'is_mine' => $m->id_expediteur === $admin->id,
                'sender_name' => $m->id_expediteur === $admin->id ? 'Moi (Admin)' : $user->name,
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        // Mark user's messages as read
        ChatMessage::where('id_expediteur', $user->id)
            ->where('id_destinataire', $admin->id)
            ->where('message_lu', false)
            ->update(['message_lu' => true]);

        return Inertia::render('admin/Chat/Show', [
            'contact' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->utilisateur_role->label(),
            ],
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, User $user): RedirectResponse
    {
        $request->validate(['message' => ['required', 'string', 'max:2000']]);

        ChatMessage::create([
            'id_expediteur' => auth()->id(),
            'id_destinataire' => $user->id,
            'message_contenu' => $request->message,
        ]);

        return back();
    }

    public function poll(Request $request, User $user): JsonResponse
    {
        $admin = auth()->user();
        $since = (int) $request->query('since', 0);

        $messages = ChatMessage::where('id_expediteur', $user->id)
            ->where('id_destinataire', $admin->id)
            ->where('id', '>', $since)
            ->get()
            ->map(fn (ChatMessage $m) => [
                'id' => $m->id,
                'message' => $m->message_contenu,
                'is_mine' => false,
                'sender_name' => $user->name,
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        if ($messages->isNotEmpty()) {
            ChatMessage::where('id_expediteur', $user->id)
                ->where('id_destinataire', $admin->id)
                ->where('id', '>', $since)
                ->update(['message_lu' => true]);
        }

        return response()->json(['messages' => $messages]);
    }
}
