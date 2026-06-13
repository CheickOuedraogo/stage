import AppLayout from '@/components/layout/AppLayout';
import { ConfirmModal } from '@/components/ui/ConfirmModal';
import { clampPercent, conventionStatusClass, formatCurrency, formatDate } from '@/lib/utils';
import { store as storePaiementDirect, destroy as destroyPaiementDirect } from '@/routes/ac/projets/conventions/paiements-directs';
import { Head, useForm, router } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    BanknotesIcon,
    CalendarIcon,
    PlusIcon,
    TrashIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { useMemo, useState } from 'react';

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
    reference: string | null;
    description: string | null;
}

interface PaiementDirect {
    id: number;
    montant: number;
    objet_depense: string;
    date_paiement: string;
    rubrique: { libelle: string } | null;
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
    statut: string;
    libelle_statut: string;
    date_signature: string | null;
    date_debut: string | null;
    date_fin: string | null;
    bailleur: { nom: string; sigle: string; type: string | null; pays: string | null };
    total_rubriques: number;
    total_versements: number;
    rubriques: Rubrique[];
    versements: Versement[];
    paiements_directs: PaiementDirect[];
}

interface Props {
    projet: { id: number; titre: string };
    convention: Convention;
}

export default function AcConventionShow({ projet, convention }: Props) {
    const [showAddPaiementDirect, setShowAddPaiementDirect] = useState(false);
    const [confirmPaiementDirect, setConfirmPaiementDirect] = useState<number | null>(null);

    const paiementDirectForm = useForm({
        rubrique_id: '',
        montant: '',
        objet_depense: '',
        description: '',
        date_paiement: new Date().toISOString().split('T')[0],
    });

    const params = { projet: projet.id, convention: convention.id };

    const handleAddPaiementDirect = (e: React.FormEvent) => {
        e.preventDefault();
        paiementDirectForm.post(storePaiementDirect.url(params), {
            onSuccess: () => { paiementDirectForm.reset(); setShowAddPaiementDirect(false); },
        });
    };

    const budgetRestant = convention.montant_fcfa - convention.total_rubriques;
    const tauxRubriques = clampPercent(convention.total_rubriques, convention.montant_fcfa);
    const tauxVersements = clampPercent(convention.total_versements, convention.montant_fcfa);

    const { totalDepense, totalRestant } = useMemo(
        () => convention.rubriques.reduce(
            (acc, r) => ({ totalDepense: acc.totalDepense + r.montant_depense, totalRestant: acc.totalRestant + (r.montant_prevu - r.montant_depense) }),
            { totalDepense: 0, totalRestant: 0 }
        ),
        [convention.rubriques]
    );

    const totalConsomme = totalDepense + convention.paiements_directs.reduce((s, p) => s + p.montant, 0);
    const tauxConsommation = clampPercent(totalConsomme, convention.montant_fcfa);
    const fondsDisponibles = convention.total_versements - totalDepense;

    return (
        <AppLayout title={convention.titre}>
            <Head title={`${convention.titre} — AC — CIFEU`} />

            <div className="mb-6 flex items-center gap-2 text-sm">
                <button
                    type="button"
                    onClick={() => router.visit(`/ac/tableau-de-bord`)}
                    className="flex items-center gap-1.5 text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:text-white transition-colors"
                >
                    <ArrowLeftIcon className="w-3.5 h-3.5" />
                    Tableau de bord
                </button>
                <span className="text-gray-300 dark:text-slate-600">/</span>
                <span className="text-gray-900 dark:text-white font-medium truncate max-w-sm">{convention.titre}</span>
            </div>

            {/* Header card */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-6 mb-6">
                <div className="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <h2 className="text-xl font-bold text-gray-900 dark:text-white">{convention.titre}</h2>
                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${conventionStatusClass(convention.statut)}`}>
                                {convention.libelle_statut}
                            </span>
                        </div>
                        <p className="text-sm text-gray-600 dark:text-slate-400">
                            {convention.bailleur.nom}
                            {convention.bailleur.sigle && ` (${convention.bailleur.sigle})`}
                            {convention.bailleur.pays && ` — ${convention.bailleur.pays}`}
                        </p>
                    </div>
                    <div className="flex items-center gap-2 flex-wrap">
                        <div className="text-right mr-2">
                            <p className="text-xs text-gray-500 dark:text-slate-400">Forme</p>
                            <p className="text-sm font-medium text-gray-900 dark:text-white">{convention.forme_label}</p>
                        </div>
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
            <div className="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Montant convention</p>
                    <p className="font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(convention.montant_fcfa)}</p>
                    {convention.devise_origine !== 'XOF' && (
                        <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            {new Intl.NumberFormat('fr-FR').format(convention.montant)} {convention.devise_origine}
                        </p>
                    )}
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Budget alloué</p>
                    <p className="font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(convention.total_rubriques)}</p>
                    <div className="mt-1.5 w-full h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div className="h-full bg-blue-500 rounded-full transition-all" style={{ width: `${tauxRubriques}%` }} />
                    </div>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{tauxRubriques}% alloué</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Versements reçus</p>
                    <p className="font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(convention.total_versements)}</p>
                    <div className="mt-1.5 w-full h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div className="h-full bg-emerald-500 rounded-full transition-all" style={{ width: `${tauxVersements}%` }} />
                    </div>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{tauxVersements}% reçu</p>
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Budget consommé</p>
                    <p className="font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(totalConsomme)}</p>
                    <div className="mt-1.5 w-full h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div className="h-full bg-red-500 rounded-full transition-all" style={{ width: `${tauxConsommation}%` }} />
                    </div>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{tauxConsommation}% utilisé</p>
                </div>
                <div className={`rounded-xl p-4 border ${fondsDisponibles < 0 ? 'bg-red-50 dark:bg-red-900/20 border-red-200' : 'bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-700'}`}>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Disponible en caisse</p>
                    <p className={`font-mono font-bold ${fondsDisponibles < 0 ? 'text-red-600' : 'text-emerald-600'}`}>{formatCurrency(fondsDisponibles)}</p>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">réellement dépensable</p>
                </div>
            </div>

            <div className="space-y-6">
                {/* Rubriques (read-only) */}
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Rubriques budgétaires</h3>
                    </div>
                    {convention.rubriques.length === 0 ? (
                        <p className="p-8 text-sm text-gray-500 dark:text-slate-400 text-center">Aucune rubrique définie</p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="bg-gray-50 dark:bg-slate-800 border-b border-gray-100 dark:border-slate-800">
                                        <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Libellé</th>
                                        <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Prévu</th>
                                        <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Dépensé</th>
                                        <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Restant</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                    {convention.rubriques.map((r) => {
                                        const restant = r.montant_prevu - r.montant_depense;
                                        const pctDepense = clampPercent(r.montant_depense, r.montant_prevu);
                                        return (
                                            <tr key={r.id} className="hover:bg-gray-50 dark:hover:bg-slate-800">
                                                <td className="px-5 py-3">
                                                    <p className="text-sm text-gray-900 dark:text-white">{r.libelle}</p>
                                                    {r.description && <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{r.description}</p>}
                                                    {pctDepense > 0 && (
                                                        <div className="mt-1.5 w-32 h-1 bg-gray-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                            <div className="h-full bg-blue-500 rounded-full" style={{ width: `${pctDepense}%` }} />
                                                        </div>
                                                    )}
                                                </td>
                                                <td className="px-5 py-3 text-right font-mono text-sm text-gray-900 dark:text-white">
                                                    {formatCurrency(r.montant_prevu)}
                                                </td>
                                                <td className="px-5 py-3 text-right font-mono text-sm text-gray-600 dark:text-slate-400">
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
                                    <tr className="border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                        <td className="px-5 py-3 text-xs font-semibold text-gray-700 dark:text-slate-300">Total</td>
                                        <td className="px-5 py-3 text-right font-mono text-xs font-bold text-gray-900 dark:text-white">
                                            {formatCurrency(convention.total_rubriques)}
                                        </td>
                                        <td className="px-5 py-3 text-right font-mono text-xs font-bold text-gray-600 dark:text-slate-400">
                                            {formatCurrency(totalDepense)}
                                        </td>
                                        <td className="px-5 py-3 text-right font-mono text-xs font-bold text-emerald-600">
                                            {formatCurrency(totalRestant)}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    )}
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Versements (read-only) */}
                    <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                        <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Versements</h3>
                        </div>
                        {convention.versements.length === 0 ? (
                            <div className="p-8 text-center">
                                <p className="text-sm text-gray-500 dark:text-slate-400">Aucun versement enregistré</p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-800">
                                            <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Date</th>
                                            <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Référence</th>
                                            <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                        {convention.versements.map((v) => (
                                            <tr key={v.id} className="hover:bg-gray-50 dark:hover:bg-slate-800">
                                                <td className="px-5 py-3 text-gray-900 dark:text-white">{formatDate(v.date_reception)}</td>
                                                <td className="px-5 py-3 text-gray-600 dark:text-slate-400 font-mono text-xs">{v.reference ?? '—'}</td>
                                                <td className="px-5 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white">
                                                    {formatCurrency(v.montant)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr className="border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                            <td colSpan={2} className="px-5 py-3 text-xs font-semibold text-gray-700 dark:text-slate-300">Total reçu</td>
                                            <td className="px-5 py-3 text-right font-mono font-bold text-gray-900 dark:text-white">
                                                {formatCurrency(convention.total_versements)}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        )}
                    </div>

                    {/* Paiements directs bailleur — gérés par l'AC */}
                    <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                        <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                            <div className="flex items-center gap-2">
                                <BanknotesIcon className="w-4 h-4 text-amber-500" />
                                <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Paiements directs bailleur</h3>
                                <span className="text-xs text-gray-400 dark:text-slate-500">{convention.paiements_directs?.length ?? 0}</span>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowAddPaiementDirect((v) => !v)}
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors"
                            >
                                {showAddPaiementDirect ? <XMarkIcon className="w-3.5 h-3.5" /> : <PlusIcon className="w-3.5 h-3.5" />}
                                {showAddPaiementDirect ? 'Annuler' : 'Enregistrer'}
                            </button>
                        </div>

                        {showAddPaiementDirect && (
                            <form onSubmit={handleAddPaiementDirect} className="px-5 py-4 border-b border-amber-100 bg-amber-50 dark:bg-amber-900/20 grid grid-cols-1 gap-3">
                                <div>
                                    <label className="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Objet *</label>
                                    <input
                                        type="text"
                                        value={paiementDirectForm.data.objet_depense}
                                        onChange={(e) => paiementDirectForm.setData('objet_depense', e.target.value)}
                                        className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                                        placeholder="Objet du paiement direct"
                                        required
                                    />
                                    {paiementDirectForm.errors.objet_depense && <p className="text-xs text-red-600 mt-1">{paiementDirectForm.errors.objet_depense}</p>}
                                </div>
                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Montant (FCFA) *</label>
                                        <input
                                            type="number"
                                            min={1}
                                            value={paiementDirectForm.data.montant}
                                            onChange={(e) => paiementDirectForm.setData('montant', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                                            required
                                        />
                                        {paiementDirectForm.errors.montant && <p className="text-xs text-red-600 mt-1">{paiementDirectForm.errors.montant}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Date *</label>
                                        <input
                                            type="date"
                                            value={paiementDirectForm.data.date_paiement}
                                            max={new Date().toISOString().split('T')[0]}
                                            onChange={(e) => paiementDirectForm.setData('date_paiement', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                                            required
                                        />
                                        {paiementDirectForm.errors.date_paiement && <p className="text-xs text-red-600 mt-1">{paiementDirectForm.errors.date_paiement}</p>}
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Rubrique (optionnel)</label>
                                    <select
                                        value={paiementDirectForm.data.rubrique_id}
                                        onChange={(e) => paiementDirectForm.setData('rubrique_id', e.target.value)}
                                        className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                                    >
                                        <option value="">Aucune rubrique</option>
                                        {convention.rubriques.map((r) => (
                                            <option key={r.id} value={r.id}>{r.libelle}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={paiementDirectForm.processing}
                                        className="px-4 py-1.5 text-xs font-medium bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:opacity-50 transition-colors"
                                    >
                                        {paiementDirectForm.processing ? 'Enregistrement…' : 'Enregistrer le paiement'}
                                    </button>
                                </div>
                            </form>
                        )}

                        {!convention.paiements_directs?.length ? (
                            <p className="p-5 text-sm text-gray-400 dark:text-slate-500 text-center">Aucun paiement direct enregistré</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="bg-gray-50 dark:bg-slate-800 border-b border-gray-100 dark:border-slate-800">
                                            <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Date</th>
                                            <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Objet</th>
                                            <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Montant</th>
                                            <th className="px-5 py-3 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                        {convention.paiements_directs.map((p) => (
                                            <tr key={p.id} className="hover:bg-gray-50 dark:hover:bg-slate-800 group">
                                                <td className="px-5 py-3 text-gray-600 dark:text-slate-400 whitespace-nowrap text-xs">{formatDate(p.date_paiement)}</td>
                                                <td className="px-5 py-3">
                                                    <p className="text-gray-900 dark:text-white line-clamp-1">{p.objet_depense}</p>
                                                    <p className="text-[10px] text-gray-500 dark:text-slate-500">{p.rubrique?.libelle ?? 'Hors rubrique'}</p>
                                                </td>
                                                <td className="px-5 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white whitespace-nowrap">{formatCurrency(p.montant)}</td>
                                                <td className="px-5 py-3 text-right">
                                                    <button
                                                        type="button"
                                                        onClick={() => setConfirmPaiementDirect(p.id)}
                                                        className="opacity-0 group-hover:opacity-100 p-1 rounded text-gray-400 dark:text-slate-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                                                        aria-label="Supprimer le paiement direct"
                                                    >
                                                        <TrashIcon className="w-3.5 h-3.5" />
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr className="border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                            <td colSpan={2} className="px-5 py-3 text-xs font-semibold text-gray-700 dark:text-slate-300">Total direct</td>
                                            <td className="px-5 py-3 text-right font-mono font-bold text-gray-900 dark:text-white">
                                                {formatCurrency(convention.paiements_directs.reduce((s, p) => s + p.montant, 0))}
                                            </td>
                                            <td />
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        )}
                    </div>
                </div>

                {/* Description */}
                {convention.description && (
                    <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                        <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Description de la convention</h3>
                        </div>
                        <div className="p-5 text-sm text-gray-600 dark:text-slate-400 whitespace-pre-wrap">{convention.description}</div>
                    </div>
                )}
            </div>

            <ConfirmModal
                open={confirmPaiementDirect !== null}
                title="Supprimer le paiement direct"
                message="Ce paiement direct sera définitivement supprimé."
                confirmLabel="Supprimer"
                onConfirm={() => router.delete(destroyPaiementDirect.url({ ...params, paiementDirect: confirmPaiementDirect! }), { onSuccess: () => setConfirmPaiementDirect(null), onError: () => setConfirmPaiementDirect(null) })}
                onCancel={() => setConfirmPaiementDirect(null)}
            />
        </AppLayout>
    );
}
