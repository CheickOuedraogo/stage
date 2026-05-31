import AppLayout from '@/components/layout/AppLayout';
import { Button } from '@/components/ui/Button';
import { formatCurrency, formatDate, formatDateTime } from '@/lib/utils';
import {
    index as demandesIndex,
    uploadRapport as uploadRapportAction,
    downloadJustificatif as _downloadJustificatif,
    downloadRapport as _downloadRapport,
} from '@/actions/App/Http/Controllers/Porteur/DemandeDepenseController';

const downloadJustificatifAction = _downloadJustificatif['/porteur/demandes/{demande}/justificatif'];
const downloadRapportAction = _downloadRapport['/porteur/demandes/{demande}/rapport-pdf'];
import { Head, Link, useForm } from '@inertiajs/react';
import {
    ArrowLeftIcon,
    DocumentArrowDownIcon,
    DocumentArrowUpIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    BanknotesIcon,
} from '@heroicons/react/24/outline';

interface Paiement {
    montant: number;
    date_paiement: string;
    mode_paiement: string;
    mode_paiement_label: string;
    reference: string | null;
    enregistre_par: string;
}

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
    rapport_validee_ac: boolean;
    validee_daf_at: string | null;
    validee_ac_at: string | null;
    validateur_daf: string | null;
    validateur_ac: string | null;
    convention: { id: number; titre: string };
    projet: { id: number; titre: string };
    rubrique: { libelle: string };
    paiement: Paiement | null;
}

interface Props {
    demande: Demande;
}

const STATUS_STEPS = [
    { statut: 'soumise', label: 'Soumise', icon: ClockIcon },
    { statut: 'validee_daf', label: 'Validée DAF', icon: CheckCircleIcon },
    { statut: 'validee_ac', label: 'Validée AC', icon: CheckCircleIcon },
    { statut: 'payee', label: 'Payée', icon: BanknotesIcon },
    { statut: 'rapport_soumis', label: 'Rapport soumis', icon: DocumentArrowUpIcon },
    { statut: 'terminee', label: 'Terminée', icon: CheckCircleIcon },
];

const STATUS_ORDER = ['soumise', 'validee_daf', 'validee_ac', 'payee', 'rapport_soumis', 'terminee'];

function getStepState(stepStatus: string, currentStatus: string): 'done' | 'current' | 'pending' | 'rejected' {
    const isRejected = currentStatus === 'rejetee_daf' || currentStatus === 'rejetee_ac';
    if (isRejected) {
        // rejectedAt = the step where rejection occurred (DAF or AC decision step)
        const rejectedAt = currentStatus === 'rejetee_daf' ? 'validee_daf' : 'validee_ac';
        const rejectedIdx = STATUS_ORDER.indexOf(rejectedAt);
        const stepIdx = STATUS_ORDER.indexOf(stepStatus);
        if (stepIdx < rejectedIdx) return 'done';
        if (stepIdx === rejectedIdx) return 'rejected';
        return 'pending';
    }
    const currentIdx = STATUS_ORDER.indexOf(currentStatus);
    const stepIdx = STATUS_ORDER.indexOf(stepStatus);
    if (stepIdx < currentIdx) return 'done';
    if (stepIdx === currentIdx) return 'current';
    return 'pending';
}

