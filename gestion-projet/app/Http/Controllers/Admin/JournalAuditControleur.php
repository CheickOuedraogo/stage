<?php

namespace App\Http\Controllers\Administrateur;

use App\Http\Controllers\Controller;
use App\Models\JournalAudit;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JournalAuditController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->query('user_id');

        $logs = JournalAudit::with('utilisateur:id_utilisateur,utilisateur_nom,utilisateur_email,utilisateur_role')
            ->when($userId, fn ($q) => $q->where('id_utilisateur', $userId))
            ->orderByDesc('cree_le')
            ->paginate(50, ['id_audit', 'id_utilisateur', 'audit_action', 'audit_description', 'audit_entite_type', 'audit_entite_id', 'audit_anciennes_valeurs', 'audit_nouvelles_valeurs', 'audit_adresse_ip', 'cree_le'])
            ->withQueryString();

        $users = Utilisateur::select('id_utilisateur', 'utilisateur_nom', 'utilisateur_email', 'utilisateur_role')
            ->orderBy('utilisateur_nom')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id_utilisateur,
                'utilisateur_nom' => $u->utilisateur_nom,
                'utilisateur_email' => $u->utilisateur_email,
                'role' => $u->utilisateur_role?->value,
                'label_role' => $u->utilisateur_role?->shortLabel(),
            ]);

        return Inertia::render('admin/JournalAudit', [
            'logs' => $logs,
            'users' => $users,
            'selectedUtilisateurId' => $userId ? (int) $userId : null,
        ]);
    }
}
