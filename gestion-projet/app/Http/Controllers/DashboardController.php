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
        $recentAuditLogs = AuditLog::with('user:id_utilisateur,name,utilisateur_role')
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(fn (AuditLog $log) => [
                'id' => $log->id,
                'action' => $log->audit_action,
                'description' => $log->audit_description,
                'user' => $log->user?->name ?? 'Système',
                'user_role' => $log->user?->utilisateur_role?->shortLabel(),
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
                'projets_actifs' => Projet::where('projet_statut', ProjectStatus::EnCours)->count(),
                'total_conventions' => Convention::count(),
                'demandes_en_cours' => DemandeDepense::whereNotIn('demande_statut', [
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
        $projetsActifs = Projet::where('projet_statut', ProjectStatus::EnCours)->count();
        $conventionsActives = Convention::where('convention_statut', ConventionStatus::Active)->count();

        $demandesEnAttente = DemandeDepense::where('demande_statut', DemandeStatus::Soumise)->count();
        $demandesEnAttenteAc = DemandeDepense::where('demande_statut', DemandeStatus::ValidéeDaf)->count();
        $rapportsSoumis = DemandeDepense::where('demande_statut', DemandeStatus::RapportSoumis)->count();

        $budgetTotal = Convention::where('convention_statut', ConventionStatus::Active)->sum('convention_montant_fcfa');
        $versementsTotal = Convention::where('convention_statut', ConventionStatus::Active)
            ->with('versements')
            ->get()
            ->flatMap->versements
            ->sum('versement_montant');

        $demandesRecentes = DemandeDepense::with([
            'convention:id_convention,convention_titre',
            'porteur:id_utilisateur,name',
        ])
            ->whereIn('demande_statut', [DemandeStatus::Soumise, DemandeStatus::ValidéeDaf, DemandeStatus::RapportSoumis])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DemandeDepense $d) => [
                'id' => $d->id,
                'objet' => $d->demande_objet,
                'montant' => $d->demande_montant,
                'status' => $d->demande_statut->value,
                'status_label' => $d->demande_statut->label(),
                'badge_class' => $d->demande_statut->badgeClass(),
                'porteur' => $d->porteur->name,
                'convention' => $d->convention->convention_titre,
                'created_at' => $d->created_at->toDateString(),
            ]);

        // Pie chart — versements reçus par projet (top 6)
        // Use eager-loaded data to avoid ambiguous JOIN on "montant" (versements + conventions both have it)
        $versementsParProjet = Projet::with(['conventions.versements'])
            ->get()
            ->map(fn (Projet $p) => [
                'name' => mb_strimwidth($p->projet_titre, 0, 20, '…'),
                'value' => $p->conventions->flatMap->versements->sum('versement_montant'),
            ])
            ->filter(fn ($item) => $item['value'] > 0)
            ->sortByDesc('value')
            ->take(6)
            ->values();

        // Line chart — paiements effectués par mois (12 derniers mois)
        // SUBSTR(paiement_date, 1, 7) works on both SQLite and MySQL (gives YYYY-MM)
        $paiementsParMois = Paiement::selectRaw('SUBSTR(paiement_date, 1, 7) as mois, SUM(paiement_montant) as total')
            ->where('paiement_date', '>=', now()->subYear()->startOfMonth())
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->map(fn ($row) => [
                'mois' => $row->mois,
                'total' => (int) $row->total,
            ]);

        // Bar chart — top 5 rubriques les plus consommées
        $topRubriques = Rubrique::with(['demandesDepenses' => fn ($q) => $q->whereIn('demande_statut', [
            DemandeStatus::Payee->value,
            DemandeStatus::RapportSoumis->value,
            DemandeStatus::Terminee->value,
        ])])
            ->get()
            ->map(fn (Rubrique $r) => [
                'libelle' => mb_strimwidth($r->rubrique_libelle, 0, 22, '…'),
                'consomme' => $r->demandesDepenses->sum('demande_montant'),
                'prevu' => $r->rubrique_montant_prevu,
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
        $demandesEnAttente = DemandeDepense::where('demande_statut', DemandeStatus::ValidéeDaf)->count();
        $rapportsSoumis = DemandeDepense::where('demande_statut', DemandeStatus::RapportSoumis)->count();
        $paiementsEffectues = DemandeDepense::where('demande_statut', DemandeStatus::Payee)
            ->orWhere('demande_statut', DemandeStatus::RapportSoumis)
            ->orWhere('demande_statut', DemandeStatus::Terminee)
            ->count();

        $montantPaye = Paiement::sum('paiement_montant');

        $demandesRecentes = DemandeDepense::with([
            'convention:id_convention,convention_titre',
            'porteur:id_utilisateur,name',
        ])
            ->whereIn('demande_statut', [DemandeStatus::ValidéeDaf, DemandeStatus::RapportSoumis])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DemandeDepense $d) => [
                'id' => $d->id,
                'objet' => $d->demande_objet,
                'montant' => $d->demande_montant,
                'status' => $d->demande_statut->value,
                'status_label' => $d->demande_statut->label(),
                'badge_class' => $d->demande_statut->badgeClass(),
                'porteur' => $d->porteur->name,
                'convention' => $d->convention->convention_titre,
                'created_at' => $d->created_at->toDateString(),
            ]);

        $paiementsRecents = Paiement::with([
            'demande:id_demande,demande_objet,id_porteur,id_convention',
            'demande.porteur:id_utilisateur,name',
            'demande.convention:id_convention,convention_titre',
        ])
            ->latest('paiement_date')
            ->limit(5)
            ->get()
            ->map(fn (Paiement $p) => [
                'id' => $p->id,
                'montant' => $p->paiement_montant,
                'date_paiement' => $p->paiement_date->toDateString(),
                'mode_paiement' => $p->paiement_mode->label(),
                'reference' => $p->paiement_reference,
                'objet' => $p->demande->demande_objet,
                'porteur' => $p->demande->porteur->name,
                'convention' => $p->demande->convention->convention_titre,
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
        $projetsActifsCount = Projet::forPorteur($porteur->id)->where('projet_statut', ProjectStatus::EnCours)->count();

        $demandesActives = DemandeDepense::where('id_porteur', $porteur->id)
            ->whereNotIn('demande_statut', [DemandeStatus::RejetéeDaf, DemandeStatus::RejetéeAc, DemandeStatus::Terminee])
            ->count();
        $demandesTotal = DemandeDepense::where('id_porteur', $porteur->id)->count();

        $demandesRecentes = DemandeDepense::with([
            'convention:id_convention,convention_titre,id_projet',
            'convention.projet:id_projet,projet_titre',
            'rubrique:id_rubrique,rubrique_libelle',
        ])
            ->where('id_porteur', $porteur->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DemandeDepense $d) => [
                'id' => $d->id,
                'objet' => $d->demande_objet,
                'montant' => $d->demande_montant,
                'status' => $d->demande_statut->value,
                'status_label' => $d->demande_statut->label(),
                'badge_class' => $d->demande_statut->badgeClass(),
                'convention' => $d->convention->convention_titre,
                'projet' => $d->convention->projet->projet_titre,
                'created_at' => $d->created_at->toDateString(),
            ]);

        // Budget overview: top 4 projects with budget data
        $projetsBudget = Projet::forPorteur($porteur->id)
            ->with(['conventions.versements', 'conventions.rubriques.demandesDepenses'])
            ->latest()
            ->limit(4)
            ->get()
            ->map(function (Projet $p) {
                $versements = $p->conventions->flatMap->versements->sum('versement_montant');
                $depenses = $p->conventions->flatMap->rubriques->flatMap->demandesDepenses
                    ->whereIn('demande_statut', [
                        DemandeStatus::Payee->value,
                        DemandeStatus::RapportSoumis->value,
                        DemandeStatus::Terminee->value,
                    ])->sum('demande_montant');

                return [
                    'titre' => mb_strimwidth($p->projet_titre, 0, 24, '…'),
                    'status' => $p->projet_statut->value,
                    'montant_estime' => $p->projet_montant_estime,
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
