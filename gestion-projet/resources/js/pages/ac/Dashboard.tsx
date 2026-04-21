import AppLayout from '@/components/layout/AppLayout';
import { Card, CardContent } from '@/components/ui/Card';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import {
    ClipboardDocumentListIcon,
    CreditCardIcon,
    InformationCircleIcon,
} from '@heroicons/react/24/outline';

export default function AcDashboard() {
    const { auth } = usePage<PageProps>().props;
    const firstName = auth.user?.name.split(' ').find((p) => !p.includes('.')) ?? auth.user?.name;

    return (
        <AppLayout title="Tableau de bord">
            <Head title="Tableau de bord Agent Comptable — CIFEU" />

            <div className="mb-8">
                <h2 className="text-2xl font-bold text-slate-900 dark:text-white">
                    Bonjour, {firstName}
                </h2>
                <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Agent Comptable — Contrôle et enregistrement des paiements
                </p>
            </div>

            <div className="mb-8 flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                <InformationCircleIcon className="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" aria-hidden="true" />
                <div>
                    <p className="font-medium text-blue-800 dark:text-blue-200 text-sm">
                        Circuit de validation disponible au Sprint 3
                    </p>
                    <p className="text-blue-700 dark:text-blue-300 text-xs mt-0.5">
                        La validation des demandes approuvées par la DAF et l'enregistrement des paiements seront disponibles au Sprint 3.
                    </p>
                </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <Card>
                    <CardContent>
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="text-sm text-slate-500 dark:text-slate-400">Demandes en attente</p>
                                <p className="text-3xl font-bold font-mono text-slate-300 dark:text-slate-600 mt-1 select-none">—</p>
                                <p className="text-xs text-slate-400 dark:text-slate-600 mt-1">Disponible Sprint 3</p>
                            </div>
                            <div className="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                                <ClipboardDocumentListIcon className="w-5 h-5 text-amber-400 dark:text-amber-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="text-sm text-slate-500 dark:text-slate-400">Paiements effectués</p>
                                <p className="text-3xl font-bold font-mono text-slate-300 dark:text-slate-600 mt-1 select-none">—</p>
                                <p className="text-xs text-slate-400 dark:text-slate-600 mt-1">Disponible Sprint 3</p>
                            </div>
                            <div className="w-11 h-11 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                                <CreditCardIcon className="w-5 h-5 text-emerald-400 dark:text-emerald-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardContent>
                    <p className="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">Fonctionnalités à venir</p>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-slate-600 dark:text-slate-400">
                        {[
                            { sprint: 'S3', label: 'Validation des demandes approuvées DAF' },
                            { sprint: 'S3', label: 'Enregistrement des paiements' },
                            { sprint: 'S3', label: 'Téléchargement des justificatifs PDF' },
                            { sprint: 'S4', label: 'Tableau de bord complet avec indicateurs' },
                            { sprint: 'S4', label: 'Historique des paiements avec filtres' },
                        ].map((f) => (
                            <div key={f.label} className="flex items-center gap-2.5">
                                <span className="shrink-0 inline-flex items-center justify-center w-8 h-5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                    {f.sprint}
                                </span>
                                <span>{f.label}</span>
                            </div>
                        ))}
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
