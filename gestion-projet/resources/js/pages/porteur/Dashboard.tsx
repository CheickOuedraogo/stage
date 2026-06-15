import {
    ArrowRightIcon,
    BanknotesIcon,
    ClipboardDocumentListIcon,
    FolderIcon,
} from '@heroicons/react/24/outline';
import { Head, Link, usePage } from '@inertiajs/react';
import { show as demandeShow } from '@/actions/App/Http/Controllers/Porteur/DemandeDepenseController';
import AppLayout from '@/components/layout/AppLayout';
import { FaqAccordion  } from '@/components/shared/FaqAccordion';
import type {FaqItem} from '@/components/shared/FaqAccordion';
import { formatCurrency } from '@/lib/utils';
import { index as demandesIndex } from '@/routes/porteur/demandes';
import { index as projetsIndex } from '@/routes/porteur/projets';
import type { PageProps } from '@/types';

interface Stats {
    projets_count: number;
    projets_actifs: number;
    demandes_actives: number;
    demandes_total: number;
}

interface Demande {
    id: number;
    objet: string;
    montant: number;
    statut: string;
    libelle_statut: string;
    badge_class: string;
    convention: string;
    projet: string;
    cree_le: string;
}

interface ProjetBudget {
    titre: string;
    statut: string;
    montant_estime: number;
    versements: number;
    depenses: number;
    disponible: number;
}

interface Props extends PageProps {
    stats: Stats;
    demandes_recentes: Demande[];
    projets_budget: ProjetBudget[];
    faq_items: FaqItem[];
}

const STATUS_COLORS: Record<string, string> = {
    en_cours: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    en_attente_financement: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    suspendu: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    termine: 'bg-slate-100 text-slate-700 dark:bg-slate-700/30 dark:text-slate-400',
    annule: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
};

const STATUS_LABELS: Record<string, string> = {
    en_cours: 'En cours',
    en_attente_financement: 'En attente',
    suspendu: 'Suspendu',
    termine: 'Terminé',
    annule: 'Annulé',
};

export default function PorteurDashboard() {
    const { auth, stats, demandes_recentes, projets_budget, faq_items } = usePage<Props>().props;
    const firstName = auth.user?.utilisateur_nom.split(' ').find((p) => !p.includes('.')) ?? auth.user?.utilisateur_nom;

    return (
        <AppLayout title="Tableau de bord">
            <Head title="Tableau de bord — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Bonjour, {firstName}</h2>
                <p className="text-sm text-slate-500 mt-1">Porteur de projet — Suivi de vos projets et demandes de dépense</p>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <Link href={projetsIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition-all">
                    <div className="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center mb-3">
                        <FolderIcon className="w-5 h-5 text-blue-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.projets_actifs}<span className="text-sm text-slate-400 font-normal ml-1">/ {stats.projets_count}</span></p>
                    <p className="text-xs text-slate-500 mt-0.5">Projets en cours</p>
                </Link>

                <Link href={demandesIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-amber-300 hover:shadow-sm transition-all">
                    <div className="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center mb-3">
                        <ClipboardDocumentListIcon className="w-5 h-5 text-amber-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.demandes_actives}<span className="text-sm text-slate-400 font-normal ml-1">en cours</span></p>
                    <p className="text-xs text-slate-500 mt-0.5">Demandes de dépense</p>
                </Link>

                <Link href={demandesIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-slate-300 hover:shadow-sm transition-all">
                    <div className="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center mb-3">
                        <ClipboardDocumentListIcon className="w-5 h-5 text-slate-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.demandes_total}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Total demandes</p>
                </Link>
            </div>

            {/* Budget par projet */}
            {projets_budget.length > 0 && (
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden mb-6">
                    <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <BanknotesIcon className="w-4 h-4 text-emerald-600" />
                            <h3 className="text-sm font-semibold text-slate-900 dark:text-white">Suivi budgétaire de vos projets</h3>
                        </div>
                        <Link href={projetsIndex.url()} className="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                            Voir tout <ArrowRightIcon className="w-3 h-3" />
                        </Link>
                    </div>
                    <div className="divide-y divide-gray-100 dark:divide-slate-800">
                        {projets_budget.map((p, i) => {
                            const taux = p.versements > 0
                                ? Math.min(100, Math.round((p.depenses / p.versements) * 100))
                                : 0;

                            return (
                                <div key={i} className="px-5 py-4">
                                    <div className="flex items-center justify-between mb-2">
                                        <div className="flex items-center gap-2 min-w-0">
                                            <span className="text-sm font-medium text-slate-900 dark:text-white truncate">{p.titre}</span>
                                            <span className={`shrink-0 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium ${STATUS_COLORS[p.statut] ?? ''}`}>
                                                {STATUS_LABELS[p.statut] ?? p.statut}
                                            </span>
                                        </div>
                                        <span className="text-xs font-mono text-emerald-600 font-semibold shrink-0 ml-2">{formatCurrency(p.disponible)} dispo.</span>
                                    </div>
                                    <div className="w-full h-2 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden mb-2">
                                        <div
                                            className={`h-full rounded-full transition-all ${taux >= 90 ? 'bg-red-500' : taux >= 70 ? 'bg-amber-500' : 'bg-blue-500'}`}
                                            style={{ width: `${taux}%` }}
                                        />
                                    </div>
                                    <div className="flex justify-between text-xs font-mono text-slate-500">
                                        <span>Dépensé : <span className="text-slate-700 dark:text-slate-300 font-semibold">{formatCurrency(p.depenses)}</span></span>
                                        <span>Reçu : <span className="text-slate-700 dark:text-slate-300 font-semibold">{formatCurrency(p.versements)}</span></span>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Demandes récentes */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-slate-900 dark:text-white">Mes dernières demandes</h3>
                    <Link href={demandesIndex.url()} className="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                        Voir tout <ArrowRightIcon className="w-3 h-3" />
                    </Link>
                </div>
                {demandes_recentes.length === 0 ? (
                    <div className="p-8 text-center">
                        <ClipboardDocumentListIcon className="w-10 h-10 text-slate-300 mx-auto mb-3" />
                        <p className="text-sm font-medium text-slate-700 dark:text-slate-300">Aucune demande pour le moment</p>
                        <p className="text-xs text-slate-500 mt-1">Accédez à une convention pour soumettre une demande de dépense.</p>
                        <Link href={projetsIndex.url()} className="inline-flex items-center gap-1.5 mt-4 text-sm text-blue-600 hover:text-blue-700 font-medium">
                            <FolderIcon className="w-4 h-4" /> Voir mes projets
                        </Link>
                    </div>
                ) : (
                    <div className="divide-y divide-gray-100 dark:divide-slate-800">
                        {demandes_recentes.map((d) => (
                            <Link
                                key={d.id}
                                href={demandeShow.url(d.id)}
                                className="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors"
                            >
                                <div className="min-w-0 flex-1">
                                    <p className="text-sm font-medium text-slate-900 dark:text-white truncate">{d.objet}</p>
                                    <p className="text-xs text-slate-500 mt-0.5 truncate">{d.projet} — {d.convention}</p>
                                </div>
                                <div className="shrink-0 text-right">
                                    <p className="text-sm font-mono font-semibold text-slate-900 dark:text-white">{formatCurrency(d.montant)}</p>
                                    <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-0.5 ${d.badge_class}`}>
                                        {d.libelle_statut}
                                    </span>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
            <FaqAccordion items={faq_items} />
        </AppLayout>
    );
}
