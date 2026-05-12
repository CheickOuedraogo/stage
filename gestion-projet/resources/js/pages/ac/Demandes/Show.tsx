import AppLayout from '@/components/layout/AppLayout';
import { Button } from '@/components/ui/Button';
import { formatCurrency, formatDate, formatDateTime } from '@/lib/utils';
import {
    index as demandesIndex,
    valider as validerAction,
    rejeter as rejeterAction,
    enregistrerPaiement as enregistrerPaiementAction,
    validerRapport as validerRapportAction,
    rejeterRapport as rejeterRapportAction,
} from '@/actions/App/Http/Controllers/Ac/DemandeDepenseController';
import {
    downloadJustificatif as _downloadJustificatif,
    downloadRapport as _downloadRapport,
} from '@/actions/App/Http/Controllers/Porteur/DemandeDepenseController';

const downloadJustificatifAction = _downloadJustificatif['/fichiers/demandes/{demande}/justificatif'];
const downloadRapportAction = _downloadRapport['/fichiers/demandes/{demande}/rapport'];
import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    CheckCircleIcon,
    XCircleIcon,
    DocumentArrowDownIcon,
    BanknotesIcon,
} from '@heroicons/react/24/outline';
import { useState } from 'react';

interface ModePaiement {
    value: string;
    label: string;
}

interface Demande {
    id: number;
    objet: string;
    montant: number;
    status: string;
    status_label: string;
    badge_class: string;
    created_at: string;
    description: string | null;
    motif_rejet: string | null;
    has_justificatif: boolean;
    has_rapport: boolean;
    rapport_validee_daf: boolean;
    rapport_validee_ac: boolean;
    validee_daf_at: string | null;
    validee_ac_at: string | null;
    validateur_daf: string | null;
    porteur_email: string;
    rubrique_montant_prevu: number;
    convention: { id: number; titre: string };
    projet: { id: number; titre: string };
    rubrique: { libelle: string };
    porteur: { name: string };
    paiement: {
        montant: number;
        date_paiement: string;
        mode_paiement: string;
        mode_paiement_label: string;
        reference: string | null;
        enregistre_par: string;
    } | null;
}

interface Props {
    demande: Demande;
    modes_paiement: ModePaiement[];
}

