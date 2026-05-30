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
        $userId = $request->query('id_utilisateur');

        $logs = JournalAudit::with('utilisateur:id_utilisateur,utilisateur_nom,utilisateur_email,role_key')
            ->when($userId, fn ($q) => $q->where('id_utilisateur', $userId))
            ->orderByDesc('cree_le')
            ->paginate(50, ['id_audit', 'id_utilisateur', 'audit_action', 'audit_description', 'audit_entite_type', 'audit_entite_id', 'audit_anciennes_valeurs', 'audit_nouvelles_valeurs', 'audit_adresse_ip', 'cree_le'])
            ->withQueryString();

        $users = Utilisateur::select('id_utilisateur', 'utilisateur_nom', 'utilisateur_email', 'role_key')
            ->orderBy('utilisateur_nom')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id_utilisateur,
                'utilisateur_nom' => $u->utilisateur_nom,
                'utilisateur_email' => $u->utilisateur_email,
                'role_key' => $u->role_key?->value,
                'label_role' => $u->role_key?->shortLabel(),
            ]);

        return Inertia::render('admin/AuditLog', [
            'logs' => $logs,
            'users' => $users,
            'selectedUtilisateurId' => $userId ? (int) $userId : null,
        ]);
    }
}
