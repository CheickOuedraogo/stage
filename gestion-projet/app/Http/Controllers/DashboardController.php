<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutConvention;
use App\Enums\StatutDemande;
use App\Enums\StatutProjet;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\Rubrique;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function admin(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'total_users' => Utilisateur::count(),
                'active_users' => Utilisateur::active()->count(),
                'users_by_role' => collect(RoleUtilisateur::cases())->map(fn ($r) => [
                    'role' => $r->shortLabel(),
                    'label' => $r->label(),
                    'count' => Utilisateur::parRole($r)->count(),
                ])->all(),
                'total_projets' => Projet::count(),
                'projets_actifs' => Projet::where('projet_statut', StatutProjet::EnCours)->count(),
                'total_conventions' => Convention::count(),
                'demandes_en_cours' => DemandeDepense::whereNotIn('demande_statut', [
                    StatutDemande::RejeteeDaf->value,
                    StatutDemande::RejeteeAgentComptable->value,
                    StatutDemande::Terminee->value,
                ])->count(),
            ],
        ]);
    }

    public function daf(): Response
    {
        $projetsEnCours = Projet::where('projet_statut', StatutProjet::EnCours)->count();
        $conventionsActives = Convention::where('convention_statut', StatutConvention::Active)->count();

        $demandesEnAttente = DemandeDepense::where('demande_statut', StatutDemande::Soumise)->count();
        $demandesEnAttenteAgentComptable = DemandeDepense::where('demande_statut', StatutDemande::ValideeDaf)->count();
        $rapportsSoumis = DemandeDepense::where('demande_statut', StatutDemande::RapportSoumis)->count();

        $budgetTotal = Convention::where('convention_statut', StatutConvention::Active)->sum(\DB::raw('convention_montant * convention_taux_conversion'));
        $versementsTotal = Convention::where('convention_statut', StatutConvention::Active)
            ->with('versements')
            ->get()
            ->flatMap->versements
            ->sum('versement_montant');

        $demandesRecentes = DemandeDepense::with([
            'convention:id_convention,convention_titre',
            'porteur:id_utilisateur,utilisateur_nom',
        ])
            ->whereIn('demande_statut', [StatutDemande::Soumise, StatutDemande::ValideeDaf, StatutDemande::RapportSoumis])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DemandeDepense $d) => [
                'id' => $d->id_demande,
                'objet' => $d->demande_objet,
                'montant' => $d->demande_montant,
                'statut' => $d->demande_statut->value,
                'libelle_statut' => $d->demande_statut->label(),
                'badge_class' => $d->demande_statut->badgeClass(),
                'porteur' => $d->porteur->utilisateur_nom,
                'convention' => $d->convention->convention_titre,
                'cree_le' => $d->cree_le?->toDateString() ?? '—',
            ]);

        // Pie chart — versements reçus par projet (top 6)
        // Use eager-loaded data to avoid ambiguous JOIN on "montant" (versements + conventions both have it)
        $versementsParProjet = Projet::with(['conventions.versements'])
            ->get()
            ->map(fn (Projet $p) => [
                'utilisateur_nom' => mb_strimwidth($p->projet_titre, 0, 20, '…'),
                'value' => $p->conventions->flatMap->versements->sum('versement_montant'),
            ])
            ->filter(fn ($item) => $item['value'] > 0)
            ->sortByDesc('value')
            ->take(6)
            ->values();

        // Line chart — paiements effectués par mois (12 derniers mois)
        $dateExpr = DB::connection()->getDriverName() === 'pgsql'
            ? "TO_CHAR(paiement_date, 'YYYY-MM')"
            : 'SUBSTR(paiement_date, 1, 7)';
        $paiementsParMois = Paiement::selectRaw("{$dateExpr} as mois, SUM(paiement_montant) as total")
            ->where('paiement_date', '>=', now()->subYear()->startOfMonth())
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->map(fn ($row) => [
                'mois' => $row->mois,
                'total' => (int) $row->total,
            ]);

        // Bar chart — top 5 rubriques les plus consommées
        $topRubriques = Rubrique::withSum('paiements', 'paiement_montant')
            ->get()
            ->map(fn (Rubrique $r) => [
                'libelle' => mb_strimwidth($r->rubrique_libelle, 0, 22, '…'),
                'consomme' => (int) $r->paiements_sum_paiement_montant,
                'prevu' => $r->rubrique_montant,
            ])
            ->filter(fn ($item) => $item['consomme'] > 0)
            ->sortByDesc('consomme')
            ->take(5)
            ->values();

        return Inertia::render('daf/Dashboard', [
            'stats' => [
                'projets_actifs' => $projetsEnCours,
                'conventions_actives' => $conventionsActives,
                'demandes_en_attente' => $demandesEnAttente,
                'demandes_en_attente_ac' => $demandesEnAttenteAgentComptable,
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

    public function ac(Request $request): Response
    {
        $demandesEnAttente = DemandeDepense::where('demande_statut', StatutDemande::ValideeDaf)->count();
        $rapportsSoumis = DemandeDepense::where('demande_statut', StatutDemande::RapportSoumis)->count();
        $paiementsEnAttente = DemandeDepense::where('demande_statut', StatutDemande::ValideeAgentComptable)->count();
        $montantPaye = Paiement::sum('paiement_montant');

        $demandesRecentes = DemandeDepense::with([
            'convention:id_convention,convention_titre',
            'porteur:id_utilisateur,utilisateur_nom',
        ])
            ->whereIn('demande_statut', [StatutDemande::ValideeDaf, StatutDemande::RapportSoumis])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DemandeDepense $d) => [
                'id' => $d->id_demande,
                'objet' => $d->demande_objet,
                'montant' => $d->demande_montant,
                'statut' => $d->demande_statut->value,
                'libelle_statut' => $d->demande_statut->label(),
                'badge_class' => $d->demande_statut->badgeClass(),
                'porteur' => $d->porteur->utilisateur_nom,
                'convention' => $d->convention->convention_titre,
                'cree_le' => $d->cree_le?->toDateString() ?? '—',
            ]);

        $paiementsRecents = Paiement::with([
            'demande:id_demande,demande_objet,id_porteur',
            'demande.porteur:id_utilisateur,utilisateur_nom',
            'convention:id_convention,convention_titre',
        ])
            ->latest('paiement_date')
            ->limit(5)
            ->get()
            ->map(fn (Paiement $p) => [
                'id' => $p->id_paiement,
                'montant' => $p->paiement_montant,
                'date_paiement' => $p->paiement_date,
                'mode_paiement' => $p->paiement_mode?->label() ?? '—',
                'reference' => $p->paiement_reference,
                'objet' => $p->demande?->demande_objet ?? $p->paiement_objet ?? 'Paiement direct',
                'porteur' => $p->demande?->porteur?->utilisateur_nom ?? '—',
                'convention' => $p->convention->convention_titre,
            ]);

        return Inertia::render('ac/Dashboard', [
            'stats' => [
                'demandes_en_attente' => $demandesEnAttente,
                'rapports_soumis' => $rapportsSoumis,
                'paiements_effectues' => Paiement::count(),
                'montant_paye' => $montantPaye,
            ],
            'demandes_recentes' => $demandesRecentes,
            'paiements_recents' => $paiementsRecents,
        ]);
    }

    public function porteur(Request $request): Response
    {
        $porteur = $request->user();

        $projetsCount = Projet::pourPorteur($porteur->id_utilisateur)->count();
        $projetsEnCoursCount = Projet::pourPorteur($porteur->id_utilisateur)->where('projet_statut', StatutProjet::EnCours)->count();

        $demandesActives = DemandeDepense::where('id_porteur', $porteur->id_utilisateur)
            ->whereNotIn('demande_statut', [StatutDemande::RejeteeDaf, StatutDemande::RejeteeAgentComptable, StatutDemande::Terminee])
            ->count();
        $demandesTotal = DemandeDepense::where('id_porteur', $porteur->id_utilisateur)->count();

        $demandesRecentes = DemandeDepense::with([
            'convention:id_convention,convention_titre,id_projet',
            'convention.projet:id_projet,projet_titre',
            'rubrique:id_rubrique,rubrique_libelle',
        ])
            ->where('id_porteur', $porteur->id_utilisateur)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DemandeDepense $d) => [
                'id' => $d->id_demande,
                'objet' => $d->demande_objet,
                'montant' => $d->demande_montant,
                'statut' => $d->demande_statut->value,
                'libelle_statut' => $d->demande_statut->label(),
                'badge_class' => $d->demande_statut->badgeClass(),
                'convention' => $d->convention->convention_titre,
                'projet' => $d->convention->projet->projet_titre,
                'cree_le' => $d->cree_le?->toDateString() ?? '—',
            ]);

        // Budget overview: top 4 projects with budget data
        $projetsBudget = Projet::pourPorteur($porteur->id_utilisateur)
            ->with(['conventions.versements'])
            ->latest()
            ->limit(4)
            ->get()
            ->map(function (Projet $p) {
                $versements = $p->conventions->flatMap->versements->sum('versement_montant');
                $depenses = Paiement::sumForProjet($p->id_projet);

                return [
                    'titre' => mb_strimwidth($p->projet_titre, 0, 24, '…'),
                    'statut' => $p->projet_statut->value,
                    'montant_estime' => $p->projet_montant_estime,
                    'versements' => $versements,
                    'depenses' => $depenses,
                    'disponible' => max(0, $versements - $depenses),
                ];
            });

        return Inertia::render('porteur/Dashboard', [
            'stats' => [
                'projets_count' => $projetsCount,
                'projets_actifs' => $projetsEnCoursCount,
                'demandes_actives' => $demandesActives,
                'demandes_total' => $demandesTotal,
            ],
            'demandes_recentes' => $demandesRecentes,
            'projets_budget' => $projetsBudget,
        ]);
    }
}
