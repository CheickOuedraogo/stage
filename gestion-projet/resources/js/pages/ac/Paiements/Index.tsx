import AppLayout from '@/components/layout/AppLayout';
import { formatCurrency, formatDate } from '@/lib/utils';
import { index as paiementsIndex } from '@/routes/ac/paiements';
import type { PageProps } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    BanknotesIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { FormEvent, useState } from 'react';

interface Paiement {
    id: number;
    montant: number;
    date_paiement: string;
    mode_paiement: string;
    mode_paiement_label: string;
    reference: string | null;
    objet: string;
    porteur: string;
    convention: string;
    projet: string;
    rubrique: string;
    enregistre_par: string;
}

interface PaginatedPaiements {
    data: Paiement[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Filters {
    mode?: string;
    search?: string;
}

interface Props extends PageProps {
    paiements: PaginatedPaiements;
    total_montant: number;
    filters: Filters;
}

const MODE_OPTIONS = [
    { value: '', label: 'Tous les modes' },
    { value: 'virement', label: 'Virement bancaire' },
    { value: 'cheque', label: 'Chèque' },
    { value: 'especes', label: 'Espèces' },
];

const MODE_BADGE: Record<string, string> = {
    virement: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    cheque: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
    especes: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
};

export default function AcPaiementsIndex() {
    const { paiements, total_montant, filters } = usePage<Props>().props;
    const [search, setSearch] = useState(filters.search ?? '');

    const applyFilter = (key: string, value: string) => {
        router.get(paiementsIndex.url(), { ...filters, [key]: value || undefined, page: undefined }, { preserveScroll: true });
    };

    const handleSearch = (e: FormEvent) => {
        e.preventDefault();
        applyFilter('search', search);
    };

    const clearFilters = () => {
        setSearch('');
        router.get(paiementsIndex.url(), {}, { preserveScroll: true });
    };

    const hasFilters = !!(filters.mode || filters.search);

    return (
        <AppLayout title="Paiements">
            <Head title="Paiements — Agent Comptable — CIFEU" />

            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Historique des paiements</h2>
                    <p className="text-sm text-slate-500 mt-1">{paiements.total} paiement{paiements.total !== 1 ? 's' : ''} enregistré{paiements.total !== 1 ? 's' : ''}</p>
                </div>
                <div className="flex items-center gap-2 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3">
                    <BanknotesIcon className="w-4 h-4 text-emerald-600" />
                    <div>
                        <p className="text-xs text-slate-500">Total décaissé</p>
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
                            placeholder="Rechercher par objet…"
                            className="pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 w-56"
                        />
                    </div>
                    <button type="submit" className="px-3 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                        Chercher
                    </button>
                </form>

                <select
                    value={filters.mode ?? ''}
                    onChange={(e) => applyFilter('mode', e.target.value)}
                    className="px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                >
                    {MODE_OPTIONS.map((o) => (
                        <option key={o.value} value={o.value}>{o.label}</option>
                    ))}
                </select>

                {hasFilters && (
                    <button onClick={clearFilters} className="flex items-center gap-1.5 px-3 py-2 text-sm text-slate-600 dark:text-slate-400 border border-gray-200 dark:border-slate-700 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
                        <XMarkIcon className="w-4 h-4" /> Réinitialiser
                    </button>
                )}
            </div>

            {/* Tableau */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                {paiements.data.length === 0 ? (
                    <div className="p-12 text-center">
                        <BanknotesIcon className="w-10 h-10 text-slate-300 mx-auto mb-3" />
                        <p className="text-sm text-slate-500">Aucun paiement trouvé.</p>
                    </div>
                ) : (
                    <>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/50">
                                        <th className="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Objet / Projet</th>
                                        <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Porteur</th>
                                        <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Mode</th>
                                        <th className="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Montant</th>
                                        <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Date</th>
                                        <th className="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Référence</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                    {paiements.data.map((p) => (
                                        <tr key={p.id} className="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <td className="px-5 py-3.5">
                                                <p className="font-medium text-slate-900 dark:text-white truncate max-w-[220px]">{p.objet}</p>
                                                <p className="text-xs text-slate-500 mt-0.5 truncate max-w-[220px]">{p.projet} — {p.rubrique}</p>
                                            </td>
                                            <td className="px-4 py-3.5 text-slate-700 dark:text-slate-300">{p.porteur}</td>
                                            <td className="px-4 py-3.5">
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${MODE_BADGE[p.mode_paiement] ?? ''}`}>
                                                    {p.mode_paiement_label}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3.5 text-right font-mono font-semibold text-emerald-600">{formatCurrency(p.montant)}</td>
                                            <td className="px-4 py-3.5 text-slate-600 dark:text-slate-400 whitespace-nowrap">{formatDate(p.date_paiement)}</td>
                                            <td className="px-4 py-3.5 text-slate-400 font-mono text-xs hidden lg:table-cell">{p.reference ?? '—'}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {paiements.last_page > 1 && (
                            <div className="px-5 py-3.5 border-t border-gray-100 dark:border-slate-800 flex items-center justify-between text-sm">
                                <p className="text-slate-500">
                                    Page {paiements.current_page} / {paiements.last_page}
                                </p>
                                <div className="flex gap-1">
                                    {paiements.links.map((link, i) => (
                                        link.url ? (
                                            <Link
                                                key={i}
                                                href={link.url}
                                                className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-colors ${
                                                    link.active
                                                        ? 'bg-blue-600 text-white'
                                                        : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800'
                                                }`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ) : (
                                            <span key={i} className="px-3 py-1.5 rounded-lg text-xs text-slate-300 dark:text-slate-600" dangerouslySetInnerHTML={{ __html: link.label }} />
                                        )
                                    ))}
                                </div>
                            </div>
                        )}
                    </>
                )}
            </div>
        </AppLayout>
    );
}
