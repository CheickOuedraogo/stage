import {
    ArrowRightIcon,
    BanknotesIcon,
    CheckCircleIcon,
    ClipboardDocumentCheckIcon,
    ClipboardDocumentListIcon,
    CreditCardIcon,
} from '@heroicons/react/24/outline';
import { Head, Link, usePage } from '@inertiajs/react';
import { show as demandeShow } from '@/actions/App/Http/Controllers/AgentComptable/DemandeDepenseController';
import AppLayout from '@/components/layout/AppLayout';
import { FaqAccordion  } from '@/components/shared/FaqAccordion';
import type {FaqItem} from '@/components/shared/FaqAccordion';
import { formatCurrency, formatDate } from '@/lib/utils';
import { index as acDemandesIndex } from '@/routes/ac/demandes';
import type { PageProps } from '@/types';

interface Stats {
    demandes_en_attente: number;
    rapports_soumis: number;
    paiements_effectues: number;
    montant_paye: number;
}

interface Demande {
    id: number;
    objet: string;
    montant: number;
    statut: string;
    libelle_statut: string;
    badge_class: string;
    porteur: string;
    convention: string;
    cree_le: string;
}

interface PaiementRecent {
    id: number;
    montant: number;
    date_paiement: string;
    mode_paiement: string;
    reference: string | null;
    objet: string;
    porteur: string;
    convention: string;
}

interface Props extends PageProps {
    stats: Stats;
    demandes_recentes: Demande[];
    paiements_recents: PaiementRecent[];
    faq_items: FaqItem[];
}

export default function AcDashboard() {
    const { auth, stats, demandes_recentes, paiements_recents, faq_items } = usePage<Props>().props;
    const firstName = auth.user?.utilisateur_nom.split(' ').find((p) => !p.includes('.')) ?? auth.user?.utilisateur_nom;

    return (
        <AppLayout title="Tableau de bord">
            <Head title="Tableau de bord Agent Comptable — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Bonjour, {firstName}</h2>
                <p className="text-sm text-slate-500 mt-1">Agent Comptable — Contrôle et enregistrement des paiements</p>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <Link href={acDemandesIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-amber-300 hover:shadow-sm transition-all">
                    <div className="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center mb-3">
                        <ClipboardDocumentListIcon className="w-5 h-5 text-amber-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.demandes_en_attente}</p>
                    <p className="text-xs text-slate-500 mt-0.5">À valider</p>
                </Link>

                <Link href={acDemandesIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-purple-300 hover:shadow-sm transition-all">
                    <div className="w-9 h-9 rounded-lg bg-purple-100 flex items-center justify-center mb-3">
                        <ClipboardDocumentCheckIcon className="w-5 h-5 text-purple-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.rapports_soumis}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Rapports à valider</p>
                </Link>

                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <div className="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center mb-3">
                        <CreditCardIcon className="w-5 h-5 text-blue-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.paiements_effectues}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Paiements effectués</p>
                </div>

                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <div className="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center mb-3">
                        <BanknotesIcon className="w-5 h-5 text-emerald-600" />
                    </div>
                    <p className="text-lg font-bold font-mono text-slate-900 dark:text-white leading-tight">{formatCurrency(stats.montant_paye)}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Montant décaissé</p>
                </div>
            </div>

            <div className="grid grid-cols-1 gap-6">

                {/* Demandes en attente */}
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 className="text-sm font-semibold text-slate-900 dark:text-white">Demandes à traiter</h3>
                        <Link href={acDemandesIndex.url()} className="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                            Voir tout <ArrowRightIcon className="w-3 h-3" />
                        </Link>
                    </div>
                    {demandes_recentes.length === 0 ? (
                        <p className="p-8 text-center text-sm text-slate-500">Aucune demande en attente</p>
                    ) : (
                        <div className="divide-y divide-gray-100 dark:divide-slate-800">
                            {demandes_recentes.map((d) => (
                                <Link
                                    key={d.id}
                                    href={demandeShow.url(d.id)}
                                    className="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors"
                                >
                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-medium text-slate-900 dark:text-white truncate">{d.objet}</p>
                                        <p className="text-xs text-slate-500 mt-0.5 truncate">{d.porteur} — {d.convention}</p>
                                    </div>
                                    <div className="shrink-0 text-right">
                                        <p className="text-sm font-mono font-semibold text-slate-900 dark:text-white">{formatCurrency(d.montant)}</p>
                                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-0.5 ${d.badge_class}`}>
                                            {d.libelle_statut}
                                        </span>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>

                {/* Paiements récents */}
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center gap-2">
                        <CheckCircleIcon className="w-4 h-4 text-emerald-600" />
                        <h3 className="text-sm font-semibold text-slate-900 dark:text-white">Derniers paiements effectués</h3>
                    </div>
                    {paiements_recents.length === 0 ? (
                        <p className="p-8 text-center text-sm text-slate-500">Aucun paiement enregistré</p>
                    ) : (
                        <div className="divide-y divide-gray-100 dark:divide-slate-800">
                            {paiements_recents.map((p) => (
                                <div key={p.id} className="px-5 py-3.5">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0 flex-1">
                                            <p className="text-sm font-medium text-slate-900 dark:text-white truncate">{p.objet}</p>
                                            <p className="text-xs text-slate-500 mt-0.5 truncate">{p.porteur} — {p.convention}</p>
                                        </div>
                                        <div className="shrink-0 text-right">
                                            <p className="text-sm font-mono font-semibold text-emerald-600">{formatCurrency(p.montant)}</p>
                                            <p className="text-xs text-slate-500 mt-0.5">{formatDate(p.date_paiement)}</p>
                                        </div>
                                    </div>
                                    <div className="mt-1.5 flex items-center gap-2">
                                        <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                            {p.mode_paiement}
                                        </span>
                                        {p.reference && (
                                            <span className="text-xs text-slate-400 font-mono">Réf. {p.reference}</span>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
            <FaqAccordion items={faq_items} />
        </AppLayout>
    );
}
