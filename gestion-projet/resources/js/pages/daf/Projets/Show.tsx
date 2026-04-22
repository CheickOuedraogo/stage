import AppLayout from '@/components/layout/AppLayout';
import { MarkdownRenderer } from '@/components/ui/MarkdownRenderer';
import { clampPercent, conventionStatusClass, formatCurrency, formatDate, projectStatusClass } from '@/lib/utils';
import { CHART_AXIS_TICK, CHART_MARGIN, CHART_TOOLTIP_STYLE } from '@/lib/charts';
import { index as dafProjetsIndex } from '@/routes/daf/projets';
import { show as dafConventionShow } from '@/routes/daf/projets/conventions';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { Bar, BarChart, CartesianGrid, Legend, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

interface Convention {
    id: number;
    titre: string;
    bailleur: { nom: string; sigle: string };
    montant_fcfa: number;
    forme: string;
    forme_label: string;
    status: string;
    status_label: string;
    total_rubriques: number;
    total_versements: number;
    rubriques_count: number;
    versements_count: number;
}

interface Projet {
    id: number;
    titre: string;
    description: string | null;
    objectifs: string | null;
    status: string;
    status_label: string;
    montant_estime: number;
    montant_conventions: number;
    total_versements: number;
    date_debut: string | null;
    date_fin_prevue: string | null;
    date_fin_reelle: string | null;
    porteur: { nom: string; email: string; telephone: string | null };
    conventions: Convention[];
}

interface Props {
    projet: Projet;
}

export default function DafProjetShow({ projet }: Props) {
    const totalConventions = projet.conventions.length;
    const totalRubriques = projet.conventions.reduce((s, c) => s + c.rubriques_count, 0);

    const conventionsChartData = projet.conventions.map((c) => ({
        name: (c.bailleur.sigle ?? c.bailleur.nom).slice(0, 12),
        'Montant': c.montant_fcfa,
        'Versé': c.total_versements,
    }));

    return (
        <AppLayout title={projet.titre}>
            <Head title={`${projet.titre} — DAF — CIFEU`} />

            {/* Back + title */}
            <div className="mb-6 flex items-center gap-3">
                <Link
                    href={dafProjetsIndex.url()}
                    className="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors"
                    aria-label="Retour à la liste des projets"
                >
                    <ArrowLeftIcon className="w-4 h-4" />
                </Link>
                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-3 flex-wrap">
                        <h2 className="text-xl font-bold text-gray-900 truncate">{projet.titre}</h2>
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${projectStatusClass(projet.status)}`}>
                            {projet.status_label}
                        </span>
                    </div>
                    <p className="text-sm text-gray-600 mt-0.5">
                        Porteur : {projet.porteur.nom}
                        {projet.porteur.telephone && ` · ${projet.porteur.telephone}`}
                    </p>
                </div>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div className="bg-white border border-gray-200 rounded-xl p-4">
                    <p className="text-xs text-gray-500 mb-1">Budget estimé</p>
                    <p className="font-mono font-bold text-gray-900 text-sm">{formatCurrency(projet.montant_estime)}</p>
                </div>
                <div className="bg-white border border-gray-200 rounded-xl p-4">
                    <p className="text-xs text-gray-500 mb-1">Total conventions</p>
                    <p className="font-mono font-bold text-gray-900 text-sm">{formatCurrency(projet.montant_conventions)}</p>
                    <p className="text-xs text-gray-500 mt-0.5">{totalConventions} convention{totalConventions !== 1 ? 's' : ''}</p>
                </div>
                <div className="bg-white border border-gray-200 rounded-xl p-4">
                    <p className="text-xs text-gray-500 mb-1">Versements reçus</p>
                    <p className="font-mono font-bold text-gray-900 text-sm">{formatCurrency(projet.total_versements)}</p>
                </div>
                <div className="bg-white border border-gray-200 rounded-xl p-4">
                    <p className="text-xs text-gray-500 mb-1">Rubriques budgétaires</p>
                    <p className="text-2xl font-bold text-gray-900">{totalRubriques}</p>
                </div>
            </div>

            {/* Dates */}
            {(projet.date_debut || projet.date_fin_prevue) && (
                <div className="bg-white border border-gray-200 rounded-xl p-4 mb-6 flex flex-wrap gap-6 text-sm">
                    {projet.date_debut && (
                        <div>
                            <p className="text-xs text-gray-500">Début</p>
                            <p className="font-medium text-gray-900">{formatDate(projet.date_debut)}</p>
                        </div>
                    )}
                    {projet.date_fin_prevue && (
                        <div>
                            <p className="text-xs text-gray-500">Fin prévue</p>
                            <p className="font-medium text-gray-900">{formatDate(projet.date_fin_prevue)}</p>
                        </div>
                    )}
                    {projet.date_fin_reelle && (
                        <div>
                            <p className="text-xs text-gray-500">Terminé le</p>
                            <p className="font-medium text-gray-900">{formatDate(projet.date_fin_reelle)}</p>
                        </div>
                    )}
                </div>
            )}

            {/* Convention chart */}
            {conventionsChartData.length > 0 && (
                <div className="bg-white border border-gray-200 rounded-xl p-5 mb-6">
                    <h3 className="text-sm font-semibold text-gray-900 mb-4">Conventions — Montant vs Versements reçus</h3>
                    <ResponsiveContainer width="100%" height={220}>
                        <BarChart data={conventionsChartData} margin={CHART_MARGIN}>
                            <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                            <XAxis dataKey="name" tick={CHART_AXIS_TICK} />
                            <YAxis tickFormatter={(v) => (v / 1_000_000).toFixed(0) + 'M'} tick={CHART_AXIS_TICK} width={40} />
                            <Tooltip
                                formatter={(value: number) => [formatCurrency(value), '']}
                                contentStyle={CHART_TOOLTIP_STYLE}
                            />
                            <Legend wrapperStyle={{ fontSize: 11 }} />
                            <Bar dataKey="Montant" fill="#3b82f6" radius={[3, 3, 0, 0]} />
                            <Bar dataKey="Versé" fill="#10b981" radius={[3, 3, 0, 0]} />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            )}

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Description */}
                <div className="lg:col-span-2 space-y-5">
                    {projet.description && (
                        <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
                            <div className="px-5 py-3.5 border-b border-gray-100">
                                <h3 className="text-sm font-semibold text-gray-900">Description</h3>
                            </div>
                            <div className="p-5">
                                <MarkdownRenderer content={projet.description} />
                            </div>
                        </div>
                    )}
                    {projet.objectifs && (
                        <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
                            <div className="px-5 py-3.5 border-b border-gray-100">
                                <h3 className="text-sm font-semibold text-gray-900">Objectifs</h3>
                            </div>
                            <div className="p-5">
                                <MarkdownRenderer content={projet.objectifs} />
                            </div>
                        </div>
                    )}
                </div>

                {/* Conventions */}
                <div>
                    <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <div className="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                            <h3 className="text-sm font-semibold text-gray-900">Conventions</h3>
                            <span className="text-xs text-gray-500">{totalConventions}</span>
                        </div>
                        <div className="divide-y divide-gray-100">
                            {projet.conventions.length === 0 ? (
                                <p className="p-5 text-sm text-gray-500 text-center">Aucune convention</p>
                            ) : (
                                projet.conventions.map((c) => (
                                    <div
                                        key={c.id}
                                        className="p-4 hover:bg-blue-50 transition-colors cursor-pointer"
                                        onClick={() => router.visit(dafConventionShow.url({ projet: projet.id, convention: c.id }))}
                                        role="link"
                                        tabIndex={0}
                                        onKeyDown={(e) => e.key === 'Enter' && router.visit(dafConventionShow.url({ projet: projet.id, convention: c.id }))}
                                        aria-label={`Voir la convention ${c.titre}`}
                                    >
                                        <div className="flex items-start justify-between gap-2 mb-1">
                                            <p className="text-xs font-semibold text-gray-900">{c.bailleur.sigle ?? c.bailleur.nom}</p>
                                            <span className={`shrink-0 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium ${conventionStatusClass(c.status)}`}>
                                                {c.status_label}
                                            </span>
                                        </div>
                                        <p className="text-xs text-gray-600 mb-2 line-clamp-1">{c.titre}</p>
                                        <div className="grid grid-cols-2 gap-2 text-xs">
                                            <div>
                                                <p className="text-gray-500">Montant</p>
                                                <p className="font-mono font-medium text-gray-900">{formatCurrency(c.montant_fcfa)}</p>
                                            </div>
                                            <div>
                                                <p className="text-gray-500">Versé</p>
                                                <p className="font-mono font-medium text-gray-900">{formatCurrency(c.total_versements)}</p>
                                            </div>
                                        </div>
                                        {c.montant_fcfa > 0 && (
                                            <div className="mt-2 w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                <div
                                                    className="h-full bg-emerald-500 rounded-full"
                                                    style={{ width: `${clampPercent(c.total_versements, c.montant_fcfa)}%` }}
                                                />
                                            </div>
                                        )}
                                        <div className="mt-1.5 text-xs text-gray-500">
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
