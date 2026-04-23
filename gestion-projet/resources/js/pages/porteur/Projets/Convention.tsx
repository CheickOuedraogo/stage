import AppLayout from '@/components/layout/AppLayout';
import { Button } from '@/components/ui/Button';
import { MarkdownRenderer } from '@/components/ui/MarkdownRenderer';
import { clampPercent, conventionStatusClass, formatCurrency, formatDate, versementTypeClass } from '@/lib/utils';
import { create as createDemande, store as storePaiementDirect } from '@/actions/App/Http/Controllers/Porteur/DemandeDepenseController';
import { store as storePaiementDirectAction } from '@/actions/App/Http/Controllers/Porteur/PaiementDirectController';
import { show as projetsShow } from '@/routes/porteur/projets';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeftIcon, BanknotesIcon, CalendarIcon, PlusIcon } from '@heroicons/react/24/outline';
import { useState } from 'react';

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
    has_demande_active: boolean;
}

export default function ConventionShow({ projet, convention, has_demande_active }: Props) {
    const tauxCouverture = clampPercent(convention.total_versements, convention.montant_fcfa);
    const tauxRubriques = clampPercent(convention.total_rubriques, convention.montant_fcfa);
    const totalDepense = convention.rubriques.reduce((s, r) => s + r.montant_depense, 0);

    const [showPaiementDirectForm, setShowPaiementDirectForm] = useState(false);
    const paiementDirectForm = useForm({
        rubrique_id: '',
        montant: '',
        objet_depense: '',
        description: '',
        date_paiement: new Date().toISOString().split('T')[0],
    });

    function handlePaiementDirect(e: React.FormEvent) {
        e.preventDefault();
        paiementDirectForm.post(
            storePaiementDirectAction.url({ projet: projet.id, convention: convention.id }),
            { onSuccess: () => { setShowPaiementDirectForm(false); paiementDirectForm.reset(); } }
        );
    }

    return (
        <AppLayout title={convention.titre}>
            <Head title={`${convention.titre} — CIFEU`} />

            {/* Breadcrumb */}
            <div className="mb-6 flex items-center gap-2 text-sm">
                <Link
                    href={projetsShow.url(projet.id)}
                    className="flex items-center gap-1.5 text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:text-white transition-colors"
                >
                    <ArrowLeftIcon className="w-3.5 h-3.5" />
                    {projet.titre}
                </Link>
                <span className="text-gray-300 dark:text-slate-600">/</span>
                <span className="text-gray-900 dark:text-white font-medium truncate max-w-sm">{convention.titre}</span>
            </div>

            {/* Header card */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-6 mb-6">
                <div className="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <h2 className="text-xl font-bold text-gray-900 dark:text-white">{convention.titre}</h2>
                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${conventionStatusClass(convention.status)}`}>
                                {convention.status_label}
                            </span>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-slate-400">
                            {convention.bailleur.nom}
                            {convention.bailleur.sigle && ` (${convention.bailleur.sigle})`}
                            {convention.bailleur.pays && ` — ${convention.bailleur.pays}`}
                        </p>
                    </div>
                    <div className="text-right">
                        <p className="text-xs text-gray-500 dark:text-slate-400">Forme</p>
                        <p className="text-sm font-medium text-gray-900 dark:text-white">{convention.forme_label}</p>
                    </div>
                </div>

                <div className="mt-4 flex flex-wrap gap-4 text-xs text-gray-600 dark:text-slate-400">
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
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Montant de la convention</p>
                    <p className="text-base font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(convention.montant_fcfa)}</p>
                    {convention.devise_origine !== 'XOF' && (
                        <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            {new Intl.NumberFormat('fr-FR').format(convention.montant)} {convention.devise_origine}
                            {' '}(taux : {convention.taux_conversion})
                        </p>
                    )}
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Budget alloué (rubriques)</p>
                    <p className="text-base font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(convention.total_rubriques)}</p>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{tauxRubriques}% du montant total</p>
                    <div className="mt-1.5 w-full h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div className="h-full bg-blue-500 rounded-full" style={{ width: `${tauxRubriques}%` }} />
                    </div>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Versements reçus</p>
                    <p className="text-base font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(convention.total_versements)}</p>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{tauxCouverture}% du montant</p>
                    <div className="mt-1.5 w-full h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div className="h-full bg-emerald-500 rounded-full" style={{ width: `${tauxCouverture}%` }} />
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Description */}
                {convention.description && (
                    <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                        <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Description</h3>
                        </div>
                        <div className="p-5">
                            <MarkdownRenderer content={convention.description} />
                        </div>
                    </div>
                )}

                {/* Bouton nouvelle demande */}
                <div className="lg:col-span-2 flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-gray-700 dark:text-slate-300">Actions</h3>
                    <div className="flex gap-3">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => setShowPaiementDirectForm(!showPaiementDirectForm)}
                        >
                            <BanknotesIcon className="w-4 h-4" />
                            Paiement direct
                        </Button>
                        <Link
                            href={createDemande.url({ projet: projet.id, convention: convention.id })}
                            className={`inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 ${
                                has_demande_active
                                    ? 'bg-slate-100 text-slate-400 cursor-not-allowed pointer-events-none dark:bg-slate-800 dark:text-slate-600'
                                    : 'bg-blue-600 hover:bg-blue-700 text-white motion-safe:hover:scale-[1.02] active:scale-[0.98]'
                            }`}
                            aria-disabled={has_demande_active}
                        >
                            <PlusIcon className="w-4 h-4" />
                            {has_demande_active ? 'Demande en cours' : 'Nouvelle demande'}
                        </Link>
                    </div>
                </div>

                {/* Formulaire paiement direct */}
                {showPaiementDirectForm && (
                    <div className="lg:col-span-2 bg-white dark:bg-slate-900 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Enregistrer un paiement direct du bailleur</h3>
                        <form onSubmit={handlePaiementDirect} className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                    Objet <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={paiementDirectForm.data.objet_depense}
                                    onChange={(e) => paiementDirectForm.setData('objet_depense', e.target.value)}
                                    className="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                    placeholder="Objet du paiement direct"
                                />
                                {paiementDirectForm.errors.objet_depense && <p className="mt-1 text-xs text-red-600">{paiementDirectForm.errors.objet_depense}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                    Montant (FCFA) <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    min={1}
                                    value={paiementDirectForm.data.montant}
                                    onChange={(e) => paiementDirectForm.setData('montant', e.target.value)}
                                    className="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-mono bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                />
                                {paiementDirectForm.errors.montant && <p className="mt-1 text-xs text-red-600">{paiementDirectForm.errors.montant}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                    Date <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    value={paiementDirectForm.data.date_paiement}
                                    max={new Date().toISOString().split('T')[0]}
                                    onChange={(e) => paiementDirectForm.setData('date_paiement', e.target.value)}
                                    className="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                />
                                {paiementDirectForm.errors.date_paiement && <p className="mt-1 text-xs text-red-600">{paiementDirectForm.errors.date_paiement}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                    Rubrique (optionnel)
                                </label>
                                <select
                                    value={paiementDirectForm.data.rubrique_id}
                                    onChange={(e) => paiementDirectForm.setData('rubrique_id', e.target.value)}
                                    className="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                >
                                    <option value="">Aucune rubrique</option>
                                    {convention.rubriques.map((r) => (
                                        <option key={r.id} value={r.id}>{r.libelle}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="sm:col-span-2 flex gap-3 justify-end pt-2">
                                <Button type="button" variant="ghost" onClick={() => setShowPaiementDirectForm(false)}>Annuler</Button>
                                <Button type="submit" loading={paiementDirectForm.processing}>
                                    <BanknotesIcon className="w-4 h-4" />
                                    Enregistrer
                                </Button>
                            </div>
                        </form>
                    </div>
                )}

                {/* Rubriques */}
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Rubriques budgétaires</h3>
                        <span className="text-xs text-gray-500 dark:text-slate-400">{convention.rubriques.length} rubrique{convention.rubriques.length !== 1 ? 's' : ''}</span>
                    </div>
                    {convention.rubriques.length === 0 ? (
                        <p className="p-5 text-sm text-gray-500 dark:text-slate-400 text-center">Aucune rubrique définie</p>
                    ) : (
                        <div className="divide-y divide-gray-100 dark:divide-slate-800">
                            {convention.rubriques.map((r) => {
                                const pct = r.montant_prevu > 0
                                    ? Math.min(100, Math.round((r.montant_depense / r.montant_prevu) * 100))
                                    : 0;
                                const restant = r.montant_prevu - r.montant_depense;
                                const overBudget = restant < 0;
                                return (
                                    <div key={r.id} className="px-5 py-4">
                                        <div className="flex items-start justify-between gap-4 mb-2">
                                            <div className="min-w-0">
                                                <p className="text-sm font-medium text-gray-900 dark:text-white truncate">{r.libelle}</p>
                                                {r.description && (
                                                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{r.description}</p>
                                                )}
                                            </div>
                                            <span className={`shrink-0 text-xs font-semibold tabular-nums px-2 py-0.5 rounded-full ${overBudget ? 'bg-red-100 text-red-700' : pct >= 80 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'}`}>
                                                {pct}%
                                            </span>
                                        </div>
                                        {/* Progress bar */}
                                        <div className="w-full h-2 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden mb-2">
                                            <div
                                                className={`h-full rounded-full transition-all duration-500 ${overBudget ? 'bg-red-500' : pct >= 80 ? 'bg-amber-500' : 'bg-blue-500'}`}
                                                style={{ width: `${Math.min(100, pct)}%` }}
                                                role="progressbar"
                                                aria-valuenow={pct}
                                                aria-valuemin={0}
                                                aria-valuemax={100}
                                                aria-label={`${r.libelle} : ${pct}% utilisé`}
                                            />
                                        </div>
                                        <div className="flex items-center justify-between text-xs text-gray-500 dark:text-slate-400 font-mono">
                                            <span>Dépensé : <span className="text-gray-700 dark:text-slate-300 font-semibold">{formatCurrency(r.montant_depense)}</span></span>
                                            <span>Prévu : <span className="text-gray-700 dark:text-slate-300">{formatCurrency(r.montant_prevu)}</span></span>
                                            <span className={overBudget ? 'text-red-600 font-semibold' : 'text-emerald-600 font-semibold'}>
                                                {overBudget ? '−' : '+'}{formatCurrency(Math.abs(restant))}
                                            </span>
                                        </div>
                                    </div>
                                );
                            })}
                            {/* Footer total */}
                            <div className="px-5 py-3 bg-gray-50 dark:bg-slate-800 flex items-center justify-between text-xs font-semibold text-gray-700 dark:text-slate-300">
                                <span>Total alloué</span>
                                <div className="flex items-center gap-6 font-mono">
                                    <span className="text-gray-500 dark:text-slate-400">Dépensé : <span className="text-gray-900 dark:text-white">{formatCurrency(totalDepense)}</span></span>
                                    <span>Prévu : <span className="text-gray-900 dark:text-white">{formatCurrency(convention.total_rubriques)}</span></span>
                                    <span className="text-emerald-600">{formatCurrency(convention.total_rubriques - totalDepense)}</span>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Versements */}
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden lg:col-span-2">
                    <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Historique des versements</h3>
                        <span className="text-xs text-gray-500 dark:text-slate-400">{convention.versements.length} versement{convention.versements.length !== 1 ? 's' : ''}</span>
                    </div>
                    {convention.versements.length === 0 ? (
                        <p className="p-8 text-sm text-gray-500 dark:text-slate-400 text-center">Aucun versement enregistré</p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-800">
                                        <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Date</th>
                                        <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Type</th>
                                        <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Référence</th>
                                        <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Montant</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                    {convention.versements.map((v) => (
                                        <tr key={v.id} className="hover:bg-gray-50 dark:hover:bg-slate-800">
                                            <td className="px-5 py-3 text-gray-900 dark:text-white">{formatDate(v.date_reception)}</td>
                                            <td className="px-5 py-3">
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${versementTypeClass(v.type)}`}>
                                                    {v.type_label}
                                                </span>
                                            </td>
                                            <td className="px-5 py-3 text-gray-600 dark:text-slate-400 font-mono text-xs">{v.reference ?? '—'}</td>
                                            <td className="px-5 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white">{formatCurrency(v.montant)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                                <tfoot>
                                    <tr className="border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                        <td colSpan={3} className="px-5 py-3 text-xs font-semibold text-gray-700 dark:text-slate-300">Total reçu</td>
                                        <td className="px-5 py-3 text-right font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(convention.total_versements)}</td>
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
