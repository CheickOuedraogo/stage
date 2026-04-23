import AppLayout from '@/components/layout/AppLayout';
import { formatCurrency } from '@/lib/utils';
import { show as demandeShow } from '@/actions/App/Http/Controllers/Porteur/DemandeDepenseController';
import { index as demandesIndex } from '@/routes/porteur/demandes';
import { index as projetsIndex } from '@/routes/porteur/projets';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRightIcon,
    ClipboardDocumentListIcon,
    FolderIcon,
    FolderOpenIcon,
} from '@heroicons/react/24/outline';

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
    status: string;
    status_label: string;
    badge_class: string;
    convention: string;
    projet: string;
    created_at: string;
}

interface Props extends PageProps {
    stats: Stats;
    demandes_recentes: Demande[];
}

export default function PorteurDashboard() {
    const { auth, stats, demandes_recentes } = usePage<Props>().props;
    const firstName = auth.user?.name.split(' ').find((p) => !p.includes('.')) ?? auth.user?.name;

    return (
        <AppLayout title="Tableau de bord">
            <Head title="Tableau de bord — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Bonjour, {firstName}</h2>
                <p className="text-sm text-slate-500 mt-1">Porteur de projet — Suivi de vos projets et demandes de dépense</p>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <Link href={projetsIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition-all">
                    <div className="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center mb-3">
                        <FolderIcon className="w-5 h-5 text-blue-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.projets_count}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Mes projets</p>
                </Link>

                <Link href={projetsIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-emerald-300 hover:shadow-sm transition-all">
                    <div className="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center mb-3">
                        <FolderOpenIcon className="w-5 h-5 text-emerald-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.projets_actifs}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Projets en cours</p>
                </Link>

                <Link href={demandesIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-amber-300 hover:shadow-sm transition-all">
                    <div className="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center mb-3">
                        <ClipboardDocumentListIcon className="w-5 h-5 text-amber-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.demandes_actives}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Demandes actives</p>
                </Link>

                <Link href={demandesIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-slate-300 hover:shadow-sm transition-all">
                    <div className="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center mb-3">
                        <ClipboardDocumentListIcon className="w-5 h-5 text-slate-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.demandes_total}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Demandes totales</p>
                </Link>
            </div>

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
                                        {d.status_label}
                                    </span>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
