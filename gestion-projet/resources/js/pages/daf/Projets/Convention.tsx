import AppLayout from '@/components/layout/AppLayout';
import { MarkdownRenderer } from '@/components/ui/MarkdownRenderer';
import { ConfirmModal } from '@/components/ui/ConfirmModal';
import { clampPercent, conventionStatusClass, formatCurrency, formatDate, truncate } from '@/lib/utils';
import { CHART_PALETTE, CHART_TOOLTIP_STYLE } from '@/lib/charts';
import { show as dafProjetsShow } from '@/routes/daf/projets';
import { store as storeRubrique, update as updateRubrique, destroy as destroyRubrique } from '@/routes/daf/projets/conventions/rubriques';
import { store as storeVersement, destroy as destroyVersement } from '@/routes/daf/projets/conventions/versements';
import { store as storePaiementDirect, destroy as destroyPaiementDirect } from '@/routes/daf/projets/conventions/paiements-directs';
import { terminer as terminerConvention, annuler as annulerConvention } from '@/routes/daf/projets/conventions';
import { Head, useForm, router } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    BanknotesIcon,
    CalendarIcon,
    CheckCircleIcon,
    PencilSquareIcon,
    PlusIcon,
    TrashIcon,
    XCircleIcon,
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

export default function DafConventionShow({ projet, convention }: Props) {
    const [editingId, setEditingId] = useState<number | null>(null);
    const [showAddRubrique, setShowAddRubrique] = useState(false);
    const [showAddVersement, setShowAddVersement] = useState(false);
    const [showAddPaiementDirect, setShowAddPaiementDirect] = useState(false);
    const [confirmRubrique, setConfirmRubrique] = useState<number | null>(null);
    const [confirmVersement, setConfirmVersement] = useState<number | null>(null);
    const [confirmPaiementDirect, setConfirmPaiementDirect] = useState<number | null>(null);
    const [confirmTerminer, setConfirmTerminer] = useState(false);
    const [confirmAnnuler, setConfirmAnnuler] = useState(false);

    const rubriqueForm = useForm({ libelle: '', montant_prevu: '', description: '' });
    const addVersementForm = useForm({ montant: '', date_reception: '', reference: '', description: '' });
    const paiementDirectForm = useForm({
        rubrique_id: '',
        montant: '',
        objet_depense: '',
        description: '',
        date_paiement: new Date().toISOString().split('T')[0],
    });

    const params = { projet: projet.id, convention: convention.id };
    const isEditable = convention.statut === 'active' || convention.statut === 'suspendue';

    const handleTerminer = () => {
        router.patch(terminerConvention.url(params), {}, { onSuccess: () => setConfirmTerminer(false) });
    };

    const handleAnnuler = () => {
        router.patch(annulerConvention.url(params), {}, { onSuccess: () => setConfirmAnnuler(false) });
    };

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

    const handleEditRubrique = (e: React.FormEvent, rubriqueId: number) => {
        e.preventDefault();
        rubriqueForm.patch(updateRubrique.url({ ...params, rubrique: rubriqueId }), {
            onSuccess: () => setEditingId(null),
        });
    };

    const handleDeleteRubrique = (rubriqueId: number) => {
        setConfirmRubrique(rubriqueId);
    };

    const handleAddVersement = (e: React.FormEvent) => {
        e.preventDefault();
        addVersementForm.post(storeVersement.url(params), {
            onSuccess: () => { addVersementForm.reset(); setShowAddVersement(false); },
        });
    };

    const handleDeleteVersement = (versementId: number) => {
        setConfirmVersement(versementId);
    };

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
    const fondsDisponibles = convention.total_versements - totalDepense; // Uniquement basé sur ce qui est en caisse

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
                        {isEditable && (
                            <>
                                <button
                                    type="button"
                                    onClick={() => setConfirmTerminer(true)}
                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50 transition-colors"
                                >
                                    <CheckCircleIcon className="w-3.5 h-3.5" />
                                    Terminer
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setConfirmAnnuler(true)}
                                    className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-700 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 transition-colors"
                                >
                                    <XCircleIcon className="w-3.5 h-3.5" />
                                    Annuler
                                </button>
                            </>
                        )}
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

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                                    <div className="sm:col-span-2">
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
                                            <th className="text-left px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Référence</th>
                                            <th className="text-right px-5 py-3 text-xs font-semibold text-gray-600 dark:text-slate-400">Montant</th>
                                            <th className="px-5 py-3 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                        {convention.versements.map((v) => (
                                            <tr key={v.id} className="hover:bg-gray-50 dark:hover:bg-slate-800 group">
                                                <td className="px-5 py-3 text-gray-900 dark:text-white">{formatDate(v.date_reception)}</td>
                                                <td className="px-5 py-3 text-gray-600 dark:text-slate-400 font-mono text-xs">{v.reference ?? '—'}</td>
                                                <td className="px-5 py-3 text-right font-mono font-semibold text-gray-900 dark:text-white">
                                                    {formatCurrency(v.montant)}
                                                </td>
                                                <td className="px-5 py-3 text-right">
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
                                            <td colSpan={2} className="px-5 py-3 text-xs font-semibold text-gray-700 dark:text-slate-300">Total reçu</td>
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

                    {/* Paiements directs du bailleur */}
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
                        <div className="p-5">
                            <MarkdownRenderer content={convention.description} />
                        </div>
                    </div>
                )}
            </div>

            <ConfirmModal
                open={confirmRubrique !== null}
                title="Supprimer la rubrique"
                message="Cette action est irréversible. La rubrique et son historique de consommation seront supprimés."
                confirmLabel="Supprimer"
                onConfirm={() => router.delete(destroyRubrique.url({ ...params, rubrique: confirmRubrique! }), { onSuccess: () => setConfirmRubrique(null), onError: () => setConfirmRubrique(null) })}
                onCancel={() => setConfirmRubrique(null)}
            />
            <ConfirmModal
                open={confirmVersement !== null}
                title="Supprimer le versement"
                message="Ce versement sera définitivement supprimé. Le montant mobilisé de la convention sera recalculé."
                confirmLabel="Supprimer"
                onConfirm={() => router.delete(destroyVersement.url({ ...params, versement: confirmVersement! }), { onSuccess: () => setConfirmVersement(null), onError: () => setConfirmVersement(null) })}
                onCancel={() => setConfirmVersement(null)}
            />
            <ConfirmModal
                open={confirmPaiementDirect !== null}
                title="Supprimer le paiement direct"
                message="Ce paiement direct sera définitivement supprimé."
                confirmLabel="Supprimer"
                onConfirm={() => router.delete(destroyPaiementDirect.url({ ...params, paiement_direct: confirmPaiementDirect! }), { onSuccess: () => setConfirmPaiementDirect(null), onError: () => setConfirmPaiementDirect(null) })}
                onCancel={() => setConfirmPaiementDirect(null)}
            />
            <ConfirmModal
                open={confirmTerminer}
                title="Terminer la convention"
                message={`La convention « ${convention.titre} » sera marquée comme terminée. Cette action est requise avant de pouvoir clôturer le projet.`}
                confirmLabel="Terminer"
                onConfirm={handleTerminer}
                onCancel={() => setConfirmTerminer(false)}
            />
            <ConfirmModal
                open={confirmAnnuler}
                title="Annuler la convention"
                message={`La convention « ${convention.titre} » sera annulée. Les financements associés ne seront plus comptabilisés dans les calculs de clôture.`}
                confirmLabel="Annuler la convention"
                onConfirm={handleAnnuler}
                onCancel={() => setConfirmAnnuler(false)}
            />
        </AppLayout>
    );
}
