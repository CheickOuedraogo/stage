import AppLayout from '@/components/layout/AppLayout';
import { Pagination } from '@/components/ui/Pagination';
import { formatCurrency, formatDate } from '@/lib/utils';
import { show as conventionShow } from '@/routes/daf/projets/conventions';
import { index as versementsIndex } from '@/routes/daf/versements';
import type { PageProps } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    BanknotesIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { FormEvent, useState } from 'react';

interface Versement {
    id: number;
    montant: number;
    date_reception: string;
    type: string;
    type_label: string;
    reference: string | null;
    description: string | null;
    convention: string;
    projet: string;
    bailleur: string;
    convention_id: number;
    projet_id: number;
}

interface PaginatedVersements {
    data: Versement[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Filters {
    type?: string;
    search?: string;
}

interface Props extends PageProps {
    versements: PaginatedVersements;
    total_montant: number;
    filters: Filters;
}

const TYPE_BADGE: Record<string, string> = {
    avance: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    tranche: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
};

export default function DafVersementsIndex() {
    const { versements, total_montant, filters } = usePage<Props>().props;
    const [search, setSearch] = useState(filters.search ?? '');

    const applyFilter = (key: string, value: string) => {
        router.get(versementsIndex.url(), { ...filters, [key]: value || undefined, page: undefined }, { preserveScroll: true });
    };

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        applyFilter('search', search);
    };

    const clearFilters = () => {
        setSearch('');
        router.get(versementsIndex.url(), {}, { preserveScroll: true });
    };

    const hasFilters = !!(filters.type || filters.search);

    return (
        <AppLayout title="Versements">
            <Head title="Versements — DAF — CIFEU" />

            <div className="mb-6 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Versements reçus</h2>
                    <p className="text-sm text-slate-500 mt-1">{versements.total} versement{versements.total !== 1 ? 's' : ''} enregistré{versements.total !== 1 ? 's' : ''}</p>
                </div>
                <div className="flex items-center gap-2 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3">
                    <BanknotesIcon className="w-4 h-4 text-emerald-600" />
                    <div>
                        <p className="text-xs text-slate-500">Total mobilisé</p>
                        <p className="text-sm font-bold font-mono text-emerald-600">{formatCurrency(total_montant)}</p>
                    </div>
                </div>
            </div>

            {/* Filtres */}
            <div className="flex flex-wrap gap-3 mb-4">
                <form onSubmit={handleSearch} className="flex gap-2">
                    <div className="relative">
                        <MagnifyingGlassIcon className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            type="text"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Convention, référence…"
                            className="pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 w-52"
                        />
                    </div>
                    <button type="submit" className="px-3 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                        Chercher
                    </button>
                </form>

                <select
                    value={filters.type ?? ''}
                    onChange={(e) => applyFilter('type', e.target.value)}
                    className="px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                >
                    <option value="">Tous les types</option>
                    <option value="avance">Avance</option>
                    <option value="tranche">Tranche</option>
                </select>

                {hasFilters && (
                    <button onClick={clearFilters} className="flex items-center gap-1.5 px-3 py-2 text-sm text-slate-600 dark:text-slate-400 border border-gray-200 dark:border-slate-700 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                        <XMarkIcon className="w-4 h-4" /> Réinitialiser
                    </button>
                )}
            </div>

            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                {versements.data.length === 0 ? (
                    <div className="p-12 text-center">
                        <BanknotesIcon className="w-10 h-10 text-slate-300 mx-auto mb-3" />
                        <p className="text-sm text-slate-500">Aucun versement trouvé.</p>
                    </div>
                ) : (
                    <>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/50">
                                        <th className="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Projet / Convention</th>
                                        <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Bailleur</th>
                                        <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Type</th>
                                        <th className="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Montant</th>
                                        <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                                        <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Référence</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                    {versements.data.map((v) => (
                                        <tr key={v.id} className="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <td className="px-5 py-3.5">
                                                <Link
                                                    href={conventionShow.url({ projet: v.projet_id, convention: v.convention_id })}
                                                    className="font-medium text-slate-900 dark:text-white hover:text-blue-600 truncate block max-w-[220px]"
                                                >
                                                    {v.convention}
                                                </Link>
                                                <p className="text-xs text-slate-500 mt-0.5 truncate max-w-[220px]">{v.projet}</p>
                                            </td>
                                            <td className="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-medium">{v.bailleur}</td>
                                            <td className="px-4 py-3.5">
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${TYPE_BADGE[v.type] ?? ''}`}>
                                                    {v.type_label}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3.5 text-right font-mono font-semibold text-emerald-600">{formatCurrency(v.montant)}</td>
                                            <td className="px-4 py-3.5 text-slate-600 dark:text-slate-400 whitespace-nowrap">{formatDate(v.date_reception)}</td>
                                            <td className="px-4 py-3.5 text-slate-400 font-mono text-xs hidden lg:table-cell">{v.reference ?? '—'}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            currentPage={versements.current_page}
                            lastPage={versements.last_page}
                            total={versements.total}
                            links={versements.links}
                            perPage={25}
                        />
                    </>
                )}
            </div>
        </AppLayout>
    );
}
