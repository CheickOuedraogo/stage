<?php

namespace App\Http\Controllers\Administrateur;

use App\Enums\RoleUtilisateur;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administrateur\MiseAJourUtilisateurDemande;
use App\Http\Requests\Administrateur\StockageUtilisateurDemande;
use App\Models\JournalAudit;
use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UtilisateurControleur extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Utilisateur::class);

        $users = Utilisateur::query()
            ->when($request->input('role'), fn ($q, $role) => $q->parRole(RoleUtilisateur::from($role)))
            ->when($request->input('search'), fn ($q, $search) => $q->where('utilisateur_nom', 'like', "%{$search}%")
                ->orWhere('utilisateur_email', 'like', "%{$search}%"))
            ->when($request->input('active') !== null, fn ($q) => $q->where('utilisateur_actif', $request->boolean('active')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['role', 'search', 'active']),
            'roles' => collect(RoleUtilisateur::cases())->map(fn ($r) => [
                'value' => $r->value,
                'label' => $r->shortLabel(),
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Utilisateur::class);

        return Inertia::render('admin/Users/Form', [
            'roles' => collect(RoleUtilisateur::cases())->map(fn ($r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ]),
        ]);
    }

    public function store(StockageUtilisateurDemande $request): RedirectResponse
    {
        $this->authorize('create', Utilisateur::class);

        $user = Utilisateur::create([
            ...$request->validated(),
            'utilisateur_mot_de_passe' => Hash::make($request->validated('utilisateur_mot_de_passe')),
            'utilisateur_actif' => true,
        ]);

        JournalAudit::log(
            'created',
            $user,
            newValues: ['utilisateur_nom' => $user->utilisateur_nom, 'utilisateur_email' => $user->utilisateur_email, 'role_key' => $user->role_key->value],
            description: "Création de l'utilisateur « {$user->utilisateur_nom} » ({$user->role_key->shortLabel()})",
        );

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$user->utilisateur_nom} a été créé avec succès.");
    }

    public function edit(Utilisateur $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('admin/Users/Form', [
            'user' => $user->only(['id_utilisateur', 'utilisateur_nom', 'utilisateur_email', 'role_key', 'utilisateur_telephone', 'utilisateur_actif']),
            'roles' => collect(RoleUtilisateur::cases())->map(fn ($r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ]),
        ]);
    }

    public function update(MiseAJourUtilisateurDemande $request, Utilisateur $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->safe()->except('utilisateur_mot_de_passe');

        if ($request->filled('utilisateur_mot_de_passe')) {
            $data['utilisateur_mot_de_passe'] = Hash::make($request->validated('utilisateur_mot_de_passe'));
        }

        $oldValues = $user->only(['utilisateur_nom', 'utilisateur_email', 'role_key', 'utilisateur_telephone', 'utilisateur_actif']);
        $user->update($data);
        $newValues = array_intersect_key($user->fresh()->only(['utilisateur_nom', 'utilisateur_email', 'role_key', 'utilisateur_telephone', 'utilisateur_actif']), $oldValues);
        $changed = array_filter(
            $newValues,
            fn ($v, $k) => $oldValues[$k] !== $v,
            ARRAY_FILTER_USE_BOTH
        );

        if (! empty($changed)) {
            JournalAudit::log(
                'updated',
                $user,
                oldValues: array_intersect_key($oldValues, $changed),
                newValues: $changed,
                description: "Modification de l'utilisateur « {$user->utilisateur_nom} »",
            );
        }

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$user->utilisateur_nom} a été mis à jour.");
    }

    public function toggleActive(Utilisateur $user): RedirectResponse
    {
        $this->authorize('update', $user);

        // Prevent admin from disabling themselves
        if ($user->id_utilisateur === auth()->id()) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas désactiver votre propre compte.']);
        }

        $user->update(['utilisateur_actif' => ! $user->utilisateur_actif]);

        $status = $user->utilisateur_actif ? 'activé' : 'désactivé';

        JournalAudit::log(
            'user_status_change',
            $user,
            description: "Compte {$user->utilisateur_nom} {$status} par ".auth()->user()->utilisateur_nom,
        );

        return back()->with('success', "Le compte de {$user->utilisateur_nom} a été {$status}.");
    }
}
