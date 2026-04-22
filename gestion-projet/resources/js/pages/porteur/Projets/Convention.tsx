import AppLayout from '@/components/layout/AppLayout';
import { MarkdownRenderer } from '@/components/ui/MarkdownRenderer';
import { clampPercent, conventionStatusClass, formatCurrency, formatDate, versementTypeClass } from '@/lib/utils';
import { show as projetsShow } from '@/routes/porteur/projets';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon, CalendarIcon } from '@heroicons/react/24/outline';

interface Rubrique {
    id: number;
    libelle: string;
    montant_prevu: number;
    montant_depense: number;
    description: string | null;
}

interface Versement {
    id: number;
    montant: number;
    date_reception: string;
    type: string;
    type_label: string;
    reference: string | null;
    description: string | null;
}

interface Convention {
    id: number;
    titre: string;
    description: string | null;
    montant: number;
    montant_fcfa: number;
    devise_origine: string;
    taux_conversion: number;
    forme: string;
    forme_label: string;
    status: string;
    status_label: string;
    date_signature: string | null;
    date_debut: string | null;
    date_fin: string | null;
    bailleur: { nom: string; sigle: string; type: string | null; pays: string | null };
    total_rubriques: number;
    total_versements: number;
    rubriques: Rubrique[];
    versements: Versement[];
}

interface Props {
    projet: { id: number; titre: string };
    convention: Convention;
}

