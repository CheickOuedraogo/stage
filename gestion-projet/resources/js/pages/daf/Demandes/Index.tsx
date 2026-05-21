import AppLayout from '@/components/layout/AppLayout';
import { formatCurrency, formatDate } from '@/lib/utils';
import {
    show as demandeShow,
    valider as validerAction,
} from '@/actions/App/Http/Controllers/Daf/DemandeDepenseController';
import { Head, Link, router } from '@inertiajs/react';
import { ClipboardDocumentCheckIcon } from '@heroicons/react/24/outline';
import { useState } from 'react';

interface Demande {
    id: number;
    objet: string;
    montant: number;
    statut: string;
    libelle_statut: string;
    badge_class: string;
    cree_le: string;
    porteur: { utilisateur_nom: string };
    convention: { id: number; titre: string };
    projet: { id: number; titre: string };
    rubrique: { libelle: string };
}

interface PaginatedDemandes {
    data: Demande[];
    current_page: number;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

interface Status {
    value: string;
    label: string;
}

interface Props {
    en_attente: Demande[];
    historique: PaginatedDemandes;
    filters: { statut?: string };
    statuses: Status[];
}

export default function DafDemandesIndex({ en_attente, historique, filters, statuses }: Props) {
    const [activeTab, setActiveTab] = useState<'attente' | 'historique'>('attente');
    const [statut, setStatut] = useState(filters.statut ?? '');

    return (
        <AppLayout title="Demandes de dépenses">
            <Head title="Demandes — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Demandes de dépenses</h2>
                <p className="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    {en_attente.length} demande{en_attente.length !== 1 ? 's' : ''} en attente de validation
                </p>
            </div>

            {/* Tabs */}
            <div
                role="tablist"
                aria-label="Onglets des demandes"
                className="flex gap-1 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl mb-6 w-fit"
            >
                {(['attente', 'historique'] as const).map((tab) => (
                    <button
                        key={tab}
                        role="tab"
                        aria-selected={activeTab === tab}
                        aria-controls={`panel-${tab}`}
                        id={`tab-${tab}`}
                        onClick={() => setActiveTab(tab)}
                        onKeyDown={(e) => {
                            if (e.key === 'ArrowRight') setActiveTab('historique');
                            if (e.key === 'ArrowLeft') setActiveTab('attente');
                        }}
                        className={`px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500/50 ${
                            activeTab === tab
                                ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        }`}
                    >
                        {tab === 'attente' ? `En attente (${en_attente.length})` : 'Historique'}
                    </button>
                ))}
            </div>

            {activeTab === 'attente' ? (
                <div role="tabpanel" id="panel-attente" aria-labelledby="tab-attente">
                    {en_attente.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-20 text-center">
                            <ClipboardDocumentCheckIcon className="w-16 h-16 text-slate-300 dark:text-slate-600 mb-4" />
                            <h3 className="text-lg font-medium text-slate-700 dark:text-slate-300 mb-1">Aucune demande en attente</h3>
                            <p className="text-sm text-slate-500 dark:text-slate-400">Toutes les demandes ont été traitées.</p>
                        </div>
                    ) : (
                        <div className="space-y-3">
                            {en_attente.map((d) => (
                                <DemandeRow key={d.id} demande={d} />
                            ))}
                        </div>
                    )}
                </div>
            ) : (
                <div role="tabpanel" id="panel-historique" aria-labelledby="tab-historique">
                    <div className="mb-4">
                        <select
                            value={statut}
                            onChange={(e) => {
                                setStatut(e.target.value);
                                router.get(
                                    window.location.pathname,
                                    { statut: e.target.value || undefined },
                                    { preserveState: true }
                                );
                            }}
                            className="px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                            aria-label="Filtrer par statut"
                        >
                            <option value="">Tous les statuts</option>
                            {statuses.map((s) => (
                                <option key={s.value} value={s.value}>{s.label}</option>
                            ))}
                        </select>
                    </div>
                    <div className="space-y-3">
                        {historique.data.map((d) => (
                            <DemandeRow key={d.id} demande={d} />
                        ))}
                    </div>
                    {(historique.prev_page_url || historique.next_page_url) && (
                        <div className="flex justify-center gap-3 mt-6">
                            {historique.prev_page_url && (
                                <button
                                    onClick={() => router.get(historique.prev_page_url!)}
                                    className="px-4 py-2 text-sm font-medium border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                                >
                                    Précédent
                                </button>
                            )}
                            <span className="px-4 py-2 text-sm text-slate-500">
                                Page {historique.current_page} / {historique.last_page}
                            </span>
                            {historique.next_page_url && (
                                <button
                                    onClick={() => router.get(historique.next_page_url!)}
                                    className="px-4 py-2 text-sm font-medium border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                                >
                                    Suivant
                                </button>
                            )}
                        </div>
                    )}
                </div>
            )}
        </AppLayout>
    );
}

function DemandeRow({ demande }: { demande: Demande }) {
    return (
        <Link
            href={demandeShow.url(demande.id)}
            className="block bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5 hover:shadow-md transition-all duration-200 hover:border-blue-300 dark:hover:border-blue-700"
        >
            <div className="flex items-start justify-between gap-4">
                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2 mb-1 flex-wrap">
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${demande.badge_class}`}>
                            {demande.libelle_statut}
                        </span>
                        <span className="text-xs text-gray-400 dark:text-slate-500">
                            {formatDate(demande.cree_le)}
                        </span>
                    </div>
                    <p className="font-medium text-gray-900 dark:text-white truncate">{demande.objet}</p>
                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                        {demande.porteur.utilisateur_nom} · {demande.projet.titre} · {demande.rubrique.libelle}
                    </p>
                </div>
                <span className="font-mono text-sm font-semibold text-gray-800 dark:text-slate-200 shrink-0">
                    {formatCurrency(demande.montant)}
                </span>
            </div>
        </Link>
    );
}
