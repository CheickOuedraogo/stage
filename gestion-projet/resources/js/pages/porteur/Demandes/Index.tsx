import AppLayout from '@/components/layout/AppLayout';
import { EmptyState } from '@/components/ui/EmptyState';
import { demandeStatusClass, formatCurrency, formatDate } from '@/lib/utils';
import {
    index as demandesIndex,
    show as demandeShow,
} from '@/actions/App/Http/Controllers/Porteur/DemandeDepenseController';
import { index as projetsIndex } from '@/routes/porteur/projets';
import { Head, Link, router } from '@inertiajs/react';
import { ClipboardDocumentListIcon, FolderOpenIcon } from '@heroicons/react/24/outline';
import { useState } from 'react';

interface Demande {
    id: number;
    objet: string;
    montant: number;
    statut: string;
    libelle_statut: string;
    badge_class: string;
    cree_le: string;
    convention: { id: number; titre: string };
    projet: { id: number; titre: string };
    rubrique: { libelle: string };
}

interface Convention {
    id: number;
    titre: string;
    projet_titre: string;
}

interface Status {
    value: string;
    label: string;
}

interface Props {
    demandes: Demande[];
    conventions: Convention[];
    filters: { statut?: string; convention_id?: string };
    statuses: Status[];
}

export default function DemandesIndex({ demandes, conventions, filters, statuses }: Props) {
    const [statut, setStatut] = useState(filters.statut ?? '');
    const [conventionId, setConventionId] = useState(filters.convention_id ?? '');

    const applyFilter = (params: Record<string, string | undefined>) => {
        router.get(demandesIndex.url(), { ...filters, ...params }, { preserveState: true });
    };

    return (
        <AppLayout title="Mes Demandes">
            <Head title="Mes Demandes — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Mes Demandes</h2>
                <p className="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    {demandes.length} demande{demandes.length !== 1 ? 's' : ''} au total
                </p>
            </div>

            {/* Filtres */}
            <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-4 mb-6 flex flex-col sm:flex-row gap-3">
                <select
                    value={statut}
                    onChange={(e) => {
                        setStatut(e.target.value);
                        applyFilter({ statut: e.target.value || undefined });
                    }}
                    className="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                    aria-label="Filtrer par statut"
                >
                    <option value="">Tous les statuts</option>
                    {statuses.map((s) => (
                        <option key={s.value} value={s.value}>{s.label}</option>
                    ))}
                </select>

                <select
                    value={conventionId}
                    onChange={(e) => {
                        setConventionId(e.target.value);
                        applyFilter({ convention_id: e.target.value || undefined });
                    }}
                    className="flex-1 min-w-0 max-w-xs px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50 truncate"
                    aria-label="Filtrer par convention"
                >
                    <option value="">Toutes les conventions</option>
                    {conventions.map((c) => {
                        const label = `${c.projet_titre} — ${c.titre}`;
                        return (
                            <option key={c.id} value={c.id} title={label}>
                                {label.length > 60 ? label.slice(0, 57) + '…' : label}
                            </option>
                        );
                    })}
                </select>
            </div>

            {demandes.length === 0 ? (
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl">
                    <EmptyState
                        icon={ClipboardDocumentListIcon}
                        title="Aucune demande pour le moment"
                        description="Accédez à une convention depuis vos projets pour soumettre votre première demande de dépense."
                        action={
                            <Link
                                href={projetsIndex.url()}
                                className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition-colors"
                            >
                                <FolderOpenIcon className="w-4 h-4" />
                                Voir mes projets
                            </Link>
                        }
                    />
                </div>
            ) : (
                <div className="space-y-3">
                    {demandes.map((d) => (
                        <Link
                            key={d.id}
                            href={demandeShow.url(d.id)}
                            className="block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-5 hover:shadow-md transition-all duration-200 hover:border-blue-300 dark:hover:border-blue-700"
                        >
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex-1 min-w-0">
                                    <div className="flex items-center gap-2 mb-1 flex-wrap">
                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${d.badge_class}`}>
                                            {d.libelle_statut}
                                        </span>
                                        <span className="text-xs text-gray-400 dark:text-slate-500">
                                            {formatDate(d.cree_le)}
                                        </span>
                                    </div>
                                    <p className="font-medium text-gray-900 dark:text-white truncate">{d.objet}</p>
                                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                                        {d.projet.titre} · {d.convention.titre} · {d.rubrique.libelle}
                                    </p>
                                </div>
                                <span className="font-mono text-sm font-semibold text-gray-800 dark:text-slate-200 shrink-0">
                                    {formatCurrency(d.montant)}
                                </span>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </AppLayout>
    );
}
