<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->when($request->input('role'), fn ($q, $role) => $q->byRole(UserRole::from($role)))
            ->when($request->input('search'), fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->when($request->input('active') !== null, fn ($q) => $q->where('utilisateur_actif', $request->boolean('active')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['role', 'search', 'active']),
            'roles' => collect(UserRole::cases())->map(fn ($r) => [
                'value' => $r->value,
                'label' => $r->shortLabel(),
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('admin/Users/Form', [
            'roles' => collect(UserRole::cases())->map(fn ($r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ]),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = User::create([
            ...$request->validated(),
            'password' => Hash::make($request->validated('password')),
            'utilisateur_actif' => true,
        ]);

        AuditLog::log(
            'created',
            $user,
            newValues: ['name' => $user->name, 'email' => $user->email, 'utilisateur_role' => $user->utilisateur_role->value],
            description: "Création de l'utilisateur « {$user->name} » ({$user->utilisateur_role->shortLabel()})",
        );

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$user->name} a été créé avec succès.");
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('admin/Users/Form', [
            'user' => $user->only(['id', 'name', 'email', 'utilisateur_role', 'telephone', 'utilisateur_actif']),
            'roles' => collect(UserRole::cases())->map(fn ($r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ]),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->safe()->except('password');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $oldValues = $user->only(['name', 'email', 'utilisateur_role', 'telephone', 'utilisateur_actif']);
        $user->update($data);
        $newValues = array_intersect_key($user->fresh()->only(['name', 'email', 'utilisateur_role', 'telephone', 'utilisateur_actif']), $oldValues);
        $changed = array_filter(
            $newValues,
            fn ($v, $k) => $oldValues[$k] !== $v,
            ARRAY_FILTER_USE_BOTH
        );

        if (! empty($changed)) {
            AuditLog::log(
                'updated',
                $user,
                oldValues: array_intersect_key($oldValues, $changed),
                newValues: $changed,
                description: "Modification de l'utilisateur « {$user->name} »",
            );
        }

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$user->name} a été mis à jour.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        // Prevent admin from disabling themselves
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas désactiver votre propre compte.']);
        }

        $user->update(['utilisateur_actif' => ! $user->utilisateur_actif]);

        $status = $user->utilisateur_actif ? 'activé' : 'désactivé';

        AuditLog::log(
            'user_status_change',
            $user,
            description: "Compte {$user->name} {$status} par ".auth()->user()->name,
        );

        return back()->with('success', "Le compte de {$user->name} a été {$status}.");
    }
}
