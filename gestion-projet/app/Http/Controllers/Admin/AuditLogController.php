<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->query('user_id');

        $logs = AuditLog::with('user:id_utilisateur,name,email,utilisateur_role')
            ->when($userId, fn ($q) => $q->where('id_utilisateur', $userId))
            ->orderByDesc('created_at')
            ->paginate(50, ['id_audit', 'id_utilisateur', 'audit_action', 'audit_description', 'audit_entite_type', 'audit_entite_id', 'audit_anciennes_valeurs', 'audit_nouvelles_valeurs', 'audit_adresse_ip', 'created_at'])
            ->withQueryString();

        $users = User::select('id_utilisateur', 'name', 'email', 'utilisateur_role')
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->utilisateur_role?->value,
                'role_label' => $u->utilisateur_role?->shortLabel(),
            ]);

        return Inertia::render('admin/AuditLog', [
            'logs' => $logs,
            'users' => $users,
            'selectedUserId' => $userId ? (int) $userId : null,
        ]);
    }
}
