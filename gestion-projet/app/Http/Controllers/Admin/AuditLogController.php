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

        $logs = AuditLog::with('user:id,name,email,role')
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        $users = User::select('id', 'name', 'email', 'role')
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role?->value,
                'role_label' => $u->role?->shortLabel(),
            ]);

        return Inertia::render('admin/AuditLog', [
            'logs' => $logs,
            'users' => $users,
            'selectedUserId' => $userId ? (int) $userId : null,
        ]);
    }
}