export default function DemandeShow({ demande }: Props) {
    const rapportForm = useForm<{ rapport: File | null }>({ rapport: null });

    const isRejected = demande.statut === 'rejetee_daf' || demande.statut === 'rejetee_ac';
    const canUploadRapport = demande.statut === 'payee';

    function handleRapportSubmit(e: React.FormEvent) {
        e.preventDefault();
        rapportForm.post(uploadRapportAction.url(demande.id), { forceFormData: true });
    }

    return (
        <AppLayout title={demande.objet}>
            <Head title={`${demande.objet} — CIFEU`} />

            {/* Breadcrumb */}
            <div className="mb-6 flex items-center gap-2 text-sm">
                <Link
                    href={demandesIndex.url()}
                    className="flex items-center gap-1.5 text-gray-500 hover:text-gray-900 dark:text-slate-400 dark:hover:text-white transition-colors"
                >
                    <ArrowLeftIcon className="w-3.5 h-3.5" />
                    Mes Demandes
                </Link>
                <span className="text-gray-300 dark:text-slate-600">/</span>
                <span className="text-gray-900 dark:text-white font-medium truncate max-w-xs">{demande.objet}</span>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Colonne principale */}
                <div className="lg:col-span-2 space-y-6">
                    {/* En-tête */}
                    <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                        <div className="flex items-start justify-between gap-4 flex-wrap mb-4">
                            <div>
                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mb-2 ${demande.badge_class}`}>
                                    {demande.libelle_statut}
                                </span>
                                <h2 className="text-xl font-semibold text-gray-900 dark:text-white">{demande.objet}</h2>
                                <p className="text-sm text-gray-500 dark:text-slate-400 mt-1">
                                    {demande.projet.titre} · {demande.convention.titre} · {demande.rubrique.libelle}
                                </p>
                            </div>
                            <span className="font-mono text-xl font-bold text-gray-900 dark:text-white">
                                {formatCurrency(demande.montant)}
                            </span>
                        </div>

                        {demande.description && (
                            <p className="text-sm text-gray-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3">
                                {demande.description}
                            </p>
                        )}

                        {isRejected && demande.motif_rejet && (
                            <div className="mt-4 flex gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <XCircleIcon className="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-sm font-medium text-red-800 dark:text-red-300">Motif de rejet</p>
                                    <p className="text-sm text-red-700 dark:text-red-400 mt-0.5">{demande.motif_rejet}</p>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Téléchargements */}
                    <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Documents</h3>
                        <div className="flex flex-wrap gap-3">
                            {demande.possede_justificatif && (
                                <a
                                    href={downloadJustificatifAction.url(demande.id)}
                                    className="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium rounded-lg transition-colors"
                                >
                                    <DocumentArrowDownIcon className="w-4 h-4" />
                                    Justificatif PDF
                                </a>
                            )}
                            {demande.possede_rapport && (
                                <a
                                    href={downloadRapportAction.url(demande.id)}
                                    className="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium rounded-lg transition-colors"
                                >
                                    <DocumentArrowDownIcon className="w-4 h-4" />
                                    Rapport d'exécution
                                </a>
                            )}
                        </div>
                    </div>

                    {/* Upload rapport */}
                    {canUploadRapport && (
                        <div className="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-6">
                            <h3 className="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-1">
                                Rapport d'exécution requis
                            </h3>

                            {demande.rapport_motif_rejet && (
                                <div className="flex gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 mb-4">
                                    <XCircleIcon className="w-5 h-5 text-red-500 shrink-0" />
                                    <div>
                                        <p className="text-sm font-medium text-red-800 dark:text-red-300">Rapport rejeté</p>
                                        <p className="text-sm text-red-700 dark:text-red-400 mt-0.5">{demande.rapport_motif_rejet}</p>
                                    </div>
                                </div>
                            )}

                            <p className="text-sm text-amber-700 dark:text-amber-400 mb-4">
                                Le paiement a été effectué. Veuillez soumettre le rapport d'exécution (PDF).
                            </p>
                            <form onSubmit={handleRapportSubmit} className="flex items-center gap-3 flex-wrap">
                                <input
                                    type="file"
                                    accept=".pdf"
                                    onChange={(e) => rapportForm.setData('rapport', e.target.files?.[0] ?? null)}
                                    className="text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200 transition-colors"
                                    aria-label="Rapport d'exécution PDF"
                                />
                                <Button type="submit" size="sm" loading={rapportForm.processing}>
                                    <DocumentArrowUpIcon className="w-4 h-4" />
                                    Soumettre le rapport
                                </Button>
                            </form>
                            {rapportForm.errors.rapport && (
                                <p className="mt-2 text-xs text-red-600 dark:text-red-400">{rapportForm.errors.rapport}</p>
                            )}
                        </div>
                    )}

                    {/* Validation rapport */}
                    {demande.statut === 'rapport_soumis' && (
                        <div className="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-6">
                            <h3 className="text-sm font-semibold text-purple-800 dark:text-purple-300 mb-2">
                                Rapport en cours de validation
                            </h3>
                            <div className="flex gap-6">
                                <div className="flex items-center gap-2">
                                    {demande.rapport_validee_daf
                                        ? <CheckCircleIcon className="w-4 h-4 text-emerald-500" />
                                        : <ClockIcon className="w-4 h-4 text-slate-400" />}
                                    <span className="text-sm text-purple-700 dark:text-purple-300">
                                        DAF {demande.rapport_validee_daf ? 'validé' : 'en attente'}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2">
                                    {demande.rapport_validee_ac
                                        ? <CheckCircleIcon className="w-4 h-4 text-emerald-500" />
                                        : <ClockIcon className="w-4 h-4 text-slate-400" />}
                                    <span className="text-sm text-purple-700 dark:text-purple-300">
                                        AC {demande.rapport_validee_ac ? 'validé' : 'en attente'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Colonne droite - Timeline */}
                <div className="space-y-6">
                    <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white mb-5">
                            Suivi du circuit
                        </h3>
                        <div className="space-y-4">
                            {STATUS_STEPS.map((step, idx) => {
                                const state = getStepState(step.statut, demande.statut);
                                const Icon = step.icon;
                                return (
                                    <div key={step.statut} className="flex gap-3">
                                        <div className="flex flex-col items-center">
                                            <div className={`w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-colors ${
                                                state === 'done' ? 'bg-emerald-500 text-white' :
                                                state === 'current' ? 'bg-blue-500 text-white' :
                                                state === 'rejected' ? 'bg-red-500 text-white' :
                                                'bg-slate-200 dark:bg-slate-700 text-slate-400'
                                            }`}>
                                                {state === 'rejected'
                                                    ? <XCircleIcon className="w-4 h-4" />
                                                    : <Icon className="w-4 h-4" />}
                                            </div>
                                            {idx < STATUS_STEPS.length - 1 && (
                                                <div className={`w-0.5 h-8 mt-1 ${state === 'done' ? 'bg-emerald-200 dark:bg-emerald-800' : 'bg-slate-200 dark:bg-slate-700'}`} />
                                            )}
                                        </div>
                                        <div className="pb-4">
                                            <p className={`text-sm font-medium ${
                                                state === 'pending' ? 'text-slate-400 dark:text-slate-600' : 'text-slate-800 dark:text-slate-200'
                                            }`}>
                                                {step.label}
                                            </p>
                                            {step.statut === 'validee_daf' && demande.validee_daf_at && (
                                                <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                                    {formatDateTime(demande.validee_daf_at)} · {demande.validateur_daf}
                                                </p>
                                            )}
                                            {step.statut === 'validee_ac' && demande.validee_ac_at && (
                                                <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                                    {formatDateTime(demande.validee_ac_at)} · {demande.validateur_ac}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>
            </div>

            {/* Paiement — pleine largeur en bas */}
            {demande.paiement && (
                <div className="mt-6 bg-white dark:bg-slate-800 border border-emerald-200 dark:border-emerald-800 rounded-xl overflow-hidden">
                    <div className="flex items-center gap-3 px-6 py-4 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-800">
                        <div className="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-800 flex items-center justify-center shrink-0">
                            <BanknotesIcon className="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <h3 className="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                                Paiement effectué
                            </h3>
                            <p className="text-xs text-emerald-600 dark:text-emerald-400">
                                Paiement enregistré le {formatDate(demande.paiement.date_paiement)}
                            </p>
                        </div>
                        <span className="ml-auto font-mono text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            {formatCurrency(demande.paiement.montant)}
                        </span>
                    </div>
                    <div className="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-1">Mode de paiement</p>
                            <p className="text-sm font-medium text-slate-900 dark:text-white">{demande.paiement.mode_paiement_label}</p>
                        </div>
                        <div>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-1">Référence</p>
                            <p className="text-sm font-mono text-slate-900 dark:text-white">{demande.paiement.reference ?? '—'}</p>
                        </div>
                        <div>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-1">Date de paiement</p>
                            <p className="text-sm font-medium text-slate-900 dark:text-white">{formatDate(demande.paiement.date_paiement)}</p>
                        </div>
                        <div>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mb-1">Enregistré par</p>
                            <p className="text-sm font-medium text-slate-900 dark:text-white">{demande.paiement.enregistre_par}</p>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
