import { BookOpenIcon, MagnifyingGlassIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { Head, Link, router, usePage } from '@inertiajs/react';
import type { FormEvent} from 'react';
import { useState } from 'react';
import AppLayout from '@/components/layout/AppLayout';
import { Pagination } from '@/components/ui/Pagination';
import { formatCurrency } from '@/lib/utils';
import { show as conventionShow } from '@/routes/daf/projets/conventions';
import { index as rubriquesIndex } from '@/routes/daf/rubriques';
import type { PageProps } from '@/types';

interface Rubrique {
    id: number;
    libelle: string;
    montant_prevu: number;
    consomme: number;
    disponible: number;
    taux: number;
    convention: string;
    projet: string;
    bailleur: string;
    convention_id: number;
    projet_id: number;
    description: string | null;
}

interface PaginatedRubriques {
    data: Rubrique[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Stats {
    total_prevu: number;
    total_rubriques: number;
}

interface Props extends PageProps {
    rubriques: PaginatedRubriques;
    stats: Stats;
    filters: { search?: string };
}

export default function DafRubriquesIndex() {
    const { rubriques, stats, filters } = usePage<Props>().props;
    const [search, setSearch] = useState(filters.search ?? '');

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get(rubriquesIndex.url(), { search: search || undefined }, { preserveScroll: true });
    };

    const clearFilters = () => {
        setSearch('');
        router.get(rubriquesIndex.url(), {}, { preserveScroll: true });
    };

    return (
        <AppLayout title="Rubriques budgétaires">
            <Head title="Rubriques — DAF — CIFEU" />

            <div className="mb-6 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Rubriques budgétaires</h2>
                    <p className="text-sm text-slate-500 mt-1">{stats.total_rubriques} rubrique{stats.total_rubriques !== 1 ? 's' : ''} — Budget total prévu : <span className="font-mono font-semibold text-slate-700 dark:text-slate-300">{formatCurrency(stats.total_prevu)}</span></p>
                </div>
            </div>

            {/* Filtre */}
            <div className="flex gap-3 mb-4">
                <form onSubmit={handleSearch} className="flex gap-2">
                    <div className="relative">
                        <MagnifyingGlassIcon className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Libellé, convention…"
                            className="pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 w-52"
                        />
                    </div>
                    <button type="submit" className="px-3 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                        Chercher
                    </button>
                </form>
                {filters.search && (
                    <button onClick={clearFilters} className="flex items-center gap-1.5 px-3 py-2 text-sm text-slate-600 dark:text-slate-400 border border-gray-200 dark:border-slate-700 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                        <XMarkIcon className="w-4 h-4" /> Réinitialiser
                    </button>
                )}
            </div>

            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                {rubriques.data.length === 0 ? (
                    <div className="p-12 text-center">
                        <BookOpenIcon className="w-10 h-10 text-slate-300 mx-auto mb-3" />
                        <p className="text-sm text-slate-500">Aucune rubrique trouvée.</p>
                    </div>
                ) : (
                    <>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/50">
                                        <th className="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Rubrique</th>
                                        <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Convention</th>
                                        <th className="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Prévu</th>
                                        <th className="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Consommé</th>
                                        <th className="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Disponible</th>
                                        <th className="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Taux</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                    {rubriques.data.map((r) => (
                                        <tr key={r.id} className="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <td className="px-5 py-3.5">
                                                <p className="font-medium text-slate-900 dark:text-white truncate max-w-[200px]">{r.libelle}</p>
                                                <p className="text-xs text-slate-500 mt-0.5 truncate max-w-[200px]">{r.projet} · {r.bailleur}</p>
                                            </td>
                                            <td className="px-4 py-3.5 hidden lg:table-cell">
                                                <Link
                                                    href={conventionShow.url({ projet: r.projet_id, convention: r.convention_id })}
                                                    className="text-xs text-blue-600 hover:text-blue-700 truncate block max-w-[180px]"
                                                >
                                                    {r.convention}
                                                </Link>
                                            </td>
                                            <td className="px-4 py-3.5 text-right font-mono text-slate-700 dark:text-slate-300">{formatCurrency(r.montant_prevu)}</td>
                                            <td className="px-4 py-3.5 text-right font-mono text-slate-700 dark:text-slate-300">{formatCurrency(r.consomme)}</td>
                                            <td className={`px-4 py-3.5 text-right font-mono font-semibold ${r.disponible === 0 ? 'text-red-600' : 'text-emerald-600'}`}>
                                                {formatCurrency(r.disponible)}
                                            </td>
                                            <td className="px-4 py-3.5 text-center">
                                                <div className="flex items-center justify-center gap-2">
                                                    <div className="w-16 h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                                        <div
                                                            className={`h-full rounded-full ${r.taux >= 100 ? 'bg-red-500' : r.taux >= 80 ? 'bg-amber-500' : 'bg-blue-500'}`}
                                                            style={{ width: `${Math.min(100, r.taux)}%` }}
                                                        />
                                                    </div>
                                                    <span className={`text-xs font-semibold ${r.taux >= 100 ? 'text-red-600' : r.taux >= 80 ? 'text-amber-600' : 'text-slate-600 dark:text-slate-400'}`}>
                                                        {r.taux}%
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            currentPage={rubriques.current_page}
                            lastPage={rubriques.last_page}
                            total={rubriques.total}
                            links={rubriques.links}
                            perPage={30}
                        />
                    </>
                )}
            </div>
        </AppLayout>
    );
}
