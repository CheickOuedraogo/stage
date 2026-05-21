<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = Notification::where('id_utilisateur', $request->user()->id_utilisateur)
            ->latest('cree_le')
            ->paginate(20)
            ->through(fn (Notification $n) => [
                'id_notification' => $n->id_notification,
                'type_notification' => $n->type_notification->value,
                'id_demande' => $n->id_demande,
                'id_projet' => $n->id_projet,
                'notification_objet' => $n->notification_objet,
                'notification_libelle_statut' => $n->notification_libelle_statut,
                'notification_motif' => $n->notification_motif,
                'lu_le' => $n->lu_le?->toIso8601String(),
                'cree_le' => $n->cree_le->diffForHumans(),
                'cree_le_complet' => $n->cree_le->format('d/m/Y à H:i'),
            ]);

        // Marquer toutes comme lues à la visite
        Notification::where('id_utilisateur', $request->user()->id_utilisateur)
            ->whereNull('lu_le')
            ->update(['lu_le' => now()]);

        return Inertia::render('Notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function marquerLue(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->id_utilisateur === $request->user()->id_utilisateur, 403);
        $notification->marquerCommentLue();

        return back();
    }

    public function marquerToutesLues(Request $request): RedirectResponse
    {
        Notification::where('id_utilisateur', $request->user()->id_utilisateur)
            ->whereNull('lu_le')
            ->update(['lu_le' => now()]);

        return back()->with('success', 'Toutes les notifications marquées comme lues.');
    }
}
