<?php

namespace App\Http\Controllers;

use App\Enums\ConventionStatus;
use App\Enums\DemandeStatus;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function admin(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::active()->count(),
                'users_by_role' => collect(UserRole::cases())->map(fn ($r) => [
                    'role' => $r->shortLabel(),
                    'label' => $r->label(),
                    'count' => User::byRole($r)->count(),
                ])->all(),
            ],
        ]);
    }

    public function daf(): Response
    {
        $projetsActifs = Projet::where('status', ProjectStatus::EnCours)->count();
        $conventionsActives = Convention::where('status', ConventionStatus::Active)->count();

        $demandesEnAttente = DemandeDepense::where('status', DemandeStatus::Soumise)->count();
        $demandesEnAttenteAc = DemandeDepense::where('status', DemandeStatus::ValidéeDaf)->count();
        $rapportsSoumis = DemandeDepense::where('status', DemandeStatus::RapportSoumis)->count();

        $budgetTotal = Convention::where('status', ConventionStatus::Active)->sum('montant_fcfa');
        $versementsTotal = Convention::where('status', ConventionStatus::Active)
            ->with('versements')
            ->get()
            ->flatMap->versements
            ->sum('montant');

        $demandesRecentes = DemandeDepense::with([
            'convention:id,titre',
            'porteur:id,name',
        ])
            ->whereIn('status', [DemandeStatus::Soumise, DemandeStatus::ValidéeDaf, DemandeStatus::RapportSoumis])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DemandeDepense $d) => [
                'id' => $d->id,
                'objet' => $d->objet,
                'montant' => $d->montant,
                'status' => $d->status->value,
                'status_label' => $d->status->label(),
                'badge_class' => $d->status->badgeClass(),
                'porteur' => $d->porteur->name,
                'convention' => $d->convention->titre,
                'created_at' => $d->created_at->toDateString(),
            ]);

        return Inertia::render('daf/Dashboard', [
            'stats' => [
                'projets_actifs' => $projetsActifs,
                'conventions_actives' => $conventionsActives,
                'demandes_en_attente' => $demandesEnAttente,
                'demandes_en_attente_ac' => $demandesEnAttenteAc,
                'rapports_soumis' => $rapportsSoumis,
                'budget_total' => $budgetTotal,
                'versements_total' => $versementsTotal,
            ],
            'demandes_recentes' => $demandesRecentes,
        ]);
    }

    public function ac(): Response
    {
        $demandesEnAttente = DemandeDepense::where('status', DemandeStatus::ValidéeDaf)->count();
        $rapportsSoumis = DemandeDepense::where('status', DemandeStatus::RapportSoumis)->count();
        $paiementsEffectues = DemandeDepense::where('status', DemandeStatus::Payee)
            ->orWhere('status', DemandeStatus::RapportSoumis)
            ->orWhere('status', DemandeStatus::Terminee)
            ->count();

        $montantPaye = Paiement::sum('montant');

        $demandesRecentes = DemandeDepense::with([
            'convention:id,titre',
            'porteur:id,name',
        ])
            ->whereIn('status', [DemandeStatus::ValidéeDaf, DemandeStatus::RapportSoumis])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DemandeDepense $d) => [
                'id' => $d->id,
                'objet' => $d->objet,
                'montant' => $d->montant,
                'status' => $d->status->value,
                'status_label' => $d->status->label(),
                'badge_class' => $d->status->badgeClass(),
                'porteur' => $d->porteur->name,
                'convention' => $d->convention->titre,
                'created_at' => $d->created_at->toDateString(),
            ]);

        return Inertia::render('ac/Dashboard', [
            'stats' => [
                'demandes_en_attente' => $demandesEnAttente,
                'rapports_soumis' => $rapportsSoumis,
                'paiements_effectues' => $paiementsEffectues,
                'montant_paye' => $montantPaye,
            ],
            'demandes_recentes' => $demandesRecentes,
        ]);
    }

    public function porteur(Request $request): Response
    {
        $porteur = $request->user();

        $projetsCount = Projet::forPorteur($porteur->id)->count();
        $projetsActifsCount = Projet::forPorteur($porteur->id)->where('status', ProjectStatus::EnCours)->count();

        $demandesActives = DemandeDepense::where('porteur_id', $porteur->id)
            ->whereNotIn('status', [DemandeStatus::RejetéeDaf, DemandeStatus::RejetéeAc, DemandeStatus::Terminee])
            ->count();
        $demandesTotal = DemandeDepense::where('porteur_id', $porteur->id)->count();

        $demandesRecentes = DemandeDepense::with([
            'convention:id,titre,projet_id',
            'convention.projet:id,titre',
            'rubrique:id,libelle',
        ])
            ->where('porteur_id', $porteur->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DemandeDepense $d) => [
                'id' => $d->id,
                'objet' => $d->objet,
                'montant' => $d->montant,
                'status' => $d->status->value,
                'status_label' => $d->status->label(),
                'badge_class' => $d->status->badgeClass(),
                'convention' => $d->convention->titre,
                'projet' => $d->convention->projet->titre,
                'created_at' => $d->created_at->toDateString(),
            ]);

        return Inertia::render('porteur/Dashboard', [
            'stats' => [
                'projets_count' => $projetsCount,
                'projets_actifs' => $projetsActifsCount,
                'demandes_actives' => $demandesActives,
                'demandes_total' => $demandesTotal,
            ],
            'demandes_recentes' => $demandesRecentes,
        ]);
    }
}
