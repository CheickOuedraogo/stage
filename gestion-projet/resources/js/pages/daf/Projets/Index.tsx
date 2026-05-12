import AppLayout from '@/components/layout/AppLayout';
import { formatCurrency, formatDate, projectStatusClass, truncate } from '@/lib/utils';
import { CHART_AXIS_TICK, CHART_MARGIN, CHART_TOOLTIP_STYLE } from '@/lib/charts';
import { index as dafProjetsIndex, show as dafProjetsShow } from '@/routes/daf/projets';
import { Head, router } from '@inertiajs/react';
import { FolderIcon } from '@heroicons/react/24/outline';
import { Bar, BarChart, CartesianGrid, Cell, Legend, Pie, PieChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

interface Projet {
    id: number;
    titre: string;
    porteur: string;
    status: string;
    status_label: string;
    montant_estime: number;
    montant_conventions: number;
    conventions_count: number;
    date_debut: string | null;
    date_fin_prevue: string | null;
}

interface Stats {
    total: number;
    en_cours: number;
    total_budget: number;
    total_conventions: number;
}

interface Props {
    projets: Projet[];
    stats: Stats;
}

const STATUS_COLORS: Record<string, string> = {
    en_cours: '#10b981',
    en_attente_financement: '#f59e0b',
    suspendu: '#f97316',
    termine: '#6b7280',
    annule: '#ef4444',
};

const CHART_COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899', '#ef4444', '#f97316', '#84cc16', '#14b8a6'];

function statusDistribution(projets: Projet[]) {
    const map: Record<string, { label: string; count: number; color: string }> = {};
    for (const p of projets) {
        if (!map[p.status]) {
            map[p.status] = { label: p.status_label, count: 0, color: STATUS_COLORS[p.status] ?? '#6b7280' };
        }
        map[p.status].count++;
    }
    return Object.values(map).filter((v) => v.count > 0);
}

function budgetChartData(projets: Projet[]) {
    return projets
        .filter((p) => p.montant_estime > 0)
        .slice(0, 8)
        .map((p) => ({
            name: truncate(p.titre, 18),
            'Budget estimé': p.montant_estime,
            'Conventions': p.montant_conventions,
        }));
}

export default function DafProjetsIndex({ projets, stats }: Props) {
    const pieData = statusDistribution(projets);
    const barData = budgetChartData(projets);

    return (
        <AppLayout title="Projets">
            <Head title="Projets — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Projets</h2>
                <p className="text-sm text-gray-500 dark:text-slate-400 mt-1">Vue d'ensemble de tous les projets</p>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Total projets</p>
                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{stats.total}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">En cours</p>
                    <p className="text-2xl font-bold text-emerald-600">{stats.en_cours}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Budget total estimé</p>
                    <p className="text-base font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(stats.total_budget)}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Total conventions</p>
                    <p className="text-base font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(stats.total_conventions)}</p>
                </div>
            </div>

            {/* Charts */}
            {projets.length > 0 && (
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
                    {/* Bar chart */}
                    <div className="lg:col-span-2 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Budget estimé vs Conventions signées</h3>
                        <ResponsiveContainer width="100%" height={220}>
                            <BarChart data={barData} margin={CHART_MARGIN}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
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

            {/* Table */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm" aria-label="Liste des projets">
                        <thead>
                            <tr className="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Projet</th>
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Porteur</th>
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Statut</th>
                                <th className="text-right px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Budget estimé</th>
                                <th className="text-right px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Conventions</th>
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Fin prévue</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                            {projets.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-6 py-16 text-center text-gray-400 dark:text-slate-500">
                                        <FolderIcon className="w-10 h-10 mx-auto mb-2 opacity-40" />
                                        <p className="font-medium">Aucun projet trouvé</p>
                                        <p className="text-xs mt-1">Modifiez les filtres ou attendez qu'un porteur soit associé à un projet.</p>
                                    </td>
                                </tr>
                            ) : (
                                projets.map((projet) => (
                                    <tr
                                        key={projet.id}
                                        className="hover:bg-blue-50 dark:hover:bg-blue-900/20 dark:bg-blue-900/20 transition-colors cursor-pointer"
                                        onClick={() => router.visit(dafProjetsShow.url(projet.id))}
                                    >
                                        <td className="px-6 py-4">
                                            <p className="font-medium text-gray-900 dark:text-white max-w-xs truncate">{projet.titre}</p>
                                        </td>
                                        <td className="px-6 py-4 text-gray-600 dark:text-slate-400 text-xs">{projet.porteur}</td>
                                        <td className="px-6 py-4">
                                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${projectStatusClass(projet.status)}`}>
                                                {projet.status_label}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right font-mono text-gray-900 dark:text-white text-xs">{formatCurrency(projet.montant_estime)}</td>
                                        <td className="px-6 py-4 text-right">
                                            <p className="font-mono text-xs text-gray-900 dark:text-white">{formatCurrency(projet.montant_conventions)}</p>
                                            <p className="text-xs text-gray-500 dark:text-slate-400">{projet.conventions_count} convention{projet.conventions_count !== 1 ? 's' : ''}</p>
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
            </div>
        </AppLayout>
    );
}
