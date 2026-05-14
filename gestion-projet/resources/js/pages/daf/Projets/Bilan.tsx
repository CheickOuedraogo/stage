import AppLayout from '@/components/layout/AppLayout';
import { formatCurrency, formatDate } from '@/lib/utils';
import { Head } from '@inertiajs/react';
import { ArrowLeftIcon, DocumentArrowDownIcon, ExclamationTriangleIcon } from '@heroicons/react/24/outline';

interface Convention {
    id: number;
    titre: string;
    bailleur: string;
    bailleur_sigle: string;
    montant_fcfa: number;
    total_versements: number;
    total_depenses: number;
    total_paiements_directs: number;
    solde: number;
}

interface Demande {
    objet: string;
    montant: number;
    rubrique: string;
    convention: string;
    date_paiement: string | null;
}

interface PaiementDirect {
    objet: string;
    montant: number;
    rubrique: string | null;
    convention: string;
    date_paiement: string | null;
}

interface AnalyseEcarts {
    budget_initial: number;
    budget_prevu: number;
    total_versements: number;
    total_depenses: number;
    ecart_budget: number;
    taux_execution: number;
    conventions_depassent_budget_initial: boolean;
    ecart_temps_jours: number | null;
    ecart_temps_label: string | null;
}

interface Bilan {
    projet: {
        id: number;
        titre: string;
        porteur: string;
        status: string;
        status_label: string;
        date_debut: string | null;
        date_fin_prevue: string | null;
        date_fin_reelle: string | null;
    };
    conventions: Convention[];
    demandes: Demande[];
    paiements_directs: PaiementDirect[];
    analyse_ecarts: AnalyseEcarts;
}

interface Props {
    bilan: Bilan;
    pdf_url: string;
}

