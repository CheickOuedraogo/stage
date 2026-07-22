import {
    index as demandesIndex,
    valider as validerAction,
    rejeter as rejeterAction,
    validerRapport as validerRapportAction,
    rejeterRapport as rejeterRapportAction,
} from '@/actions/App/Http/Controllers/Daf/DemandeDepenseController';
import {
    downloadJustificatif as _downloadJustificatif,
    downloadRapport as _downloadRapport,
} from '@/actions/App/Http/Controllers/Porteur/DemandeDepenseController';
import AppLayout from '@/components/layout/AppLayout';
import { Button } from '@/components/ui/Button';
import { Modal } from '@/components/ui/Modal';
import { formatCurrency, formatDate, formatDateTime } from '@/lib/utils';

const downloadJustificatifAction = _downloadJustificatif['/fichiers/demandes/{demande}/justificatif'];
const downloadRapportAction = _downloadRapport['/fichiers/demandes/{demande}/rapport'];
import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    CheckCircleIcon,
    XCircleIcon,
    DocumentArrowDownIcon,
    EyeIcon,
} from '@heroicons/react/24/outline';
import { useState } from 'react';

interface Demande {
    id: number;
    objet: string;
    montant: number;
    statut: string;
    libelle_statut: string;
    badge_class: string;
    cree_le: string;
    description: string | null;
    motif_rejet: string | null;
    possede_justificatif: boolean;
    possede_rapport: boolean;
    rapport_motif_rejet: string | null;
    rapport_validee_daf: boolean;
    validee_daf_at: string | null;
    validee_ac_at: string | null;
    validateur_daf: string | null;
    validateur_ac: string | null;
    porteur_email: string;
    rubrique_montant: number;
    convention: { id: number; titre: string };
    projet: { id: number; titre: string };
    rubrique: { libelle: string };
    porteur: { utilisateur_nom: string };
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
}

