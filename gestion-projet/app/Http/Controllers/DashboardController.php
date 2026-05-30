<?php

namespace App\Http\Controllers;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutConvention;
use App\Enums\StatutDemande;
use App\Enums\StatutProjet;
use App\Models\Convention;
use App\Models\DemandeDepense;
use App\Models\Faq;
use App\Models\JournalAudit;
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
        $recentJournalAudits = JournalAudit::with('utilisateur:id_utilisateur,utilisateur_nom,role_key')
            ->latest('cree_le')
            ->limit(8)
            ->get()
            ->map(fn (JournalAudit $log) => [
                'id' => $log->id_audit,
                'action' => $log->audit_action,
                'description' => $log->audit_description,
                'user' => $log->utilisateur?->utilisateur_nom ?? 'Système',
                'user_role' => $log->utilisateur?->role_key?->shortLabel(),
                'cree_le' => $log->cree_le?->diffForHumans() ?? '—',
            ]);

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
            'recent_audit_logs' => $recentJournalAudits,
            'faq_items' => $this->getFaqForRole(RoleUtilisateur::Administrateur),
        ]);
    }

    public function daf(): Response
    {
        $projetsAgentComptabletifs = Projet::where('projet_statut', StatutProjet::EnCours)->count();
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
                'projets_actifs' => $projetsAgentComptabletifs,
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
            'faq_items' => $this->getFaqForRole(RoleUtilisateur::Daf),
        ]);
    }

    public function ac(): Response
    {
        $demandesEnAttente = DemandeDepense::where('demande_statut', StatutDemande::ValideeDaf)->count();
        $rapportsSoumis = DemandeDepense::where('demande_statut', StatutDemande::RapportSoumis)->count();
        $paiementsEffectues = DemandeDepense::where('demande_statut', StatutDemande::Payee)
            ->orWhere('demande_statut', StatutDemande::RapportSoumis)
            ->orWhere('demande_statut', StatutDemande::Terminee)
            ->count();

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
            'demande:id_demande,demande_objet,id_porteur,id_convention',
            'demande.porteur:id_utilisateur,utilisateur_nom',
            'demande.convention:id_convention,convention_titre',
        ])
            ->latest('paiement_date')
            ->limit(5)
            ->get()
            ->map(fn (Paiement $p) => [
                'id' => $p->id_paiement,
                'montant' => $p->paiement_montant,
                'date_paiement' => $p->paiement_date->toDateString(),
                'mode_paiement' => $p->paiement_mode?->label() ?? '',
                'reference' => $p->paiement_reference,
                'objet' => $p->demande->demande_objet,
                'porteur' => $p->demande->porteur->utilisateur_nom,
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
            'faq_items' => $this->getFaqForRole(RoleUtilisateur::AgentComptable),
        ]);
    }

    public function porteur(Request $request): Response
    {
        $porteur = $request->user();

        $projetsCount = Projet::pourPorteur($porteur->id_utilisateur)->count();
        $projetsAgentComptabletifsCount = Projet::pourPorteur($porteur->id_utilisateur)->where('projet_statut', StatutProjet::EnCours)->count();

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
                'projets_actifs' => $projetsAgentComptabletifsCount,
                'demandes_actives' => $demandesActives,
                'demandes_total' => $demandesTotal,
            ],
            'demandes_recentes' => $demandesRecentes,
            'projets_budget' => $projetsBudget,
            'faq_items' => $this->getFaqForRole(RoleUtilisateur::Porteur),
        ]);
    }

    /** @return list<array{id: int, question: string, reponse: string}> */
    private function getFaqForRole(RoleUtilisateur $role): array
    {
        return Faq::active()
            ->get()
            ->filter(fn (Faq $f) => $f->isVisibleFor($role))
            ->map(fn (Faq $f) => ['id' => $f->id_faq, 'question' => $f->faq_question, 'reponse' => $f->faq_reponse])
            ->values()
            ->toArray();
    }
}
