<?php

namespace App\Http\Controllers\Daf;

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
    public function index(Request $request): Response
    {
        $user = $request->user();
        $admin = User::byRole(UserRole::Admin)->first();

        $messages = $admin
            ? ChatMessage::where(fn ($q) => $q
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
                    'is_mine' => $m->sender_id === $user->id,
                    'sender_name' => $m->sender_id === $user->id ? 'Moi' : 'Admin',
                    'created_at' => $m->created_at->toIso8601String(),
                ])
            : collect();

        // Mark admin's messages as read
        if ($admin) {
            ChatMessage::where('sender_id', $admin->id)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return Inertia::render('daf/Chat', [
            'messages' => $messages,
            'admin_online' => false,
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate(['message' => ['required', 'string', 'max:2000']]);

        $admin = User::byRole(UserRole::Admin)->firstOrFail();

        ChatMessage::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $admin->id,
            'message' => $request->message,
        ]);

        return back();
    }

    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();
        $admin = User::byRole(UserRole::Admin)->first();
        $since = $request->query('since', 0);

        $messages = $admin
            ? ChatMessage::where('sender_id', $admin->id)
                ->where('receiver_id', $user->id)
                ->where('id', '>', $since)
                ->get()
                ->map(fn (ChatMessage $m) => [
                    'id' => $m->id,
                    'message' => $m->message,
                    'is_mine' => false,
                    'sender_name' => 'Admin',
                    'created_at' => $m->created_at->toIso8601String(),
                ])
            : collect();

        // Mark new messages as read
        if ($admin && $messages->isNotEmpty()) {
            ChatMessage::where('sender_id', $admin->id)
                ->where('receiver_id', $user->id)
                ->where('id', '>', $since)
                ->update(['is_read' => true]);
        }

        return response()->json(['messages' => $messages]);
    }
}
