<?php

namespace App\Http\Controllers;

use App\Enums\ConventionStatus;
use App\Enums\DemandeStatus;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\Rubrique;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function admin(): Response
    {
        $recentAuditLogs = AuditLog::with('user:id,name')
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user' => $log->user?->name ?? 'Système',
                'created_at' => $log->created_at?->diffForHumans() ?? '—',
            ]);

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::active()->count(),
                'users_by_role' => collect(UserRole::cases())->map(fn ($r) => [
                    'role' => $r->shortLabel(),
                    'label' => $r->label(),
                    'count' => User::byRole($r)->count(),
                ])->all(),
                'total_projets' => Projet::count(),
                'projets_actifs' => Projet::where('status', ProjectStatus::EnCours)->count(),
                'total_conventions' => Convention::count(),
                'demandes_en_cours' => DemandeDepense::whereNotIn('status', [
                    DemandeStatus::RejetéeDaf->value,
                    DemandeStatus::RejetéeAc->value,
                    DemandeStatus::Terminee->value,
                ])->count(),
            ],
            'recent_audit_logs' => $recentAuditLogs,
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

        // Pie chart — versements reçus par projet (top 6)
        // Use eager-loaded data to avoid ambiguous JOIN on "montant" (versements + conventions both have it)
        $versementsParProjet = Projet::with(['conventions.versements'])
            ->get()
            ->map(fn (Projet $p) => [
                'name' => mb_strimwidth($p->titre, 0, 20, '…'),
                'value' => $p->conventions->flatMap->versements->sum('montant'),
            ])
            ->filter(fn ($item) => $item['value'] > 0)
            ->sortByDesc('value')
            ->take(6)
            ->values();

        // Line chart — paiements effectués par mois (12 derniers mois)
        // SUBSTR(date_paiement, 1, 7) works on both SQLite and MySQL (gives YYYY-MM)
        $paiementsParMois = Paiement::selectRaw('SUBSTR(date_paiement, 1, 7) as mois, SUM(montant) as total')
            ->where('date_paiement', '>=', now()->subYear()->startOfMonth())
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->map(fn ($row) => [
                'mois' => $row->mois,
                'total' => (int) $row->total,
            ]);

        // Bar chart — top 5 rubriques les plus consommées
        $topRubriques = Rubrique::with(['demandesDepenses' => fn ($q) => $q->whereIn('status', [
            DemandeStatus::Payee->value,
            DemandeStatus::RapportSoumis->value,
            DemandeStatus::Terminee->value,
        ])])
            ->get()
            ->map(fn (Rubrique $r) => [
                'libelle' => mb_strimwidth($r->libelle, 0, 22, '…'),
                'consomme' => $r->demandesDepenses->sum('montant'),
                'prevu' => $r->montant_prevu,
            ])
            ->filter(fn ($item) => $item['consomme'] > 0)
            ->sortByDesc('consomme')
            ->take(5)
            ->values();

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
            'versements_par_projet' => $versementsParProjet,
            'paiements_par_mois' => $paiementsParMois,
            'top_rubriques' => $topRubriques,
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

        $paiementsRecents = Paiement::with([
            'demande:id,objet,porteur_id,convention_id',
            'demande.porteur:id,name',
            'demande.convention:id,titre',
        ])
            ->latest('date_paiement')
            ->limit(5)
            ->get()
            ->map(fn (Paiement $p) => [
                'id' => $p->id,
                'montant' => $p->montant,
                'date_paiement' => $p->date_paiement->toDateString(),
                'mode_paiement' => $p->mode_paiement->label(),
                'reference' => $p->reference,
                'objet' => $p->demande->objet,
                'porteur' => $p->demande->porteur->name,
                'convention' => $p->demande->convention->titre,
            ]);

        return Inertia::render('ac/Dashboard', [
            'stats' => [
                'demandes_en_attente' => $demandesEnAttente,
                'rapports_soumis' => $rapportsSoumis,
                'paiements_effectues' => $paiementsEffectues,
                'montant_paye' => $montantPaye,
            ],
            'demandes_recentes' => $demandesRecentes,
            'paiements_recents' => $paiementsRecents,
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

        // Budget overview: top 4 projects with budget data
        $projetsBudget = Projet::forPorteur($porteur->id)
            ->with(['conventions.versements', 'conventions.rubriques.demandesDepenses'])
            ->latest()
            ->limit(4)
            ->get()
            ->map(function (Projet $p) {
                $versements = $p->conventions->flatMap->versements->sum('montant');
                $depenses = $p->conventions->flatMap->rubriques->flatMap->demandesDepenses
                    ->whereIn('status', [
                        DemandeStatus::Payee->value,
                        DemandeStatus::RapportSoumis->value,
                        DemandeStatus::Terminee->value,
                    ])->sum('montant');

                return [
                    'titre' => mb_strimwidth($p->titre, 0, 24, '…'),
                    'status' => $p->status->value,
                    'montant_estime' => $p->montant_estime,
                    'versements' => $versements,
                    'depenses' => $depenses,
                    'disponible' => max(0, $versements - $depenses),
                ];
            });

        return Inertia::render('porteur/Dashboard', [
            'stats' => [
                'projets_count' => $projetsCount,
                'projets_actifs' => $projetsActifsCount,
                'demandes_actives' => $demandesActives,
                'demandes_total' => $demandesTotal,
            ],
            'demandes_recentes' => $demandesRecentes,
            'projets_budget' => $projetsBudget,
        ]);
    }
}