export default function AcDemandeShow({ demande, modes_paiement }: Props) {
    const [showRejectForm, setShowRejectForm] = useState(false);
    const [showPaiementForm, setShowPaiementForm] = useState(false);
    const rejectForm = useForm({ motif: '' });
    const paiementForm = useForm({
        montant: demande.montant.toString(),
        date_paiement: new Date().toISOString().split('T')[0],
        mode_paiement: '',
        reference: '',
    });

    const canValidate = demande.status === 'validee_daf';
    const canPay = demande.status === 'validee_ac' && !demande.paiement;
    const canValidateRapport = demande.status === 'rapport_soumis';

    function handleValider() {
        router.post(validerAction.url(demande.id), {}, { preserveScroll: true });
    }

    function handleRejeter(e: React.FormEvent) {
        e.preventDefault();
        rejectForm.post(rejeterAction.url(demande.id), {
            onSuccess: () => setShowRejectForm(false),
        });
    }

    function handlePaiement(e: React.FormEvent) {
        e.preventDefault();
        paiementForm.post(enregistrerPaiementAction.url(demande.id), {
            onSuccess: () => setShowPaiementForm(false),
        });
    }

    return (
        <AppLayout title={demande.objet}>
            <Head title={`${demande.objet} — AC`} />

            {/* Breadcrumb */}
            <div className="mb-6 flex items-center gap-2 text-sm">
                <Link
                    href={demandesIndex.url()}
                    className="flex items-center gap-1.5 text-gray-500 hover:text-gray-900 dark:text-slate-400 dark:hover:text-white transition-colors"
                >
                    <ArrowLeftIcon className="w-3.5 h-3.5" />
                    Demandes
                </Link>
                <span className="text-gray-300 dark:text-slate-600">/</span>
                <span className="text-gray-900 dark:text-white font-medium truncate max-w-xs">{demande.objet}</span>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-6">
                    {/* Détails */}
                    <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                        <div className="flex items-start justify-between gap-4 flex-wrap mb-4">
                            <div>
                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mb-2 ${demande.badge_class}`}>
                                    {demande.status_label}
                                </span>
                                <h2 className="text-xl font-semibold text-gray-900 dark:text-white">{demande.objet}</h2>
                                <p className="text-sm text-gray-500 dark:text-slate-400 mt-1">
                                    Par <strong>{demande.porteur.name}</strong> · {demande.projet.titre} · {demande.convention.titre}
                                </p>
                            </div>
                            <span className="font-mono text-xl font-bold text-gray-900 dark:text-white">
                                {formatCurrency(demande.montant)}
                            </span>
                        </div>

                        <dl className="grid grid-cols-2 gap-4 text-sm border-t border-gray-100 dark:border-slate-700 pt-4">
                            <div>
                                <dt className="text-slate-500 dark:text-slate-400 mb-0.5">Rubrique</dt>
                                <dd className="font-medium text-gray-800 dark:text-slate-200">{demande.rubrique.libelle}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500 dark:text-slate-400 mb-0.5">Montant rubrique</dt>
                                <dd className="font-mono font-medium text-gray-800 dark:text-slate-200">{formatCurrency(demande.rubrique_montant_prevu)}</dd>
                            </div>
                            {demande.validee_daf_at && (
                                <div>
                                    <dt className="text-slate-500 dark:text-slate-400 mb-0.5">Validée DAF le</dt>
                                    <dd className="text-gray-700 dark:text-slate-300">
                                        {formatDateTime(demande.validee_daf_at)}{demande.validateur_daf ? ` · ${demande.validateur_daf}` : ''}
                                    </dd>
                                </div>
                            )}
                        </dl>

                        {demande.description && (
                            <div className="mt-4">
                                <p className="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Description</p>
                                <p className="text-sm text-gray-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3">{demande.description}</p>
                            </div>
                        )}

                        {demande.motif_rejet && (
                            <div className="mt-4 flex gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <XCircleIcon className="w-5 h-5 text-red-500 shrink-0" />
                                <div>
                                    <p className="text-sm font-medium text-red-800 dark:text-red-300">Motif de rejet</p>
                                    <p className="text-sm text-red-700 dark:text-red-400 mt-0.5">{demande.motif_rejet}</p>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Documents */}
                    <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Documents</h3>
                        <div className="flex flex-wrap gap-3">
                            {demande.has_justificatif && (
                                <a href={downloadJustificatifAction.url(demande.id)} className="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium rounded-lg transition-colors">
                                    <DocumentArrowDownIcon className="w-4 h-4" />
                                    Justificatif PDF
                                </a>
                            )}
                            {demande.has_rapport && (
                                <a href={downloadRapportAction.url(demande.id)} className="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium rounded-lg transition-colors">
                                    <DocumentArrowDownIcon className="w-4 h-4" />
                                    Rapport d'exécution
                                </a>
                            )}
                        </div>
                    </div>

                    {/* Validation AC */}
                    {canValidate && (
                        <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">
                                Décision (2e niveau — contrôle comptable)
                            </h3>
                            {!showRejectForm ? (
                                <div className="flex gap-3">
                                    <Button onClick={handleValider} variant="primary">
                                        <CheckCircleIcon className="w-4 h-4" />
                                        Valider la demande
                                    </Button>
                                    <Button variant="danger" onClick={() => setShowRejectForm(true)}>
                                        <XCircleIcon className="w-4 h-4" />
                                        Rejeter
                                    </Button>
                                </div>
                            ) : (
                                <form onSubmit={handleRejeter} className="space-y-3">
                                    <div>
                                        <label htmlFor="motif" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                            Motif de rejet <span className="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            id="motif"
                                            rows={3}
                                            value={rejectForm.data.motif}
                                            onChange={(e) => rejectForm.setData('motif', e.target.value)}
                                            className="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500/50 resize-none"
                                            placeholder="Expliquer la raison du rejet…"
                                        />
                                        {rejectForm.errors.motif && <p className="mt-1 text-xs text-red-600">{rejectForm.errors.motif}</p>}
                                    </div>
                                    <div className="flex gap-3">
                                        <Button type="submit" variant="danger" loading={rejectForm.processing}>Confirmer le rejet</Button>
                                        <Button type="button" variant="ghost" onClick={() => setShowRejectForm(false)}>Annuler</Button>
                                    </div>
                                </form>
                            )}
                        </div>
                    )}

                    {/* Enregistrement paiement */}
                    {canPay && (
                        <div className="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-6">
                            <h3 className="text-sm font-semibold text-emerald-800 dark:text-emerald-300 mb-4">
                                Enregistrement du paiement
                            </h3>
                            {!showPaiementForm ? (
                                <Button onClick={() => setShowPaiementForm(true)}>
                                    <BanknotesIcon className="w-4 h-4" />
                                    Enregistrer le paiement
                                </Button>
                            ) : (
                                <form onSubmit={handlePaiement} className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label htmlFor="montant_p" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                                Montant <span className="text-red-500">*</span>
                                            </label>
                                            <input
                                                id="montant_p"
                                                type="number"
                                                min={1}
                                                value={paiementForm.data.montant}
                                                onChange={(e) => paiementForm.setData('montant', e.target.value)}
                                                className="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-mono bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                            />
                                            {paiementForm.errors.montant && <p className="mt-1 text-xs text-red-600">{paiementForm.errors.montant}</p>}
                                        </div>
                                        <div>
                                            <label htmlFor="date_p" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                                Date <span className="text-red-500">*</span>
                                            </label>
                                            <input
                                                id="date_p"
                                                type="date"
                                                value={paiementForm.data.date_paiement}
                                                max={new Date().toISOString().split('T')[0]}
                                                onChange={(e) => paiementForm.setData('date_paiement', e.target.value)}
                                                className="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                            />
                                            {paiementForm.errors.date_paiement && <p className="mt-1 text-xs text-red-600">{paiementForm.errors.date_paiement}</p>}
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <label htmlFor="mode" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                                Mode <span className="text-red-500">*</span>
                                            </label>
                                            <select
                                                id="mode"
                                                value={paiementForm.data.mode_paiement}
                                                onChange={(e) => paiementForm.setData('mode_paiement', e.target.value)}
                                                className="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                            >
                                                <option value="">Sélectionner</option>
                                                {modes_paiement.map((m) => (
                                                    <option key={m.value} value={m.value}>{m.label}</option>
                                                ))}
                                            </select>
                                            {paiementForm.errors.mode_paiement && <p className="mt-1 text-xs text-red-600">{paiementForm.errors.mode_paiement}</p>}
                                        </div>
                                        <div>
                                            <label htmlFor="reference" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                                Référence
                                            </label>
                                            <input
                                                id="reference"
                                                type="text"
                                                value={paiementForm.data.reference}
                                                onChange={(e) => paiementForm.setData('reference', e.target.value)}
                                                placeholder="N° de chèque, virement…"
                                                className="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                            />
                                        </div>
                                    </div>
                                    <div className="flex gap-3 pt-2">
                                        <Button type="submit" loading={paiementForm.processing}>
                                            <BanknotesIcon className="w-4 h-4" />
                                            Confirmer le paiement
                                        </Button>
                                        <Button type="button" variant="ghost" onClick={() => setShowPaiementForm(false)}>Annuler</Button>
                                    </div>
                                </form>
                            )}
                        </div>
                    )}

                    {/* Validation rapport */}
                    {canValidateRapport && (
                        <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">
                                Validation du rapport d'exécution
                            </h3>
                            <div className="flex gap-3">
                                <Button
                                    onClick={() => router.post(validerRapportAction.url(demande.id), {}, { preserveScroll: true })}
                                    variant="primary"
                                    disabled={demande.rapport_validee_ac}
                                >
                                    <CheckCircleIcon className="w-4 h-4" />
                                    {demande.rapport_validee_ac ? 'Rapport déjà validé' : 'Valider le rapport'}
                                </Button>
                                {!demande.rapport_validee_ac && (
                                    <Button
                                        onClick={() => router.post(rejeterRapportAction.url(demande.id), {}, { preserveScroll: true })}
                                        variant="danger"
                                    >
                                        <XCircleIcon className="w-4 h-4" />
                                        Rejeter le rapport
                                    </Button>
                                )}
                            </div>
                        </div>
                    )}
                </div>

                {/* Sidebar */}
                <div className="space-y-6">
                    <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Informations</h3>
                        <dl className="space-y-3 text-sm">
                            <div>
                                <dt className="text-slate-500 dark:text-slate-400 mb-0.5">Porteur</dt>
                                <dd className="font-medium text-gray-800 dark:text-slate-200">{demande.porteur.name}</dd>
                                <dd className="text-xs text-slate-400">{demande.porteur_email}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500 dark:text-slate-400 mb-0.5">Convention</dt>
                                <dd className="text-gray-700 dark:text-slate-300">{demande.convention.titre}</dd>
                            </div>
                        </dl>
                    </div>

                    {demande.paiement && (
                        <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Paiement effectué</h3>
                            <dl className="space-y-3 text-sm">
                                <div className="flex justify-between">
                                    <dt className="text-slate-500">Montant</dt>
                                    <dd className="font-mono font-semibold">{formatCurrency(demande.paiement.montant)}</dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-slate-500">Date</dt>
                                    <dd>{formatDate(demande.paiement.date_paiement)}</dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-slate-500">Mode</dt>
                                    <dd>{demande.paiement.mode_paiement_label}</dd>
                                </div>
                            </dl>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
