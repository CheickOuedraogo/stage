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

        if ($admin) {
            ChatMessage::where('sender_id', $admin->id)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $faqItems = FaqItem::active()
            ->get()
            ->filter(fn (FaqItem $f) => $f->isVisibleFor(UserRole::Ac))
            ->map(fn (FaqItem $f) => ['id' => $f->id, 'question' => $f->question, 'reponse' => $f->reponse])
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
        $since = (int) $request->query('since', 0);

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

        if ($admin && $messages->isNotEmpty()) {
            ChatMessage::where('sender_id', $admin->id)
                ->where('receiver_id', $user->id)
                ->where('id', '>', $since)
                ->update(['is_read' => true]);
        }

        return response()->json(['messages' => $messages]);
    }
}
