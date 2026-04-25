import AppLayout from '@/components/layout/AppLayout';
import { formatCurrency } from '@/lib/utils';
import { show as demandeShow } from '@/actions/App/Http/Controllers/Daf/DemandeDepenseController';
import { index as dafDemandesIndex } from '@/routes/daf/demandes';
import { index as dafProjetsIndex } from '@/routes/daf/projets';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRightIcon,
    BanknotesIcon,
    ChartBarIcon,
    ClipboardDocumentCheckIcon,
    ClipboardDocumentListIcon,
    FolderIcon,
} from '@heroicons/react/24/outline';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Legend,
    Line,
    LineChart,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

const PIE_COLORS = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4'];

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
    status: string;
    status_label: string;
    badge_class: string;
    porteur: string;
    convention: string;
    created_at: string;
}

interface ChartEntry {
    name: string;
    value: number;
}

interface PaiementMois {
    mois: string;
    total: number;
}

interface TopRubrique {
    libelle: string;
    consomme: number;
    prevu: number;
}

interface Props extends PageProps {
    stats: Stats;
    demandes_recentes: Demande[];
    versements_par_projet: ChartEntry[];
    paiements_par_mois: PaiementMois[];
    top_rubriques: TopRubrique[];
}

function formatMois(mois: string): string {
    const [year, month] = mois.split('-');
    const date = new Date(Number(year), Number(month) - 1);
    return date.toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' });
}

function ChartTooltipFCFA({ active, payload, label }: { active?: boolean; payload?: { name: string; value: number; color?: string }[]; label?: string }) {
    if (!active || !payload?.length) return null;
    return (
        <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 shadow-lg text-xs">
            {label && <p className="font-semibold text-slate-700 dark:text-slate-200 mb-1">{label}</p>}
            {payload.map((p, i) => (
                <p key={i} style={{ color: p.color }} className="font-mono">
                    {p.name} : {formatCurrency(p.value)}
                </p>
            ))}
        </div>
    );
}

function PieTooltipFCFA({ active, payload }: { active?: boolean; payload?: { name: string; value: number }[] }) {
    if (!active || !payload?.length) return null;
    return (
        <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 shadow-lg text-xs">
            <p className="font-semibold text-slate-700 dark:text-slate-200 mb-1 max-w-[160px] truncate">{payload[0].name}</p>
            <p className="font-mono text-blue-600">{formatCurrency(payload[0].value)}</p>
        </div>
    );
}

export default function DafDashboard() {
    const { auth, stats, demandes_recentes, versements_par_projet, paiements_par_mois, top_rubriques } = usePage<Props>().props;
    const firstName = auth.user?.name.split(' ').find((p) => !p.includes('.')) ?? auth.user?.name;
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

            {/* Graphiques */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                {/* Pie — Répartition des versements par projet */}
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5">
                    <h3 className="text-sm font-semibold text-slate-900 dark:text-white mb-4">Répartition des fonds par projet</h3>
                    {versements_par_projet.length === 0 ? (
                        <p className="text-xs text-slate-500 text-center py-10">Aucun versement enregistré</p>
                    ) : (
                        <ResponsiveContainer width="100%" height={220}>
                            <PieChart>
                                <Pie
                                    data={versements_par_projet}
                                    cx="50%"
                                    cy="50%"
                                    innerRadius={55}
                                    outerRadius={85}
                                    paddingAngle={3}
                                    dataKey="value"
                                >
                                    {versements_par_projet.map((_, index) => (
                                        <Cell key={index} fill={PIE_COLORS[index % PIE_COLORS.length]} />
                                    ))}
                                </Pie>
                                <Tooltip content={<PieTooltipFCFA />} />
                                <Legend
                                    formatter={(value) => <span className="text-xs text-slate-600 dark:text-slate-400">{value}</span>}
                                    iconSize={8}
                                    iconType="circle"
                                />
                            </PieChart>
                        </ResponsiveContainer>
                    )}
                </div>

                {/* Line — Évolution des paiements par mois */}
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5">
                    <h3 className="text-sm font-semibold text-slate-900 dark:text-white mb-4">Évolution des paiements (12 mois)</h3>
                    {paiements_par_mois.length === 0 ? (
                        <p className="text-xs text-slate-500 text-center py-10">Aucun paiement sur les 12 derniers mois</p>
                    ) : (
                        <ResponsiveContainer width="100%" height={220}>
                            <LineChart data={paiements_par_mois} margin={{ top: 5, right: 10, left: 0, bottom: 5 }}>
                                <CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" />
                                <XAxis
                                    dataKey="mois"
                                    tickFormatter={formatMois}
                                    tick={{ fontSize: 11, fill: '#94a3b8' }}
                                    tickLine={false}
                                    axisLine={false}
                                />
                                <YAxis
                                    tickFormatter={(v) => `${Math.round(v / 1_000_000)}M`}
                                    tick={{ fontSize: 11, fill: '#94a3b8' }}
                                    tickLine={false}
                                    axisLine={false}
                                    width={40}
                                />
                                <Tooltip content={<ChartTooltipFCFA />} />
                                <Line
                                    type="monotone"
                                    dataKey="total"
                                    name="Paiements"
                                    stroke="#3b82f6"
                                    strokeWidth={2}
                                    dot={{ r: 3, fill: '#3b82f6' }}
                                    activeDot={{ r: 5 }}
                                />
                            </LineChart>
                        </ResponsiveContainer>
                    )}
                </div>
            </div>

            {/* Bar — Top 5 rubriques consommées */}
            {top_rubriques.length > 0 && (
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5 mb-6">
                    <h3 className="text-sm font-semibold text-slate-900 dark:text-white mb-4">Top rubriques budgétaires les plus consommées</h3>
                    <ResponsiveContainer width="100%" height={200}>
                        <BarChart data={top_rubriques} layout="vertical" margin={{ top: 0, right: 20, left: 0, bottom: 0 }}>
                            <CartesianGrid strokeDasharray="3 3" horizontal={false} stroke="#e2e8f0" />
                            <XAxis
                                type="number"
                                tickFormatter={(v) => `${Math.round(v / 1_000_000)}M`}
                                tick={{ fontSize: 11, fill: '#94a3b8' }}
                                tickLine={false}
                                axisLine={false}
                            />
                            <YAxis
                                type="category"
                                dataKey="libelle"
                                width={130}
                                tick={{ fontSize: 11, fill: '#64748b' }}
                                tickLine={false}
                                axisLine={false}
                            />
                            <Tooltip content={<ChartTooltipFCFA />} />
                            <Bar dataKey="prevu" name="Prévu" fill="#e2e8f0" radius={[0, 4, 4, 0]} />
                            <Bar dataKey="consomme" name="Consommé" fill="#3b82f6" radius={[0, 4, 4, 0]} />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            )}

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