export default function ConventionShow({ projet, convention }: Props) {
    const tauxCouverture = clampPercent(convention.total_versements, convention.montant_fcfa);
    const tauxRubriques = clampPercent(convention.total_rubriques, convention.montant_fcfa);

    const totalDepense = convention.rubriques.reduce((s, r) => s + r.montant_depense, 0);

    return (
        <AppLayout title={convention.titre}>
            <Head title={`${convention.titre} — CIFEU`} />

            {/* Breadcrumb */}
            <div className="mb-6 flex items-center gap-2 text-sm">
                <Link
                    href={projetsShow.url(projet.id)}
                    className="flex items-center gap-1.5 text-gray-500 hover:text-gray-900 transition-colors"
                >
                    <ArrowLeftIcon className="w-3.5 h-3.5" />
                    {projet.titre}
                </Link>
                <span className="text-gray-300">/</span>
                <span className="text-gray-900 font-medium truncate max-w-sm">{convention.titre}</span>
            </div>

            {/* Header card */}
            <div className="bg-white border border-gray-200 rounded-xl p-6 mb-6">
                <div className="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <h2 className="text-xl font-bold text-gray-900">{convention.titre}</h2>
                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${conventionStatusClass(convention.status)}`}>
                                {convention.status_label}
                            </span>
                        </div>
                        <p className="text-sm text-gray-600">
                            {convention.bailleur.nom}
                            {convention.bailleur.sigle && ` (${convention.bailleur.sigle})`}
                            {convention.bailleur.pays && ` — ${convention.bailleur.pays}`}
                        </p>
                    </div>
                    <div className="text-right">
                        <p className="text-xs text-gray-500">Forme</p>
                        <p className="text-sm font-medium text-gray-900">{convention.forme_label}</p>
                    </div>
                </div>

                <div className="mt-4 flex flex-wrap gap-4 text-xs text-gray-600">
                    {convention.date_signature && (
                        <div className="flex items-center gap-1.5">
                            <CalendarIcon className="w-3.5 h-3.5" />
                            Signé le {formatDate(convention.date_signature)}
                        </div>
                    )}
                    {convention.date_debut && (
                        <div className="flex items-center gap-1.5">
                            <CalendarIcon className="w-3.5 h-3.5" />
                            Début : {formatDate(convention.date_debut)}
                        </div>
                    )}
                    {convention.date_fin && (
                        <div className="flex items-center gap-1.5">
                            <CalendarIcon className="w-3.5 h-3.5" />
                            Fin : {formatDate(convention.date_fin)}
                        </div>
                    )}
                </div>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div className="bg-white border border-gray-200 rounded-xl p-4">
                    <p className="text-xs text-gray-500 mb-1">Montant de la convention</p>
                    <p className="text-base font-mono font-bold text-gray-900">{formatCurrency(convention.montant_fcfa)}</p>
                    {convention.devise_origine !== 'XOF' && (
                        <p className="text-xs text-gray-500 mt-0.5">
                            {new Intl.NumberFormat('fr-FR').format(convention.montant)} {convention.devise_origine}
                            {' '}(taux : {convention.taux_conversion})
                        </p>
                    )}
                </div>
                <div className="bg-white border border-gray-200 rounded-xl p-4">
                    <p className="text-xs text-gray-500 mb-1">Budget alloué (rubriques)</p>
                    <p className="text-base font-mono font-bold text-gray-900">{formatCurrency(convention.total_rubriques)}</p>
                    <p className="text-xs text-gray-500 mt-0.5">{tauxRubriques}% du montant total</p>
                    <div className="mt-1.5 w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div className="h-full bg-blue-500 rounded-full" style={{ width: `${tauxRubriques}%` }} />
                    </div>
                </div>
                <div className="bg-white border border-gray-200 rounded-xl p-4">
                    <p className="text-xs text-gray-500 mb-1">Versements reçus</p>
                    <p className="text-base font-mono font-bold text-gray-900">{formatCurrency(convention.total_versements)}</p>
                    <p className="text-xs text-gray-500 mt-0.5">{tauxCouverture}% du montant</p>
                    <div className="mt-1.5 w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div className="h-full bg-emerald-500 rounded-full" style={{ width: `${tauxCouverture}%` }} />
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Description */}
                {convention.description && (
                    <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <div className="px-5 py-3.5 border-b border-gray-100">
                            <h3 className="text-sm font-semibold text-gray-900">Description</h3>
                        </div>
                        <div className="p-5">
                            <MarkdownRenderer content={convention.description} />
                        </div>
                    </div>
                )}

                {/* Rubriques */}
                <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div className="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                        <h3 className="text-sm font-semibold text-gray-900">Rubriques budgétaires</h3>
                        <span className="text-xs text-gray-500">{convention.rubriques.length} rubrique{convention.rubriques.length !== 1 ? 's' : ''}</span>
                    </div>
                    {convention.rubriques.length === 0 ? (
                        <p className="p-5 text-sm text-gray-500 text-center">Aucune rubrique définie</p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="bg-gray-50 border-b border-gray-100">
                                        <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600">Libellé</th>
                                        <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600">Prévu</th>
                                        <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600">Dépensé</th>
                                        <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600">Restant</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {convention.rubriques.map((r) => {
                                        const restant = r.montant_prevu - r.montant_depense;
                                        return (
                                            <tr key={r.id}>
                                                <td className="px-5 py-3">
                                                    <p className="text-sm text-gray-900">{r.libelle}</p>
                                                    {r.description && <p className="text-xs text-gray-500 mt-0.5">{r.description}</p>}
                                                </td>
                                                <td className="px-5 py-3 text-right font-mono text-sm text-gray-900">
                                                    {formatCurrency(r.montant_prevu)}
                                                </td>
                                                <td className="px-5 py-3 text-right font-mono text-sm text-gray-600">
                                                    {formatCurrency(r.montant_depense)}
                                                </td>
                                                <td className={`px-5 py-3 text-right font-mono text-sm font-semibold ${restant < 0 ? 'text-red-600' : 'text-emerald-600'}`}>
                                                    {formatCurrency(restant)}
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                                <tfoot>
                                    <tr className="border-t border-gray-200 bg-gray-50">
                                        <td className="px-5 py-3 text-xs font-semibold text-gray-700">Total alloué</td>
                                        <td className="px-5 py-3 text-right font-mono text-xs font-bold text-gray-900">
                                            {formatCurrency(convention.total_rubriques)}
                                        </td>
                                        <td className="px-5 py-3 text-right font-mono text-xs font-bold text-gray-600">
                                            {formatCurrency(totalDepense)}
                                        </td>
                                        <td className="px-5 py-3 text-right font-mono text-xs font-bold text-emerald-600">
                                            {formatCurrency(convention.total_rubriques - totalDepense)}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    )}
                </div>

                {/* Versements */}
                <div className="bg-white border border-gray-200 rounded-xl overflow-hidden lg:col-span-2">
                    <div className="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                        <h3 className="text-sm font-semibold text-gray-900">Historique des versements</h3>
                        <span className="text-xs text-gray-500">{convention.versements.length} versement{convention.versements.length !== 1 ? 's' : ''}</span>
                    </div>
                    {convention.versements.length === 0 ? (
                        <p className="p-8 text-sm text-gray-500 text-center">Aucun versement enregistré</p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b border-gray-100 bg-gray-50">
                                        <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600">Date</th>
                                        <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600">Type</th>
                                        <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600">Référence</th>
                                        <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600">Montant</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {convention.versements.map((v) => (
                                        <tr key={v.id} className="hover:bg-gray-50">
                                            <td className="px-5 py-3 text-gray-900">{formatDate(v.date_reception)}</td>
                                            <td className="px-5 py-3">
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${versementTypeClass(v.type)}`}>
                                                    {v.type_label}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3 text-gray-600 font-mono text-xs">{v.reference ?? '—'}</td>
                                            <td className="px-5 py-3 text-right font-mono font-semibold text-gray-900">{formatCurrency(v.montant)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                                <tfoot>
                                    <tr className="border-t border-gray-200 bg-gray-50">
                                        <td colSpan={3} className="px-5 py-3 text-xs font-semibold text-gray-700">Total reçu</td>
                                        <td className="px-5 py-3 text-right font-mono font-bold text-gray-900">{formatCurrency(convention.total_versements)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