export default function BilanProjet({ bilan, pdf_url }: Props) {
    const { projet, conventions, demandes, paiements_directs, analyse_ecarts } = bilan;

    const ecartPositif = analyse_ecarts.ecart_budget >= 0;

    return (
        <AppLayout title={`Bilan — ${projet.titre}`}>
            <Head title={`Bilan de clôture — ${projet.titre}`} />

            {/* En-tête */}
            <div className="mb-6 flex items-start justify-between gap-4">
                <div className="flex items-center gap-3">
                    <button
                        type="button"
                        onClick={() => history.back()}
                        className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-500 dark:text-slate-400 transition-colors"
                        aria-label="Retour"
                    >
                        <ArrowLeftIcon className="w-4 h-4" />
                    </button>
                    <div>
                        <h2 className="text-xl font-bold text-gray-900 dark:text-white">Bilan de clôture</h2>
                        <p className="text-sm text-gray-500 dark:text-slate-400 mt-0.5">{projet.titre}</p>
                    </div>
                </div>
                <a
                    href={pdf_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors"
                >
                    <DocumentArrowDownIcon className="w-4 h-4" />
                    Exporter en PDF
                </a>
            </div>

            {/* Informations projet */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5 mb-6">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Informations du projet</h3>
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p className="text-xs text-gray-500 dark:text-slate-400">Porteur</p>
                        <p className="font-medium text-gray-900 dark:text-white">{projet.porteur}</p>
                    </div>
                    <div>
                        <p className="text-xs text-gray-500 dark:text-slate-400">Date de début</p>
                        <p className="font-medium text-gray-900 dark:text-white">{projet.date_debut ? formatDate(projet.date_debut) : '—'}</p>
                    </div>
                    <div>
                        <p className="text-xs text-gray-500 dark:text-slate-400">Date de fin prévue</p>
                        <p className="font-medium text-gray-900 dark:text-white">{projet.date_fin_prevue ? formatDate(projet.date_fin_prevue) : '—'}</p>
                    </div>
                    <div>
                        <p className="text-xs text-gray-500 dark:text-slate-400">Date de clôture</p>
                        <p className="font-medium text-gray-900 dark:text-white">{projet.date_fin_reelle ? formatDate(projet.date_fin_reelle) : '—'}</p>
                    </div>
                </div>
            </div>

            {/* Alerte dépassement budget initial */}
            {analyse_ecarts.conventions_depassent_budget_initial && (
                <div className="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                    <ExclamationTriangleIcon className="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" aria-hidden="true" />
                    <div className="text-sm">
                        <p className="font-semibold text-amber-800 dark:text-amber-300">Financement supérieur au budget initial</p>
                        <p className="mt-0.5 text-amber-700 dark:text-amber-400">
                            Le total des conventions ({formatCurrency(analyse_ecarts.budget_prevu)}) dépasse le budget initial estimé du projet ({formatCurrency(analyse_ecarts.budget_initial)}).
                            Écart : +{formatCurrency(analyse_ecarts.budget_prevu - analyse_ecarts.budget_initial)}.
                        </p>
                    </div>
                </div>
            )}

            {/* Synthèse financière */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Budget prévu</p>
                    <p className="font-mono font-bold text-blue-600 dark:text-blue-400 text-sm">{formatCurrency(analyse_ecarts.budget_prevu)}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Versements reçus</p>
                    <p className="font-mono font-bold text-gray-900 dark:text-white text-sm">{formatCurrency(analyse_ecarts.total_versements)}</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Total consommé</p>
                    <p className="font-mono font-bold text-gray-900 dark:text-white text-sm">{formatCurrency(analyse_ecarts.total_depenses)}</p>
                    <div className="mt-1.5 w-full h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div
                            className={`h-full rounded-full ${analyse_ecarts.taux_execution >= 90 ? 'bg-red-500' : analyse_ecarts.taux_execution >= 70 ? 'bg-amber-500' : 'bg-emerald-500'}`}
                            style={{ width: `${Math.min(100, analyse_ecarts.taux_execution)}%` }}
                        />
                    </div>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{analyse_ecarts.taux_execution}% du budget</p>
                </div>
                <div className={`border rounded-xl p-4 ${ecartPositif ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'}`}>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Écart budgétaire</p>
                    <p className={`font-mono font-bold text-sm ${ecartPositif ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400'}`}>
                        {ecartPositif ? '+' : ''}{formatCurrency(analyse_ecarts.ecart_budget)}
                    </p>
                    <p className={`text-xs mt-0.5 ${ecartPositif ? 'text-emerald-600 dark:text-emerald-500' : 'text-red-600 dark:text-red-500'}`}>
                        {ecartPositif ? 'Sous-consommation' : 'Dépassement'}
                    </p>
                </div>
            </div>

            {analyse_ecarts.ecart_temps_label && (
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 mb-6 flex items-center gap-3 text-sm">
                    <span className="text-gray-500 dark:text-slate-400">Délais :</span>
                    <span className={`font-semibold ${(analyse_ecarts.ecart_temps_jours ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'}`}>
                        {analyse_ecarts.ecart_temps_label}
                    </span>
                </div>
            )}

            {/* Tableau conventions */}
            {conventions.length > 0 && (
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden mb-6">
                    <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Conventions de financement</h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 dark:bg-slate-800">
                                <tr>
                                    <th className="text-left text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Bailleur</th>
                                    <th className="text-left text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Convention</th>
                                    <th className="text-right text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Montant prévu</th>
                                    <th className="text-right text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Versements reçus</th>
                                    <th className="text-right text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Dépenses</th>
                                    <th className="text-right text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Solde</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                {conventions.map((c) => (
                                    <tr key={c.id} className="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                                        <td className="px-4 py-3 font-medium text-gray-900 dark:text-white">{c.bailleur_sigle || c.bailleur}</td>
                                        <td className="px-4 py-3 text-gray-600 dark:text-slate-400 max-w-xs truncate">{c.titre}</td>
                                        <td className="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">{formatCurrency(c.montant_fcfa)}</td>
                                        <td className="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">{formatCurrency(c.total_versements)}</td>
                                        <td className="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">{formatCurrency(c.total_depenses + c.total_paiements_directs)}</td>
                                        <td className={`px-4 py-3 text-right font-mono font-medium ${c.solde >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'}`}>
                                            {c.solde >= 0 ? '+' : ''}{formatCurrency(c.solde)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot className="border-t-2 border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                <tr>
                                    <td colSpan={2} className="px-4 py-3 text-xs font-semibold text-gray-900 dark:text-white">Total</td>
                                    <td className="px-4 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white text-xs">{formatCurrency(conventions.reduce((s, c) => s + c.montant_fcfa, 0))}</td>
                                    <td className="px-4 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white text-xs">{formatCurrency(conventions.reduce((s, c) => s + c.total_versements, 0))}</td>
                                    <td className="px-4 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white text-xs">{formatCurrency(analyse_ecarts.total_depenses)}</td>
                                    <td className="px-4 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white text-xs">{formatCurrency(conventions.reduce((s, c) => s + c.solde, 0))}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            )}

            {/* Demandes terminées */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden mb-6">
                <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Demandes de dépense terminées</h3>
                    <span className="text-xs text-gray-500 dark:text-slate-400">{demandes.length}</span>
                </div>
                {demandes.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 dark:bg-slate-800">
                                <tr>
                                    <th className="text-left text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Objet</th>
                                    <th className="text-left text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Rubrique</th>
                                    <th className="text-left text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Convention</th>
                                    <th className="text-right text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Montant</th>
                                    <th className="text-left text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Date paiement</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                {demandes.map((d, i) => (
                                    <tr key={i} className="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                                        <td className="px-4 py-3 text-gray-900 dark:text-white">{d.objet}</td>
                                        <td className="px-4 py-3 text-gray-600 dark:text-slate-400">{d.rubrique}</td>
                                        <td className="px-4 py-3 text-gray-600 dark:text-slate-400">{d.convention}</td>
                                        <td className="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">{formatCurrency(d.montant)}</td>
                                        <td className="px-4 py-3 text-gray-600 dark:text-slate-400">{d.date_paiement ? formatDate(d.date_paiement) : '—'}</td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot className="border-t-2 border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                <tr>
                                    <td colSpan={3} className="px-4 py-3 text-xs font-semibold text-gray-900 dark:text-white">Total demandes</td>
                                    <td className="px-4 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white text-xs">{formatCurrency(demandes.reduce((s, d) => s + d.montant, 0))}</td>
                                    <td />
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                ) : (
                    <p className="p-5 text-sm text-gray-500 dark:text-slate-400 text-center">Aucune demande de dépense terminée.</p>
                )}
            </div>

            {/* Paiements directs */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden mb-6">
                <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Paiements directs</h3>
                    <span className="text-xs text-gray-500 dark:text-slate-400">{paiements_directs.length}</span>
                </div>
                {paiements_directs.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50 dark:bg-slate-800">
                                <tr>
                                    <th className="text-left text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Objet</th>
                                    <th className="text-left text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Rubrique</th>
                                    <th className="text-left text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Convention</th>
                                    <th className="text-right text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Montant</th>
                                    <th className="text-left text-xs font-medium text-gray-500 dark:text-slate-400 px-4 py-3">Date</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                {paiements_directs.map((p, i) => (
                                    <tr key={i} className="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                                        <td className="px-4 py-3 text-gray-900 dark:text-white">{p.objet}</td>
                                        <td className="px-4 py-3 text-gray-600 dark:text-slate-400">{p.rubrique ?? '—'}</td>
                                        <td className="px-4 py-3 text-gray-600 dark:text-slate-400">{p.convention}</td>
                                        <td className="px-4 py-3 text-right font-mono text-gray-900 dark:text-white">{formatCurrency(p.montant)}</td>
                                        <td className="px-4 py-3 text-gray-600 dark:text-slate-400">{p.date_paiement ? formatDate(p.date_paiement) : '—'}</td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot className="border-t-2 border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                <tr>
                                    <td colSpan={3} className="px-4 py-3 text-xs font-semibold text-gray-900 dark:text-white">Total paiements directs</td>
                                    <td className="px-4 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white text-xs">{formatCurrency(paiements_directs.reduce((s, p) => s + p.montant, 0))}</td>
                                    <td />
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                ) : (
                    <p className="p-5 text-sm text-gray-500 dark:text-slate-400 text-center">Aucun paiement direct enregistré.</p>
                )}
            </div>
        </AppLayout>
    );
}
