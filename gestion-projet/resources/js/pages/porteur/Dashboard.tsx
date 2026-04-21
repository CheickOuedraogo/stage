import AppLayout from '@/components/layout/AppLayout';
import { Card, CardContent } from '@/components/ui/Card';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import {
    ClipboardDocumentListIcon,
    FolderIcon,
    InformationCircleIcon,
} from '@heroicons/react/24/outline';

export default function PorteurDashboard() {
    const { auth } = usePage<PageProps>().props;
    const firstName = auth.user?.name.split(' ').find((p) => !p.includes('.')) ?? auth.user?.name;

    return (
        <AppLayout title="Mes Projets">
            <Head title="Mes Projets — CIFEU" />

            <div className="mb-8">
                <h2 className="text-2xl font-bold text-slate-900 dark:text-white">
                    Bonjour, {firstName}
                </h2>
                <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Porteur de projet — Suivi de vos projets et demandes de dépense
                </p>
            </div>

            <div className="mb-8 flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
                <InformationCircleIcon className="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" aria-hidden="true" />
                <div>
                    <p className="font-medium text-blue-800 dark:text-blue-200 text-sm">
                        Vos projets seront disponibles au Sprint 2
                    </p>
                    <p className="text-blue-700 dark:text-blue-300 text-xs mt-0.5">
                        La consultation de vos projets, conventions et l'envoi de demandes de dépense seront disponibles prochainement.
                    </p>
                </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <Card>
                    <CardContent>
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="text-sm text-slate-500 dark:text-slate-400">Mes projets</p>
                                <p className="text-3xl font-bold font-mono text-slate-300 dark:text-slate-600 mt-1 select-none">—</p>
                                <p className="text-xs text-slate-400 dark:text-slate-600 mt-1">Disponible Sprint 2</p>
                            </div>
                            <div className="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                                <FolderIcon className="w-5 h-5 text-blue-400 dark:text-blue-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="text-sm text-slate-500 dark:text-slate-400">Demandes en cours</p>
                                <p className="text-3xl font-bold font-mono text-slate-300 dark:text-slate-600 mt-1 select-none">—</p>
                                <p className="text-xs text-slate-400 dark:text-slate-600 mt-1">Disponible Sprint 3</p>
                            </div>
                            <div className="w-11 h-11 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                                <ClipboardDocumentListIcon className="w-5 h-5 text-amber-400 dark:text-amber-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardContent>
                    <p className="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">Ce que vous pourrez faire prochainement</p>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-slate-600 dark:text-slate-400">
                        {[
                            { sprint: 'S2', label: 'Consulter vos projets et conventions' },
                            { sprint: 'S2', label: 'Voir les statistiques budgétaires' },
                            { sprint: 'S3', label: 'Soumettre des demandes de dépense' },
                            { sprint: 'S3', label: 'Enregistrer les paiements directs' },
                            { sprint: 'S4', label: 'Suivre la timeline de vos demandes' },
                            { sprint: 'S4', label: 'Accéder à la FAQ d\'assistance' },
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
