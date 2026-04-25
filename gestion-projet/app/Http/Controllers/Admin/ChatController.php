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
        $conversations = User::whereIn('role', [UserRole::Daf, UserRole::Ac])
            ->get()
            ->map(function (User $u) {
                $lastMsg = ChatMessage::where(fn ($q) => $q
                    ->where('sender_id', $u->id)
                    ->orWhere('receiver_id', $u->id)
                )
                    ->latest()
                    ->first();

                $unread = ChatMessage::where('sender_id', $u->id)
                    ->where('is_read', false)
                    ->count();

                return [
                    'user_id' => $u->id,
                    'name' => $u->name,
                    'role' => $u->role->label(),
                    'unread' => $unread,
                    'last_message' => $lastMsg?->message,
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
            ->where('sender_id', $user->id)->where('receiver_id', $admin->id)
            ->orWhere('sender_id', $admin->id)->where('receiver_id', $user->id)
        )
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (ChatMessage $m) => [
                'id' => $m->id,
                'message' => $m->message,
                'is_mine' => $m->sender_id === $admin->id,
                'sender_name' => $m->sender_id === $admin->id ? 'Moi (Admin)' : $user->name,
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        // Mark user's messages as read
        ChatMessage::where('sender_id', $user->id)
            ->where('receiver_id', $admin->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return Inertia::render('admin/Chat/Show', [
            'contact' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role->label(),
            ],
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, User $user): RedirectResponse
    {
        $request->validate(['message' => ['required', 'string', 'max:2000']]);

        ChatMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'message' => $request->message,
        ]);

        return back();
    }

    public function poll(Request $request, User $user): JsonResponse
    {
        $admin = auth()->user();
        $since = (int) $request->query('since', 0);

        $messages = ChatMessage::where('sender_id', $user->id)
            ->where('receiver_id', $admin->id)
            ->where('id', '>', $since)
            ->get()
            ->map(fn (ChatMessage $m) => [
                'id' => $m->id,
                'message' => $m->message,
                'is_mine' => false,
                'sender_name' => $user->name,
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        if ($messages->isNotEmpty()) {
            ChatMessage::where('sender_id', $user->id)
                ->where('receiver_id', $admin->id)
                ->where('id', '>', $since)
                ->update(['is_read' => true]);
        }

        return response()->json(['messages' => $messages]);
    }
}
