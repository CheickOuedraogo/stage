import AppLayout from '@/components/layout/AppLayout';
import { formatCurrency, projectStatusClass } from '@/lib/utils';
import { index as projetsIndex, show as projetsShow } from '@/routes/porteur/projets';
import { Head, Link, router } from '@inertiajs/react';
import { FolderOpenIcon, MagnifyingGlassIcon } from '@heroicons/react/24/outline';
import { useState } from 'react';

interface Projet {
    id: number;
    titre: string;
    status: string;
    status_label: string;
    montant_estime: number;
    date_debut: string | null;
    date_fin_prevue: string | null;
    conventions_count: number;
    montant_conventions: number;
    pourcentage_financement: number;
}

interface Props {
    projets: Projet[];
    filters: { search?: string; status?: string };
}

const STATUS_OPTIONS = [
    { value: '', label: 'Tous les statuts' },
    { value: 'en_cours', label: 'En cours' },
    { value: 'en_attente_financement', label: 'En attente de financement' },
    { value: 'suspendu', label: 'Suspendu' },
    { value: 'termine', label: 'Terminé' },
    { value: 'annule', label: 'Annulé' },
];

export default function ProjetsIndex({ projets, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');

    const applyFilter = (params: Record<string, string | undefined>) => {
        router.get(projetsIndex.url(), { ...filters, ...params }, { preserveState: true });
    };

    return (
        <AppLayout title="Mes Projets">
            <Head title="Mes Projets — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Mes Projets</h2>
                <p className="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    {projets.length} projet{projets.length !== 1 ? 's' : ''}
                </p>
            </div>

            {/* Filters */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 mb-6 flex flex-col sm:flex-row gap-3">
                <div className="flex-1 relative">
                    <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-slate-500" />
                    <input
                        type="search"
                        placeholder="Rechercher un projet…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onKeyDown={(e) => e.key === 'Enter' && applyFilter({ search })}
                        aria-label="Rechercher un projet"
                        className="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                    />
                </div>
                <select
                    value={filters.status ?? ''}
                    onChange={(e) => applyFilter({ status: e.target.value || undefined })}
                    aria-label="Filtrer par statut"
                    className="px-3 py-2.5 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                >
                    {STATUS_OPTIONS.map((opt) => (
                        <option key={opt.value} value={opt.value}>{opt.label}</option>
                    ))}
                </select>
            </div>

            {projets.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-20 text-center">
                    <FolderOpenIcon className="w-12 h-12 text-gray-300 dark:text-slate-600 mb-3" />
                    <p className="text-gray-500 dark:text-slate-400 font-medium">Aucun projet trouvé</p>
                    <p className="text-sm text-gray-400 dark:text-slate-500 mt-1">Ajustez vos filtres ou contactez l'administrateur.</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    {projets.map((projet) => (
                        <ProjetCard key={projet.id} projet={projet} />
                    ))}
                </div>
            )}
        </AppLayout>
    );
}

function ProjetCard({ projet }: { projet: Projet }) {
    return (
        <Link href={projetsShow.url(projet.id)} className="block group h-full">
            <div className="h-full flex flex-col bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5 hover:border-gray-300 dark:hover:border-slate-600 hover:shadow-sm transition-all">
                <div className="flex items-start justify-between gap-3 mb-4">
                    <h3 className="text-sm font-semibold text-gray-900 dark:text-white leading-snug group-hover:text-gray-700 dark:group-hover:text-slate-200 line-clamp-2">
                        {projet.titre}
                    </h3>
                    <span className={`shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${projectStatusClass(projet.status)}`}>
                        {projet.status_label}
                    </span>
                </div>

                <div className="mb-4">
                    <div className="flex justify-between text-xs text-gray-500 dark:text-slate-400 mb-1.5">
                        <span>Financement mobilisé</span>
                        <span className="font-medium text-gray-900 dark:text-white">{projet.pourcentage_financement}%</span>
                    </div>
                    <div className="w-full h-2 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div
                            className="h-full bg-blue-500 rounded-full transition-all"
                            style={{ width: `${projet.pourcentage_financement}%` }}
                            role="progressbar"
                            aria-valuenow={projet.pourcentage_financement}
                            aria-valuemin={0}
                            aria-valuemax={100}
                        />
                    </div>
                </div>

                <div className="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <p className="text-gray-400 dark:text-slate-500">Montant estimé</p>
                        <p className="font-mono font-medium text-gray-900 dark:text-white">{formatCurrency(projet.montant_estime)}</p>
                    </div>
                    <div>
                        <p className="text-gray-400 dark:text-slate-500">Conventions</p>
                        <p className="font-medium text-gray-900 dark:text-white">{projet.conventions_count}</p>
                    </div>
                </div>

                {projet.date_fin_prevue && (
                    <p className="mt-auto pt-3 text-xs text-gray-400 dark:text-slate-500">
                        Fin prévue : {new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(projet.date_fin_prevue))}
                    </p>
                )}
            </div>
        </Link>
    );
}
