import AppLayout from '@/components/layout/AppLayout';
import { MarkdownRenderer } from '@/components/ui/MarkdownRenderer';
import { clampPercent, conventionStatusClass, formatCurrency, formatDate, truncate, versementTypeClass } from '@/lib/utils';
import { CHART_PALETTE, CHART_TOOLTIP_STYLE } from '@/lib/charts';
import { show as dafProjetsShow } from '@/routes/daf/projets';
import { store as storeRubrique, update as updateRubrique, destroy as destroyRubrique } from '@/routes/daf/projets/conventions/rubriques';
import { store as storeVersement, destroy as destroyVersement } from '@/routes/daf/projets/conventions/versements';
import { Head, useForm, router } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    CalendarIcon,
    PencilSquareIcon,
    PlusIcon,
    TrashIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { useMemo, useState } from 'react';
import { Cell, Legend, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';

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

const TYPE_OPTIONS = [
    { value: 'tranche', label: 'Tranche' },
    { value: 'avance', label: 'Avance' },
    { value: 'solde', label: 'Solde' },
    { value: 'dotation', label: 'Dotation' },
];

export default function DafConventionShow({ projet, convention }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const [showAddRubrique, setShowAddRubrique] = useState(false);
    const [showAddVersement, setShowAddVersement] = useState(false);

    const rubriqueForm = useForm({ libelle: '', montant_prevu: '', description: '' });
    const addVersementForm = useForm({ montant: '', date_reception: '', type: 'tranche', reference: '', description: '' });

    const params = { projet: projet.id, convention: convention.id };

    const startEdit = (r: Rubrique) => {
        setShowAddRubrique(false);
        rubriqueForm.setData({ libelle: r.libelle, montant_prevu: String(r.montant_prevu), description: r.description ?? '' });
        setEditingId(r.id);
    };

    const openAddRubrique = () => {
        setEditingId(null);
        rubriqueForm.reset();
        setShowAddRubrique((v) => !v);
    };

    const handleAddRubrique = (e: React.FormEvent) => {
        e.preventDefault();
        rubriqueForm.post(storeRubrique.url(params), {
            onSuccess: () => { rubriqueForm.reset(); setShowAddRubrique(false); },
        });
    };

    const handleEditRubrique = (e: React.FormEvent, rubriqeId: number) => {
        e.preventDefault();
        rubriqueForm.patch(updateRubrique.url({ ...params, rubrique: rubriqeId }), {
            onSuccess: () => setEditingId(null),
        });
    };

    const handleDeleteRubrique = (rubriqeId: number) => {
        if (!confirm('Supprimer cette rubrique ?')) return;
        router.delete(destroyRubrique.url({ ...params, rubrique: rubriqeId }));
    };

    const handleAddVersement = (e: React.FormEvent) => {
        e.preventDefault();
        addVersementForm.post(storeVersement.url(params), {
            onSuccess: () => { addVersementForm.reset(); setShowAddVersement(false); },
        });
    };

    const handleDeleteVersement = (versementId: number) => {
        if (!confirm('Supprimer ce versement ?')) return;
        router.delete(destroyVersement.url({ ...params, versement: versementId }));
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

    const rubriquesPieData = useMemo(
        () => convention.rubriques.map((r) => ({ name: truncate(r.libelle, 22), value: r.montant_prevu })),
        [convention.rubriques]
    );

    return (
        <AppLayout title={convention.titre}>
            <Head title={`${convention.titre} — DAF — CIFEU`} />

            {/* Breadcrumb */}
            <div className="mb-6 flex items-center gap-2 text-sm">
                <button
                    type="button"
                    onClick={() => router.visit(dafProjetsShow.url(projet.id))}
                    className="flex items-center gap-1.5 text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:text-white transition-colors"
                >
                    <ArrowLeftIcon className="w-3.5 h-3.5" />
                    {projet.titre}
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
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Montant de la convention</p>
                    <p className="font-mono font-bold text-gray-900 dark:text-white">{formatCurrency(convention.montant_fcfa)}</p>
                    {convention.devise_origine !== 'XOF' && (
                        <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            {new Intl.NumberFormat('fr-FR').format(convention.montant)} {convention.devise_origine}
                        </p>
                    )}
                </div>
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Budget alloué (rubriques)</p>
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
                <div className={`rounded-xl p-4 border ${budgetRestant < 0 ? 'bg-red-50 dark:bg-red-900/20 border-red-200' : 'bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-700'}`}>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mb-1">Budget disponible</p>
                    <p className={`font-mono font-bold ${budgetRestant < 0 ? 'text-red-600' : 'text-gray-900 dark:text-white'}`}>{formatCurrency(budgetRestant)}</p>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">non encore alloué</p>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Left: rubriques + versements */}
                <div className="lg:col-span-2 space-y-6">

                    {/* Rubriques */}
                    <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                        <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Rubriques budgétaires</h3>
                            <button
                                type="button"
                                onClick={openAddRubrique}
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-gray-900 text-white rounded-lg hover:bg-gray-700 transition-colors"
                            >
                                {showAddRubrique ? <XMarkIcon className="w-3.5 h-3.5" /> : <PlusIcon className="w-3.5 h-3.5" />}
                                {showAddRubrique ? 'Annuler' : 'Ajouter'}
                            </button>
                        </div>

                        {/* Add form */}
                        {showAddRubrique && (
                            <form onSubmit={handleAddRubrique} className="px-5 py-4 border-b border-blue-100 bg-blue-50 dark:bg-blue-900/20">
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div className="sm:col-span-2">
                                        <label className="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Libellé *</label>
                                        <input
                                            type="text"
                                            value={rubriqueForm.data.libelle}
                                            onChange={(e) => rubriqueForm.setData('libelle', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500"
                                            placeholder="Ex: Matériel informatique"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Montant prévu (FCFA) *</label>
                                        <input
                                            type="number"
                                            min={1}
                                            value={rubriqueForm.data.montant_prevu}
                                            onChange={(e) => rubriqueForm.setData('montant_prevu', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500"
                                            placeholder="5000000"
                                            required
                                        />
                                    </div>
                                </div>
                                <div className="mt-2">
                                    <label className="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Description (optionnel)</label>
                                    <input
                                        type="text"
                                        value={rubriqueForm.data.description}
                                        onChange={(e) => rubriqueForm.setData('description', e.target.value)}
                                        className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500"
                                        placeholder="Description courte…"
                                    />
                                </div>
                                {rubriqueForm.errors.montant_prevu && (
                                    <p className="mt-1.5 text-xs text-red-600">{rubriqueForm.errors.montant_prevu}</p>
                                )}
                                <div className="mt-3 flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={rubriqueForm.processing}
                                        className="px-4 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                                    >
                                        {rubriqueForm.processing ? 'Enregistrement…' : 'Enregistrer'}
                                    </button>
                                </div>
                            </form>
                        )}

                        {/* Rubriques table */}
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
                                            <th className="px-5 py-3 w-20"></th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                        {convention.rubriques.map((r) => {
                                            const restant = r.montant_prevu - r.montant_depense;
                                            const pctDepense = clampPercent(r.montant_depense, r.montant_prevu);
                                            return editingId === r.id ? (
                                                <tr key={r.id} className="bg-amber-50 dark:bg-amber-900/20">
                                                    <td colSpan={5} className="px-5 py-3">
                                                        <form onSubmit={(e) => handleEditRubrique(e, r.id)} className="space-y-2">
                                                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                                <div className="sm:col-span-2">
                                                                    <input
                                                                        type="text"
                                                                        value={rubriqueForm.data.libelle}
                                                                        onChange={(e) => rubriqueForm.setData('libelle', e.target.value)}
                                                                        className="w-full px-3 py-1.5 text-sm border border-amber-300 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                                                                        required
                                                                    />
                                                                </div>
                                                                <div>
                                                                    <input
                                                                        type="number"
                                                                        min={1}
                                                                        value={rubriqueForm.data.montant_prevu}
                                                                        onChange={(e) => rubriqueForm.setData('montant_prevu', e.target.value)}
                                                                        className="w-full px-3 py-1.5 text-sm border border-amber-300 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                                                                        required
                                                                    />
                                                                </div>
                                                            </div>
                                                            <input
                                                                type="text"
                                                                value={rubriqueForm.data.description}
                                                                onChange={(e) => rubriqueForm.setData('description', e.target.value)}
                                                                placeholder="Description…"
                                                                className="w-full px-3 py-1.5 text-sm border border-amber-300 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                                                            />
                                                            {rubriqueForm.errors.montant_prevu && (
                                                                <p className="text-xs text-red-600">{rubriqueForm.errors.montant_prevu}</p>
                                                            )}
                                                            <div className="flex gap-2">
                                                                <button type="submit" disabled={rubriqueForm.processing} className="px-3 py-1 text-xs font-medium bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:opacity-50">
                                                                    {rubriqueForm.processing ? 'Sauvegarde…' : 'Sauvegarder'}
                                                                </button>
                                                                <button type="button" onClick={() => setEditingId(null)} className="px-3 py-1 text-xs font-medium text-gray-600 dark:text-slate-400 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800">
                                                                    Annuler
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </td>
                                                </tr>
                                            ) : (
                                                <tr key={r.id} className="hover:bg-gray-50 dark:hover:bg-slate-800 group">
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
                                                    <td className="px-5 py-3">
                                                        <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <button
                                                                type="button"
                                                                onClick={() => startEdit(r)}
                                                                className="p-1 rounded text-gray-400 dark:text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:bg-amber-900/20 transition-colors"
                                                                aria-label="Modifier la rubrique"
                                                            >
                                                                <PencilSquareIcon className="w-3.5 h-3.5" />
                                                            </button>
                                                            <button
                                                                type="button"
                                                                onClick={() => handleDeleteRubrique(r.id)}
                                                                className="p-1 rounded text-gray-400 dark:text-slate-500 hover:text-red-600 hover:bg-red-50 dark:bg-red-900/20 transition-colors"
                                                                aria-label="Supprimer la rubrique"
                                                            >
                                                                <TrashIcon className="w-3.5 h-3.5" />
                                                            </button>
                                                        </div>
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
                                            <td />
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        )}
                    </div>

                    {/* Versements */}
                    <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                        <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white">Versements</h3>
                            <button
                                type="button"
                                onClick={() => setShowAddVersement((v) => !v)}
                                className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                            >
                                {showAddVersement ? <XMarkIcon className="w-3.5 h-3.5" /> : <PlusIcon className="w-3.5 h-3.5" />}
                                {showAddVersement ? 'Annuler' : 'Enregistrer'}
                            </button>
                        </div>

                        {showAddVersement && (
                            <form onSubmit={handleAddVersement} className="px-5 py-4 border-b border-emerald-100 bg-emerald-50 dark:bg-emerald-900/20">
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Montant (FCFA) *</label>
                                        <input
                                            type="number"
                                            min={1}
                                            value={addVersementForm.data.montant}
                                            onChange={(e) => addVersementForm.setData('montant', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
                                            placeholder="10000000"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Date de réception *</label>
                                        <input
                                            type="date"
                                            value={addVersementForm.data.date_reception}
                                            onChange={(e) => addVersementForm.setData('date_reception', e.target.value)}
                                            max={new Date().toISOString().split('T')[0]}
                                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Type *</label>
                                        <select
                                            value={addVersementForm.data.type}
                                            onChange={(e) => addVersementForm.setData('type', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
                                        >
                                            {TYPE_OPTIONS.map((o) => (
                                                <option key={o.value} value={o.value}>{o.label}</option>
                                            ))}
                                        </select>
                                    </div>
                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Référence</label>
                                        <input
                                            type="text"
                                            value={addVersementForm.data.reference}
                                            onChange={(e) => addVersementForm.setData('reference', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
                                            placeholder="VRS-XXXX-####"
                                        />
                                    </div>
                                </div>
                                {addVersementForm.errors.montant && (
                                    <p className="mt-1.5 text-xs text-red-600">{addVersementForm.errors.montant}</p>
                                )}
                                {addVersementForm.errors.date_reception && (
                                    <p className="mt-1.5 text-xs text-red-600">{addVersementForm.errors.date_reception}</p>
                                )}
                                <div className="mt-3 flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={addVersementForm.processing}
                                        className="px-4 py-1.5 text-xs font-medium bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                                    >
                                        {addVersementForm.processing ? 'Enregistrement…' : 'Enregistrer le versement'}
                                    </button>
                                </div>
                            </form>
                        )}

                        {convention.versements.length === 0 && !showAddVersement && (
                            <div className="p-8 text-center">
                                <p className="text-sm text-gray-500 dark:text-slate-400">Aucun versement enregistré</p>
                            </div>
                        )}

                        {convention.versements.length > 0 && (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-800">
                                            <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Date</th>
                                            <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Type</th>
                                            <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Référence</th>
                                            <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Montant</th>
                                            <th className="px-5 py-3 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                        {convention.versements.map((v) => (
                                            <tr key={v.id} className="hover:bg-gray-50 dark:hover:bg-slate-800 group">
                                                <td className="px-5 py-3 text-gray-900 dark:text-white">{formatDate(v.date_reception)}</td>
                                                <td className="px-5 py-3">
                                                    <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${versementTypeClass(v.type)}`}>
                                                        {v.type_label}
                                                    </span>
                                                </td>
                                                <td className="px-5 py-3 text-gray-600 dark:text-slate-400 font-mono text-xs">{v.reference ?? '—'}</td>
                                                <td className="px-5 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white">
                                                    {formatCurrency(v.montant)}
                                                </td>
                                                <td className="px-5 py-3">
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDeleteVersement(v.id)}
                                                        className="opacity-0 group-hover:opacity-100 p-1 rounded text-gray-400 dark:text-slate-500 hover:text-red-600 hover:bg-red-50 dark:bg-red-900/20 transition-all"
                                                        aria-label="Supprimer le versement"
                                                    >
                                                        <TrashIcon className="w-3.5 h-3.5" />
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr className="border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                            <td colSpan={3} className="px-5 py-3 text-xs font-semibold text-gray-700 dark:text-slate-300">Total reçu</td>
                                            <td className="px-5 py-3 text-right font-mono font-bold text-gray-900 dark:text-white">
                                                {formatCurrency(convention.total_versements)}
                                            </td>
                                            <td />
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        )}
                    </div>
                </div>

                {/* Right: chart + description */}
                <div className="space-y-5">
                    {rubriquesPieData.length > 0 && (
                        <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-3">Répartition budgétaire</h3>
                            <ResponsiveContainer width="100%" height={200}>
                                <PieChart>
                                    <Pie data={rubriquesPieData} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={70} innerRadius={30} paddingAngle={2}>
                                        {rubriquesPieData.map((_, index) => (
                                            <Cell key={index} fill={CHART_PALETTE[index % CHART_PALETTE.length]} />
                                        ))}
                                    </Pie>
                                    <Tooltip
                                        formatter={(value) => [formatCurrency(Number(value)), '']}
                                        contentStyle={CHART_TOOLTIP_STYLE}
                                    />
                                </PieChart>
                            </ResponsiveContainer>
                            <div className="mt-2 space-y-1">
                                {rubriquesPieData.map((r, i) => (
                                    <div key={i} className="flex items-center gap-2 text-xs text-gray-600 dark:text-slate-400">
                                        <span className="w-2.5 h-2.5 rounded-full shrink-0" style={{ backgroundColor: CHART_PALETTE[i % CHART_PALETTE.length] }} />
                                        <span className="truncate">{r.name}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

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
                </div>
            </div>
        </AppLayout>
    );
}
