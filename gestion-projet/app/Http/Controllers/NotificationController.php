<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20)
            ->through(fn (DatabaseNotification $n) => [
                'id' => $n->id_utilisateur,
                'data' => $n->data,
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->diffForHumans(),
                'created_at_full' => $n->created_at->format('d/m/Y à H:i'),
            ]);

        // Marquer toutes comme lues à la visite
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return Inertia::render('Notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($notification->notifiable_id === $request->user()->id_utilisateur, 403);
        $notification->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'Toutes les notifications marquées comme lues.');
    }
}
