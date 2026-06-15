import { WrenchScrewdriverIcon } from '@heroicons/react/24/outline';
import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import AppLayout from '@/components/layout/AppLayout';
import { Button } from '@/components/ui/Button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { update as maintenanceUpdate } from '@/routes/admin/maintenance';

interface MaintenanceStatus {
    active: boolean;
    reason: string | null;
    until: string | null;
}

interface MaintenancePageProps {
    maintenance: MaintenanceStatus;
}

export default function MaintenancePage({ maintenance }: MaintenancePageProps) {
    const { data, setData, patch, processing, errors } = useForm({
        active: maintenance.active,
        reason: maintenance.reason ?? '',
        until: maintenance.until ?? '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        patch(maintenanceUpdate.url(), { preserveScroll: true });
    };

    return (
        <AppLayout title="Mode maintenance">
            <Head title="Maintenance — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <WrenchScrewdriverIcon className="w-7 h-7" />
                    Mode maintenance
                </h2>
                <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Activez le mode maintenance pour bloquer l'accès aux utilisateurs non-admin.
                </p>
            </div>

            <div className="max-w-lg">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle>Configuration</CardTitle>
                            <span
                                className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${
                                    maintenance.active
                                        ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                                        : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                }`}
                            >
                                <span className="w-1.5 h-1.5 rounded-full bg-current" aria-hidden="true" />
                                {maintenance.active ? 'Maintenance active' : 'Système opérationnel'}
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-5" aria-label="Paramètres de maintenance" noValidate>
                            {/* Toggle */}
                            <div className="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                                <div>
                                    <p className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {data.active ? 'Désactiver la maintenance' : 'Activer la maintenance'}
                                    </p>
                                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {data.active
                                            ? 'Les utilisateurs pourront se reconnecter.'
                                            : 'Tous les utilisateurs seront déconnectés.'}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    role="switch"
                                    aria-checked={data.active}
                                    onClick={() => setData('active', !data.active)}
                                    className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/50 ${
                                        data.active ? 'bg-amber-500' : 'bg-slate-300 dark:bg-slate-600'
                                    }`}
                                    aria-label="Activer/désactiver le mode maintenance"
                                >
                                    <span
                                        className={`inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform ${
                                            data.active ? 'translate-x-6' : 'translate-x-1'
                                        }`}
                                    />
                                </button>
                            </div>

                            {data.active && (
                                <>
                                    <div className="space-y-1">
                                        <label htmlFor="reason" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Raison de la maintenance <span className="text-red-500" aria-hidden="true">*</span>
                                        </label>
                                        <textarea
                                            id="reason"
                                            value={data.reason}
                                            onChange={(e) => setData('reason', e.target.value)}
                                            rows={3}
                                            required
                                            placeholder="Ex : Mise à jour du système, maintenance programmée…"
                                            aria-invalid={!!errors.reason}
                                            className="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 resize-none"
                                        />
                                        {errors.reason && (
                                            <p className="text-xs text-red-600 dark:text-red-400">{errors.reason}</p>
                                        )}
                                    </div>

                                    <div className="space-y-1">
                                        <label htmlFor="until" className="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                            Date et heure de fin estimée
                                        </label>
                                        <input
                                            id="until"
                                            type="datetime-local"
                                            value={data.until}
                                            onChange={(e) => setData('until', e.target.value)}
                                            aria-invalid={!!errors.until}
                                            className="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                        />
                                        {errors.until && (
                                            <p className="text-xs text-red-600 dark:text-red-400">{errors.until}</p>
                                        )}
                                        <p className="text-xs text-slate-500 dark:text-slate-400">
                                            La maintenance sera désactivée automatiquement à cette heure.
                                        </p>
                                    </div>
                                </>
                            )}

                            <Button
                                type="submit"
                                variant={data.active ? 'danger' : 'primary'}
                                loading={processing}
                            >
                                {data.active ? '🔧 Activer la maintenance' : '✅ Désactiver la maintenance'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
