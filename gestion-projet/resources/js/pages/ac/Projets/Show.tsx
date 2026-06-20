import { ArrowLeftIcon, DocumentChartBarIcon } from '@heroicons/react/24/outline';
import { Head, Link, router } from '@inertiajs/react';
import { Bar, BarChart, CartesianGrid, Legend, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import AppLayout from '@/components/layout/AppLayout';
import { MarkdownRenderer } from '@/components/ui/MarkdownRenderer';
import { CHART_AXIS_TICK, CHART_MARGIN, CHART_TOOLTIP_STYLE } from '@/lib/charts';
import { clampPercent, conventionStatusClass, formatCurrency, formatDate, projectStatusClass } from '@/lib/utils';
import { index as acProjetsIndex } from '@/routes/ac/projets';
import { show as acConventionShow } from '@/routes/ac/projets/conventions';

interface Convention {
    id: number;
    titre: string;
    bailleur: { nom: string; sigle: string };
    montant_fcfa: number;
    forme: string;
    forme_label: string;
    statut: string;
    libelle_statut: string;
    total_rubriques: number;
    total_versements: number;
    rubriques_count: number;
    versements_count: number;
}

interface AnalyseEcarts {
    budget_prevu: number;
    total_versements: number;
    total_depenses: number;
    ecart_budget: number;
    solde_caisse: number;
    taux_execution: number;
    date_fin_prevue: string | null;
    date_fin_reelle: string | null;
    ecart_temps_jours: number | null;
    ecart_temps_label: string | null;
}

interface Projet {
    id: number;
    titre: string;
    description: string | null;
    objectifs: string | null;
    statut: string;
    libelle_statut: string;
    statut_final: string | null;
    statut_final_label: string | null;
    montant_estime: number;
    montant_conventions: number;
    total_versements: number;
    date_debut: string | null;
    date_fin_prevue: string | null;
    date_fin_reelle: string | null;
    porteur: { nom: string; utilisateur_email: string; utilisateur_telephone: string | null };
    conventions: Convention[];
    analyse_ecarts: AnalyseEcarts;
    bilan_url: string | null;
}

interface Props {
    projet: Projet;
}

export default function AcProjetShow({ projet }: Props) {
    const totalConventions = projet.conventions.length;
    const totalRubriques = projet.conventions.reduce((s, c) => s + c.rubriques_count, 0);

    const conventionsChartData = projet.conventions.map((c) => ({
        name: (c.bailleur.sigle ?? c.bailleur.nom).slice(0, 12),
        'Montant': c.montant_fcfa,
        'Versé': c.total_versements,
    }));

    return (
        <AppLayout title={projet.titre}>
            <Head title={`${projet.titre} — AC — CIFEU`} />

            {/* Back + title */}
            <div className="mb-6 flex flex-col md:flex-row md:items-center gap-3 justify-between">
                <div className="flex items-center gap-3 min-w-0">
                    <Link
                        href={acProjetsIndex.url()}
                        className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 dark:bg-slate-800 text-gray-500 dark:text-slate-400 transition-colors"
                        aria-label="Retour à la liste des projets"
                    >
                        <ArrowLeftIcon className="w-4 h-4" />
                    </Link>
                    <div className="min-w-0">
                        <div className="flex items-center gap-3 flex-wrap">
                            <h2 className="text-xl font-bold text-gray-900 dark:text-white truncate">{projet.titre}</h2>
                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${projectStatusClass(projet.statut)}`}>
                                {projet.libelle_statut}
                            </span>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-slate-400 mt-0.5">
                            Porteur : {projet.porteur.nom}
                            {projet.porteur.utilisateur_telephone && ` · ${projet.porteur.utilisateur_telephone}`}
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-2 flex-wrap">
                    {projet.bilan_url && (
                        <Link
                            href={projet.bilan_url}
                            className="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-blue-400 hover:text-blue-700 transition-all"
                        >
                            <DocumentChartBarIcon className="w-4 h-4" />
                            Voir le bilan
                        </Link>
                    )}
                </div>
            </div>


            {/* Stats */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Budget estimé</p>
                    <p className="font-mono font-bold text-gray-900 dark:text-white text-sm">{formatCurrency(projet.montant_estime)}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Total conventions</p>
                    <p className="font-mono font-bold text-gray-900 dark:text-white text-sm">{formatCurrency(projet.montant_conventions)}</p>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{totalConventions} convention{totalConventions !== 1 ? 's' : ''}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Versements reçus</p>
                    <p className="font-mono font-bold text-gray-900 dark:text-white text-sm">{formatCurrency(projet.total_versements)}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Rubriques budgétaires</p>
                    <p className="text-2xl font-bold text-gray-900 dark:text-white">{totalRubriques}</p>
                </div>
            </div>

            {/* Dates */}
            {(projet.date_debut || projet.date_fin_prevue) && (
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 mb-6 flex flex-wrap gap-6 text-sm">
                    {projet.date_debut && (
                        <div>
                            <p className="text-xs text-gray-500 dark:text-slate-400">Début</p>
                            <p className="font-medium text-gray-900 dark:text-white">{formatDate(projet.date_debut)}</p>
                        </div>
                    )}
                    {projet.date_fin_prevue && (
                        <div>
                            <p className="text-xs text-gray-500 dark:text-slate-400">Fin prévue</p>
                            <p className="font-medium text-gray-900 dark:text-white">{formatDate(projet.date_fin_prevue)}</p>
                        </div>
                    )}
                    {projet.date_fin_reelle && (
                        <div>
                            <p className="text-xs text-gray-500 dark:text-slate-400">Terminé le</p>
                            <p className="font-medium text-gray-900 dark:text-white">{formatDate(projet.date_fin_reelle)}</p>
                        </div>
                    )}
                </div>
            )}

            {/* Convention chart */}
            {conventionsChartData.length > 0 && (
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5 mb-6 shadow-sm">
                    <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Conventions — Montant vs Versements reçus</h3>
                    <ResponsiveContainer width="100%" height={220}>
                        <BarChart data={conventionsChartData} margin={CHART_MARGIN}>
                            <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" className="dark:stroke-slate-800" />
                            <XAxis dataKey="name" tick={CHART_AXIS_TICK} />
                            <YAxis tickFormatter={(v) => (v / 1_000_000).toFixed(0) + 'M'} tick={CHART_AXIS_TICK} width={40} />
                            <Tooltip
                                formatter={(value) => [formatCurrency(Number(value)), '']}
                                contentStyle={CHART_TOOLTIP_STYLE}
                            />
                            <Legend wrapperStyle={{ fontSize: 11 }} />
                            <Bar dataKey="Montant" fill="#3b82f6" radius={[3, 3, 0, 0]} />
                            <Bar dataKey="Versé" fill="#10b981" radius={[3, 3, 0, 0]} />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            )}

            {/* Analyse des écarts */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden mb-6 shadow-sm">
                <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800">
                    <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Analyse des écarts (budget & délais)</h3>
                </div>
                <div className="p-5 grid grid-cols-2 lg:grid-cols-5 gap-4">
                    {/* Taux d'exécution */}
                    <div>
                        <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Taux d'exécution</p>
                        <p className={`text-2xl font-bold font-mono ${projet.analyse_ecarts.taux_execution >= 90 ? 'text-red-600' : projet.analyse_ecarts.taux_execution >= 70 ? 'text-amber-600' : 'text-emerald-600'}`}>
                            {projet.analyse_ecarts.taux_execution}%
                        </p>
                        <div className="mt-1.5 w-full h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div
                                className={`h-full rounded-full ${projet.analyse_ecarts.taux_execution >= 90 ? 'bg-red-500' : projet.analyse_ecarts.taux_execution >= 70 ? 'bg-amber-500' : 'bg-emerald-500'}`}
                                style={{ width: `${Math.min(100, projet.analyse_ecarts.taux_execution)}%` }}
                            />
                        </div>
                    </div>
                    {/* Écart budgétaire */}
                    <div>
                        <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Écart budgétaire</p>
                        <p className={`text-base font-bold font-mono ${projet.analyse_ecarts.ecart_budget >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                            {projet.analyse_ecarts.ecart_budget >= 0 ? '+' : ''}{formatCurrency(projet.analyse_ecarts.ecart_budget)}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            Reliquat budget prévu
                        </p>
                    </div>
                    {/* Solde Caisse */}
                    <div>
                        <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Solde Caisse</p>
                        <p className={`text-base font-bold font-mono ${projet.analyse_ecarts.solde_caisse >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>
                            {formatCurrency(projet.analyse_ecarts.solde_caisse)}
                        </p>
                        <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            Fonds disponibles en caisse
                        </p>
                    </div>
                    {/* Total dépensé */}
                    <div>
                        <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Total dépensé</p>
                        <p className="text-base font-bold font-mono text-gray-900 dark:text-white">{formatCurrency(projet.analyse_ecarts.total_depenses)}</p>
                        <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">sur {formatCurrency(projet.analyse_ecarts.budget_prevu)} prévu</p>
                    </div>
                    {/* Écart temporel */}
                    <div>
                        <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Délais</p>
                        {projet.analyse_ecarts.ecart_temps_label ? (
                            <>
                                <p className={`text-sm font-semibold ${(projet.analyse_ecarts.ecart_temps_jours ?? 0) > 0 ? 'text-red-600' : 'text-emerald-600'}`}>
                                    {projet.analyse_ecarts.ecart_temps_label}
                                </p>
                                {projet.analyse_ecarts.date_fin_reelle && (
                                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Clôturé le {formatDate(projet.analyse_ecarts.date_fin_reelle)}</p>
                                )}
                            </>
                        ) : (
                            <p className="text-sm text-gray-500 dark:text-slate-400">Date de fin non définie</p>
                        )}
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Description */}
                <div className="lg:col-span-2 space-y-5">
                    {projet.description && (
                        <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm">
                            <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800">
                                <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Description</h3>
                            </div>
                            <div className="p-5">
                                <MarkdownRenderer content={projet.description} />
                            </div>
                        </div>
                    )}
                    {projet.objectifs && (
                        <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm">
                            <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800">
                                <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Objectifs</h3>
                            </div>
                            <div className="p-5">
                                <MarkdownRenderer content={projet.objectifs} />
                            </div>
                        </div>
                    )}
                </div>

                {/* Conventions */}
                <div>
                    <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm">
                        <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Conventions</h3>
                            <span className="text-xs text-gray-500 dark:text-slate-400">{totalConventions}</span>
                        </div>
                        <div className="divide-y divide-gray-100 dark:divide-slate-800">
                            {projet.conventions.length === 0 ? (
                                <p className="p-5 text-sm text-gray-500 dark:text-slate-400 text-center">Aucune convention</p>
                            ) : (
                                projet.conventions.map((c) => (
                                    <div
                                        key={c.id}
                                        className="p-4 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors cursor-pointer"
                                        onClick={() => router.visit(acConventionShow.url({ projet: projet.id, convention: c.id }))}
                                        role="link"
                                        tabIndex={0}
                                        onKeyDown={(e) => e.key === 'Enter' && router.visit(acConventionShow.url({ projet: projet.id, convention: c.id }))}
                                        aria-label={`Voir la convention ${c.titre}`}
                                    >
                                        <div className="flex items-start justify-between gap-2 mb-1">
                                            <p className="text-xs font-semibold text-gray-900 dark:text-white">{c.bailleur.sigle ?? c.bailleur.nom}</p>
                                            <span className={`shrink-0 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium ${conventionStatusClass(c.statut)}`}>
                                                {c.libelle_statut}
                                            </span>
                                        </div>
                                        <p className="text-xs text-gray-600 dark:text-slate-400 mb-2 line-clamp-1">{c.titre}</p>
                                        <div className="grid grid-cols-2 gap-2 text-xs">
                                            <div>
                                                <p className="text-gray-500 dark:text-slate-400">Montant</p>
                                                <p className="font-mono font-medium text-gray-900 dark:text-white">{formatCurrency(c.montant_fcfa)}</p>
                                            </div>
                                            <div>
                                                <p className="text-gray-500 dark:text-slate-400">Versé</p>
                                                <p className="font-mono font-medium text-gray-900 dark:text-white">{formatCurrency(c.total_versements)}</p>
                                            </div>
                                        </div>
                                        {c.montant_fcfa > 0 && (
                                            <div className="mt-2 w-full h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                                <div
                                                    className="h-full bg-emerald-500 rounded-full"
                                                    style={{ width: `${clampPercent(c.total_versements, c.montant_fcfa)}%` }}
                                                />
                                            </div>
                                        )}
                                        <div className="mt-1.5 text-xs text-gray-500 dark:text-slate-400">
                                            {c.rubriques_count} rubrique{c.rubriques_count !== 1 ? 's' : ''} · {c.forme_label}
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
