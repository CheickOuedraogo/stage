import {
    ArrowRightIcon,
    BanknotesIcon,
    ChartBarIcon,
    ClipboardDocumentCheckIcon,
    ClipboardDocumentListIcon,
    FolderIcon,
} from '@heroicons/react/24/outline';
import { Head, Link, usePage } from '@inertiajs/react';
import { show as demandeShow } from '@/actions/App/Http/Controllers/Daf/DemandeDepenseController';
import AppLayout from '@/components/layout/AppLayout';
import { FaqAccordion  } from '@/components/shared/FaqAccordion';
import type {FaqItem} from '@/components/shared/FaqAccordion';
import { formatCurrency } from '@/lib/utils';
import { index as dafDemandesIndex } from '@/routes/daf/demandes';
import { index as dafProjetsIndex } from '@/routes/daf/projets';
import { index as dafRapportsIndex } from '@/routes/daf/rapports';
import type { PageProps } from '@/types';

interface Stats {
    projets_actifs: number;
    conventions_actives: number;
    demandes_en_attente: number;
    demandes_en_attente_ac: number;
    rapports_soumis: number;
    budget_total: number;
    versements_total: number;
}

interface Demande {
    id: number;
    objet: string;
    montant: number;
    statut: string;
    libelle_statut: string;
    badge_class: string;
    porteur: string;
    convention: string;
    cree_le: string;
}

interface Props extends PageProps {
    stats: Stats;
    demandes_recentes: Demande[];
    faq_items: FaqItem[];
}

export default function DafDashboard() {
    const { auth, stats, demandes_recentes, faq_items } = usePage<Props>().props;
    const firstName = auth.user?.utilisateur_nom.split(' ').find((p) => !p.includes('.')) ?? auth.user?.utilisateur_nom;
    const tauxMobilisation = stats.budget_total > 0
        ? Math.min(100, Math.round((stats.versements_total / stats.budget_total) * 100))
        : 0;

    return (
        <AppLayout title="Tableau de bord">
            <Head title="Tableau de bord DAF — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Bonjour, {firstName}</h2>
                <p className="text-sm text-slate-500 mt-1">Direction Administration et Finances — Suivi de la gestion financière</p>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <Link href={dafProjetsIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition-all group">
                    <div className="flex items-start justify-between mb-3">
                        <div className="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center">
                            <FolderIcon className="w-5 h-5 text-blue-600" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.projets_actifs}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Projets actifs</p>
                </Link>

                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <div className="flex items-start justify-between mb-3">
                        <div className="w-9 h-9 rounded-lg bg-violet-100 flex items-center justify-center">
                            <ChartBarIcon className="w-5 h-5 text-violet-600" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.conventions_actives}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Conventions actives</p>
                </div>

                <Link href={dafDemandesIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-amber-300 hover:shadow-sm transition-all group">
                    <div className="flex items-start justify-between mb-3">
                        <div className="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center">
                            <ClipboardDocumentListIcon className="w-5 h-5 text-amber-600" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.demandes_en_attente}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Demandes à valider</p>
                </Link>

                <Link href={dafDemandesIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-purple-300 hover:shadow-sm transition-all">
                    <div className="flex items-start justify-between mb-3">
                        <div className="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center">
                            <ClipboardDocumentCheckIcon className="w-5 h-5 text-purple-600" />
                        </div>
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.rapports_soumis}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Rapports à valider</p>
                </Link>
            </div>

            {/* Budget mobilisation */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5 mb-6">
                <div className="flex items-center justify-between mb-3">
                    <div className="flex items-center gap-2">
                        <BanknotesIcon className="w-4 h-4 text-emerald-600" />
                        <span className="text-sm font-semibold text-slate-700 dark:text-slate-300">Mobilisation budgétaire globale</span>
                    </div>
                    <span className="text-sm font-bold text-emerald-600">{tauxMobilisation}%</span>
                </div>
                <div className="w-full h-2.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden mb-3">
                    <div
                        className="h-full bg-emerald-500 rounded-full transition-all duration-700"
                        style={{ width: `${tauxMobilisation}%` }}
                    />
                </div>
                <div className="flex items-center justify-between text-xs font-mono text-slate-600">
                    <span>Versements reçus : <span className="font-bold text-slate-900 dark:text-white">{formatCurrency(stats.versements_total)}</span></span>
                    <span>Budget total : <span className="font-bold text-slate-900 dark:text-white">{formatCurrency(stats.budget_total)}</span></span>
                </div>
            </div>

            {/* Rapports — raccourci */}
            <div className="mb-6 flex items-center justify-between bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-5 py-3.5">
                <div className="flex items-center gap-2">
                    <ChartBarIcon className="w-4 h-4 text-indigo-500" />
                    <span className="text-sm text-slate-600 dark:text-slate-400">Graphiques et analyses budgétaires détaillées dans les Rapports</span>
                </div>
                <Link href={dafRapportsIndex.url()} className="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium shrink-0">
                    Voir les rapports <ArrowRightIcon className="w-3 h-3" />
                </Link>
            </div>

            {/* Demandes récentes */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-slate-900 dark:text-white">Demandes en attente de traitement</h3>
                    <Link href={dafDemandesIndex.url()} className="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                        Voir tout <ArrowRightIcon className="w-3 h-3" />
                    </Link>
                </div>
                {demandes_recentes.length === 0 ? (
                    <p className="p-8 text-center text-sm text-slate-500">Aucune demande en attente</p>
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
                                    <p className="text-xs text-slate-500 mt-0.5 truncate">{d.porteur} — {d.convention}</p>
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
