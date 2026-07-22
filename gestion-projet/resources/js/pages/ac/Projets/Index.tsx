import { FolderIcon, MagnifyingGlassIcon } from '@heroicons/react/24/outline';
import { Head, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { Bar, BarChart, CartesianGrid, Cell, Legend, Pie, PieChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import AppLayout from '@/components/layout/AppLayout';
import { CHART_AXIS_TICK, CHART_MARGIN, CHART_TOOLTIP_STYLE } from '@/lib/charts';
import { formatCurrency, formatDate, projectStatusClass, truncate } from '@/lib/utils';
import { show as acProjetsShow, index as acProjetsIndex } from '@/routes/ac/projets';
import { Pagination } from '@/components/ui/Pagination';

interface Projet {
    id: number;
    titre: string;
    porteur: string;
    statut: string;
    libelle_statut: string;
    montant_estime: number;
    montant_conventions: number;
    total_versements: number;
    total_consomme: number;
    conventions_count: number;
    taux_execution: number;
    taux_financement: number;
    date_debut: string | null;
    date_fin_prevue: string | null;
}

interface Stats {
    total: number;
    en_cours: number;
    total_budget: number;
    total_conventions: number;
    total_consomme: number;
    taux_execution_global: number;
}

interface PaginatedProjets {
    data: Projet[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    projets: PaginatedProjets;
    stats: Stats;
    filters: { search?: string; statut?: string };
}

const STATUS_COLORS: Record<string, string> = {
    en_cours: '#10b981',
    en_attente_financement: '#f59e0b',
    suspendu: '#f97316',
    termine: '#6b7280',
    annule: '#ef4444',
};

const CHART_COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899', '#ef4444', '#f97316', '#84cc16', '#14b8a6'];

const STATUS_OPTIONS = [
    { value: '', label: 'Tous les statuts' },
    { value: 'en_cours', label: 'En cours' },
    { value: 'en_attente_financement', label: 'En attente de financement' },
    { value: 'suspendu', label: 'Suspendu' },
    { value: 'termine', label: 'Terminé' },
    { value: 'annule', label: 'Annulé' },
];

function statusDistribution(projetsList: Projet[]) {
    const map: Record<string, { label: string; count: number; color: string }> = {};

    for (const p of projetsList) {
        if (!map[p.statut]) {
            map[p.statut] = { label: p.libelle_statut, count: 0, color: STATUS_COLORS[p.statut] ?? '#6b7280' };
        }
        map[p.statut].count++;
    }

    return Object.values(map).filter((v) => v.count > 0);
}

function budgetChartData(projetsList: Projet[]) {
    return projetsList
        .filter((p) => p.montant_estime > 0)
        .slice(0, 8)
        .map((p) => ({
            name: truncate(p.titre, 18),
            'Budget estimé': p.montant_estime,
            'Conventions': p.montant_conventions,
        }));
}

export default function AcProjetsIndex({ projets, stats, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const applyFilter = (params: Record<string, string | undefined>) => {
        router.get(acProjetsIndex.url(), { ...filters, ...params }, { preserveState: true, preserveScroll: true });
    };

    // Dynamic search with 400ms debounce
    useEffect(() => {
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => {
            applyFilter({ search: search || undefined, page: undefined });
        }, 400);
        return () => { if (debounceRef.current) clearTimeout(debounceRef.current); };
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const pieData = statusDistribution(projets.data);
    const barData = budgetChartData(projets.data);

    return (
        <AppLayout title="Projets">
            <Head title="Projets — CIFEU" />

            <div className="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Projets</h2>
                    <p className="text-sm text-gray-500 dark:text-slate-400 mt-1">
                        Suivi des budgets et états des projets sous votre contrôle financier
                    </p>
                </div>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 transition-all hover:shadow-md">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Total projets</p>
                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{stats.total}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 transition-all hover:shadow-md">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">En cours</p>
                    <p className="text-2xl font-bold text-emerald-600">{stats.en_cours}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 transition-all hover:shadow-md">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Budget total estimé</p>
                    <p className="text-base font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(stats.total_budget)}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 transition-all hover:shadow-md">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Total conventions</p>
                    <p className="text-base font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(stats.total_conventions)}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 transition-all hover:shadow-md">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Total consommé</p>
                    <p className="text-base font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(stats.total_consomme)}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 transition-all hover:shadow-md">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Taux d'exécution global</p>
                    <p className="text-2xl font-bold text-blue-600 dark:text-blue-400">{stats.taux_execution_global}%</p>
                </div>
            </div>

            {/* Charts */}
            {projets.data.length > 0 && (
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
                    {/* Bar chart */}
                    <div className="lg:col-span-2 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Budget estimé vs Conventions signées</h3>
                        <ResponsiveContainer width="100%" height={220}>
                            <BarChart data={barData} margin={CHART_MARGIN}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" className="dark:stroke-slate-800" />
                                <XAxis dataKey="name" tick={CHART_AXIS_TICK} />
                                <YAxis tickFormatter={(v) => (v / 1_000_000).toFixed(0) + 'M'} tick={CHART_AXIS_TICK} width={40} />
                                <Tooltip
                                    formatter={(value) => [formatCurrency(Number(value)), '']}
                                    contentStyle={CHART_TOOLTIP_STYLE}
                                />
                                <Legend wrapperStyle={{ fontSize: 11 }} />
                                <Bar dataKey="Budget estimé" fill="#1e3a5f" radius={[3, 3, 0, 0]} />
                                <Bar dataKey="Conventions" fill="#3b82f6" radius={[3, 3, 0, 0]} />
                            </BarChart>
                        </ResponsiveContainer>
                    </div>

                    {/* Pie chart */}
                    <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Répartition par statut</h3>
                        <ResponsiveContainer width="100%" height={220}>
                            <PieChart>
                                <Pie data={pieData} dataKey="count" nameKey="label" cx="50%" cy="50%" outerRadius={70} innerRadius={35} paddingAngle={3}>
                                    {pieData.map((entry, index) => (
                                        <Cell key={entry.label} fill={entry.color ?? CHART_COLORS[index % CHART_COLORS.length]} />
                                    ))}
                                </Pie>
                                <Tooltip
                                    formatter={(value, name) => [`${Number(value)} projet${Number(value) !== 1 ? 's' : ''}`, String(name)]}
                                    contentStyle={CHART_TOOLTIP_STYLE}
                                />
                                <Legend wrapperStyle={{ fontSize: 11 }} />
                            </PieChart>
                        </ResponsiveContainer>
                    </div>
                </div>
            )}

            {/* Filters & Search */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 mb-6 flex flex-col sm:flex-row gap-3">
                <div className="flex-1 relative">
                    <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-slate-500" />
                    <input
                        type="search"
                        placeholder="Rechercher par titre de projet ou porteur…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        aria-label="Rechercher un projet"
                        className="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                    />
                </div>
                <select
                    value={filters.statut ?? ''}
                    onChange={(e) => applyFilter({ statut: e.target.value || undefined })}
                    aria-label="Filtrer par statut"
                    className="px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                >
                    {STATUS_OPTIONS.map((opt) => (
                        <option key={opt.value} value={opt.value}>{opt.label}</option>
                    ))}
                </select>
            </div>

            {/* Table */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm" aria-label="Liste des projets">
                        <thead>
                            <tr className="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Projet</th>
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Porteur</th>
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Statut</th>
                                <th className="text-right px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Budget estimé</th>
                                <th className="text-right px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Conventions</th>
                                <th className="text-center px-3 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Taux financ.</th>
                                <th className="text-center px-3 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Taux exéc.</th>
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Fin prévue</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                            {projets.data.length === 0 ? (
                                <tr>
                                    <td colSpan={8} className="px-6 py-16 text-center text-gray-400 dark:text-slate-500">
                                        <FolderIcon className="w-10 h-10 mx-auto mb-2 opacity-40" />
                                        <p className="font-medium">Aucun projet trouvé</p>
                                        <p className="text-xs mt-1">Modifiez les filtres de recherche.</p>
                                    </td>
                                </tr>
                            ) : (
                                projets.data.map((projet) => (
                                    <tr
                                        key={projet.id}
                                        className="hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors cursor-pointer"
                                        onClick={() => router.visit(acProjetsShow.url(projet.id))}
                                    >
                                        <td className="px-6 py-4">
                                            <p className="font-medium text-gray-900 dark:text-white max-w-xs truncate">{projet.titre}</p>
                                        </td>
                                        <td className="px-6 py-4 text-gray-600 dark:text-slate-400 text-xs">{projet.porteur}</td>
                                        <td className="px-6 py-4">
                                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${projectStatusClass(projet.statut)}`}>
                                                {projet.libelle_statut}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right font-mono text-gray-900 dark:text-white text-xs">{formatCurrency(projet.montant_estime)}</td>
                                        <td className="px-6 py-4 text-right">
                                            <p className="font-mono text-xs text-gray-900 dark:text-white">{formatCurrency(projet.montant_conventions)}</p>
                                            <p className="text-xs text-gray-500 dark:text-slate-400">{projet.conventions_count} convention{projet.conventions_count !== 1 ? 's' : ''}</p>
                                        </td>
                                        <td className="px-3 py-4 text-center text-xs hidden lg:table-cell">
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
                                                projet.taux_financement >= 100 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' :
                                                projet.taux_financement > 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
                                                'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
                                            }`}>
                                                {projet.taux_financement}%
                                            </span>
                                        </td>
                                        <td className="px-3 py-4 text-center text-xs hidden lg:table-cell">
                                            <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
                                                projet.taux_execution >= 75 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' :
                                                projet.taux_execution > 0 ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
                                                'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
                                            }`}>
                                                {projet.taux_execution}%
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-gray-600 dark:text-slate-400 text-xs hidden lg:table-cell">
                                            {projet.date_fin_prevue ? formatDate(projet.date_fin_prevue) : '—'}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination
                    currentPage={projets.current_page}
                    lastPage={projets.last_page}
                    total={projets.total}
                    links={projets.links}
                    perPage={10}
                />
            </div>
        </AppLayout>
    );
}