export default function DafDemandeShow({ demande }: Props) {
    const [showRejectForm, setShowRejectForm] = useState(false);
    const [showRapportRejectForm, setShowRapportRejectForm] = useState(false);
    const rejectForm = useForm({ motif: '' });
    const rapportRejectForm = useForm({ motif: '' });

    const canValidate = demande.statut === 'soumise';
    const canValidateRapport = demande.statut === 'rapport_soumis';

    function handleValider() {
        router.post(validerAction.url(demande.id), {}, { preserveScroll: true });
    }

    function handleRejeter(e: React.FormEvent) {
        e.preventDefault();
        rejectForm.post(rejeterAction.url(demande.id), {
            onSuccess: () => setShowRejectForm(false),
        });
    }

    function handleRapportRejeter(e: React.FormEvent) {
        e.preventDefault();
        rapportRejectForm.post(rejeterRapportAction.url(demande.id), {
            onSuccess: () => setShowRapportRejectForm(false),
        });
    }

    return (
        <AppLayout title={demande.objet}>
            <Head title={`${demande.objet} — DAF`} />

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
                                    {demande.libelle_statut}
                                </span>
                                <h2 className="text-xl font-semibold text-gray-900 dark:text-white">{demande.objet}</h2>
                                <p className="text-sm text-gray-500 dark:text-slate-400 mt-1">
                                    Par <strong>{demande.porteur.utilisateur_nom}</strong> · {demande.projet.titre} · {demande.convention.titre}
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
                                <dt className="text-slate-500 dark:text-slate-400 mb-0.5">Montant prévu rubrique</dt>
                                <dd className="font-mono font-medium text-gray-800 dark:text-slate-200">{formatCurrency(demande.rubrique_montant)}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500 dark:text-slate-400 mb-0.5">Date de soumission</dt>
                                <dd className="text-gray-700 dark:text-slate-300">{formatDate(demande.cree_le)}</dd>
                            </div>
                        </dl>

                        {demande.description && (
                            <div className="mt-4">
                                <p className="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Description</p>
                                <p className="text-sm text-gray-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3">
                                    {demande.description}
                                </p>
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
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Documents associés</h3>
                        {!demande.possede_justificatif && !demande.possede_rapport ? (
                            <p className="text-sm text-gray-500 dark:text-slate-400">Aucun document n'a été soumis.</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm border-collapse">
                                    <thead>
                                        <tr className="text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-transparent">
                                            <th className="py-2 pr-4">Type de document</th>
                                            <th className="py-2 px-4 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-transparent">
                                        {demande.possede_justificatif && (
                                            <tr>
                                                <td className="py-2.5 pr-4 font-medium text-slate-850 dark:text-slate-200">
                                                    Justificatif de dépense (PDF)
                                                </td>
                                                <td className="py-2.5 px-4 text-right space-x-2">
                                                    <a
                                                        href={`${downloadJustificatifAction.url(demande.id)}?inline=1`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-400 text-xs font-medium rounded-lg transition-colors"
                                                    >
                                                        <EyeIcon className="w-3.5 h-3.5" />
                                                        Voir
                                                    </a>
                                                    <a
                                                        href={downloadJustificatifAction.url(demande.id)}
                                                        className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-medium rounded-lg transition-colors"
                                                    >
                                                        <DocumentArrowDownIcon className="w-3.5 h-3.5" />
                                                        Télécharger
                                                    </a>
                                                </td>
                                            </tr>
                                        )}
                                        {demande.possede_rapport && (
                                            <tr>
                                                <td className="py-2.5 pr-4 font-medium text-slate-850 dark:text-slate-200">
                                                    Rapport d'exécution
                                                </td>
                                                <td className="py-2.5 px-4 text-right space-x-2">
                                                    <a
                                                        href={`${downloadRapportAction.url(demande.id)}?inline=1`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-400 text-xs font-medium rounded-lg transition-colors"
                                                    >
                                                        <EyeIcon className="w-3.5 h-3.5" />
                                                        Voir
                                                    </a>
                                                    <a
                                                        href={downloadRapportAction.url(demande.id)}
                                                        className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-medium rounded-lg transition-colors"
                                                    >
                                                        <DocumentArrowDownIcon className="w-3.5 h-3.5" />
                                                        Télécharger
                                                    </a>
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>

                    {/* Actions validation DAF */}
                    {canValidate && (
                        <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">
                                Décision (1er niveau — conformité & budget)
                            </h3>
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

                            <Modal
                                open={showRejectForm}
                                onClose={() => setShowRejectForm(false)}
                                title="Rejeter la demande"
                            >
                                <form onSubmit={handleRejeter} className="space-y-4">
                                    <div>
                                        <label htmlFor="motif" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                            Motif de rejet <span className="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            id="motif"
                                            rows={4}
                                            value={rejectForm.data.motif}
                                            onChange={(e) => rejectForm.setData('motif', e.target.value)}
                                            className="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500/50 resize-none"
                                            placeholder="Expliquer la raison du rejet en détail…"
                                        />
                                        {rejectForm.errors.motif && (
                                            <p className="mt-1 text-xs text-red-600">{rejectForm.errors.motif}</p>
                                        )}
                                    </div>
                                    <div className="flex justify-end gap-3 pt-2">
                                        <Button type="button" variant="ghost" onClick={() => setShowRejectForm(false)}>
                                            Annuler
                                        </Button>
                                        <Button type="submit" variant="danger" loading={rejectForm.processing}>
                                            Confirmer le rejet
                                        </Button>
                                    </div>
                                </form>
                            </Modal>
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
                                    disabled={demande.rapport_validee_daf}
                                >
                                    <CheckCircleIcon className="w-4 h-4" />
                                    {demande.rapport_validee_daf ? 'Rapport déjà validé' : 'Valider le rapport'}
                                </Button>
                                {!demande.rapport_validee_daf && (
                                    <Button variant="danger" onClick={() => setShowRapportRejectForm(true)}>
                                        <XCircleIcon className="w-4 h-4" />
                                        Rejeter le rapport
                                    </Button>
                                )}
                            </div>

                            <Modal
                                open={showRapportRejectForm}
                                onClose={() => setShowRapportRejectForm(false)}
                                title="Rejeter le rapport d'exécution"
                            >
                                <form onSubmit={handleRapportRejeter} className="space-y-4">
                                    <div>
                                        <label htmlFor="motif-rapport" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                            Motif de rejet <span className="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            id="motif-rapport"
                                            rows={4}
                                            value={rapportRejectForm.data.motif}
                                            onChange={(e) => rapportRejectForm.setData('motif', e.target.value)}
                                            className="w-full px-3 py-2.5 border border-slate-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-red-500/50 resize-none"
                                            placeholder="Expliquer pourquoi le rapport est rejeté…"
                                        />
                                        {rapportRejectForm.errors.motif && (
                                            <p className="mt-1 text-xs text-red-600">{rapportRejectForm.errors.motif}</p>
                                        )}
                                    </div>
                                    <div className="flex justify-end gap-3 pt-2">
                                        <Button type="button" variant="ghost" onClick={() => setShowRapportRejectForm(false)}>
                                            Annuler
                                        </Button>
                                        <Button type="submit" variant="danger" loading={rapportRejectForm.processing}>
                                            Confirmer le rejet
                                        </Button>
                                    </div>
                                </form>
                            </Modal>
                        </div>
                    )}
                </div>

                {/* Sidebar infos */}
                <div className="space-y-6">
                    <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Informations</h3>
                        <dl className="space-y-3 text-sm">
                            <div>
                                <dt className="text-slate-500 dark:text-slate-400 mb-0.5">Porteur</dt>
                                <dd className="font-medium text-gray-800 dark:text-slate-200">{demande.porteur.utilisateur_nom}</dd>
                                <dd className="text-xs text-slate-400">{demande.porteur_email}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500 dark:text-slate-400 mb-0.5">Convention</dt>
                                <dd className="text-gray-700 dark:text-slate-300">{demande.convention.titre}</dd>
                            </div>
                            {demande.validee_daf_at && (
                                <div>
                                    <dt className="text-slate-500 dark:text-slate-400 mb-0.5">Validée DAF le</dt>
                                    <dd className="text-gray-700 dark:text-slate-300">{formatDateTime(demande.validee_daf_at)}</dd>
                                </div>
                            )}
                            {demande.validee_ac_at && (
                                <div>
                                    <dt className="text-slate-500 dark:text-slate-400 mb-0.5">Validée AC le</dt>
                                    <dd className="text-gray-700 dark:text-slate-300">{formatDateTime(demande.validee_ac_at)}</dd>
                                </div>
                            )}
                        </dl>
                    </div>

                    {demande.paiement && (
                        <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                            <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Paiement</h3>
                            <dl className="space-y-3 text-sm">
                                <div className="flex justify-between">
                                    <dt className="text-slate-500 dark:text-slate-400">Montant</dt>
                                    <dd className="font-mono font-semibold">{formatCurrency(demande.paiement.montant)}</dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-slate-500 dark:text-slate-400">Date</dt>
                                    <dd>{formatDate(demande.paiement.date_paiement)}</dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-slate-500 dark:text-slate-400">Mode</dt>
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
