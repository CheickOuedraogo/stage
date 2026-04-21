import AppLayout from '@/components/layout/AppLayout';
import { Badge } from '@/components/ui/Badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { auditLog as adminAuditLog } from '@/routes/admin';
import { index as usersIndex } from '@/routes/admin/users';
import { update as maintenanceUpdate } from '@/routes/admin/maintenance';
import type { PageProps } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import {
    ClipboardDocumentCheckIcon,
    ShieldCheckIcon,
    UsersIcon,
    UserGroupIcon,
} from '@heroicons/react/24/outline';
import { FormEvent } from 'react';

interface AdminDashboardProps {
    stats: {
        total_users: number;
        active_users: number;
        users_by_role: Array<{ role: string; label: string; count: number }>;
    };
}

type BadgeVariant = 'admin' | 'daf' | 'ac' | 'porteur' | 'default';

const roleVariants: Record<string, BadgeVariant> = {
    Admin: 'admin',
    DAF: 'daf',
    AC: 'ac',
    Porteur: 'porteur',
};

export default function AdminDashboard({ stats }: AdminDashboardProps) {
    const { auth, maintenance } = usePage<PageProps>().props;
    const inactive = stats.total_users - stats.active_users;

    const { data, setData, patch, processing } = useForm({
        active: maintenance.active,
        reason: maintenance.reason ?? '',
        until: maintenance.until ?? '',
    });

    const submitMaintenance = (e: FormEvent) => {
        e.preventDefault();
        patch(maintenanceUpdate.url(), { preserveScroll: true });
    };

    return (
        <AppLayout title="Tableau de bord">
            <Head title="Tableau de bord Administrateur — CIFEU" />

            <div className="mb-8">
                <h2 className="text-2xl font-bold text-gray-900">
                    Bonjour, {auth.user?.name.split(' ')[0]}
                </h2>
                <p className="text-sm text-gray-500 mt-1">
                    Vue d'ensemble de l'administration du système CIFEU
                </p>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                <Card>
                    <CardContent>
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Total utilisateurs</p>
                                <p className="text-3xl font-bold font-mono text-gray-900 mt-1">
                                    {stats.total_users}
                                </p>
                                <p className="text-xs text-gray-400 mt-1">
                                    {stats.active_users} actif{stats.active_users !== 1 ? 's' : ''}
                                    {inactive > 0 && ` · ${inactive} désactivé${inactive !== 1 ? 's' : ''}`}
                                </p>
                            </div>
                            <div className="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                <UsersIcon className="w-5 h-5 text-blue-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <div className="flex items-start justify-between">
                            <div>
                                <p className="text-sm text-gray-500">Comptes actifs</p>
                                <p className="text-3xl font-bold font-mono text-gray-900 mt-1">
                                    {stats.active_users}
                                </p>
                                <p className="text-xs mt-1">
                                    <span className="text-emerald-600 font-medium">
                                        {stats.total_users > 0
                                            ? Math.round((stats.active_users / stats.total_users) * 100)
                                            : 0}% du total
                                    </span>
                                </p>
                            </div>
                            <div className="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                                <ShieldCheckIcon className="w-5 h-5 text-emerald-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent>
                        <div className="flex items-start justify-between mb-3">
                            <p className="text-sm text-gray-500">Répartition par rôle</p>
                            <div className="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                                <UserGroupIcon className="w-5 h-5 text-purple-600" />
                            </div>
                        </div>
                        <div className="space-y-2">
                            {stats.users_by_role.map((r) => (
                                <div key={r.role} className="flex items-center justify-between">
                                    <Badge variant={(roleVariants[r.role] ?? 'default') as BadgeVariant}>
                                        {r.role}
                                    </Badge>
                                    <span className="text-sm font-mono font-semibold text-gray-700">
                                        {r.count}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Quick links + Maintenance side by side */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Quick links */}
                <div className="space-y-4">
                    <Link
                        href={usersIndex.url()}
                        className="group flex items-center gap-4 p-5 bg-white border border-gray-200 rounded-xl hover:border-blue-300 hover:shadow-sm transition-all duration-200"
                    >
                        <div className="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 group-hover:bg-blue-100 transition-colors">
                            <UsersIcon className="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <p className="font-semibold text-gray-900 text-sm">Gérer les utilisateurs</p>
                            <p className="text-xs text-gray-500 mt-0.5">Créer, modifier, activer/désactiver</p>
                        </div>
                    </Link>

                    <Link
                        href={adminAuditLog.url()}
                        className="group flex items-center gap-4 p-5 bg-white border border-gray-200 rounded-xl hover:border-indigo-300 hover:shadow-sm transition-all duration-200"
                    >
                        <div className="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0 group-hover:bg-indigo-100 transition-colors">
                            <ClipboardDocumentCheckIcon className="w-5 h-5 text-indigo-600" />
                        </div>
                        <div>
                            <p className="font-semibold text-gray-900 text-sm">Journal d'audit</p>
                            <p className="text-xs text-gray-500 mt-0.5">Consulter l'activité des utilisateurs</p>
                        </div>
                    </Link>
                </div>

                {/* Maintenance toggle */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle>Mode maintenance</CardTitle>
                            <span
                                className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${
                                    maintenance.active
                                        ? 'bg-amber-100 text-amber-700'
                                        : 'bg-emerald-100 text-emerald-700'
                                }`}
                            >
                                <span className="w-1.5 h-1.5 rounded-full bg-current" aria-hidden="true" />
                                {maintenance.active ? 'Actif' : 'Inactif'}
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submitMaintenance} className="space-y-4" noValidate>
                            {/* Toggle */}
                            <div className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p className="text-sm font-medium text-gray-700">
                                        {data.active ? 'Désactiver la maintenance' : 'Activer la maintenance'}
                                    </p>
                                    <p className="text-xs text-gray-500 mt-0.5">
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
                                    className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 ${
                                        data.active ? 'bg-amber-500' : 'bg-gray-300'
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
                                <div className="space-y-3">
                                    <div className="space-y-1">
                                        <label htmlFor="reason" className="block text-xs font-medium text-gray-700">
                                            Raison <span className="text-red-500" aria-hidden="true">*</span>
                                        </label>
                                        <textarea
                                            id="reason"
                                            value={data.reason}
                                            onChange={(e) => setData('reason', e.target.value)}
                                            rows={2}
                                            required
                                            placeholder="Ex : Mise à jour du système…"
                                            className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 resize-none"
                                        />
                                    </div>
                                    <div className="space-y-1">
                                        <label htmlFor="until" className="block text-xs font-medium text-gray-700">
                                            Fin estimée
                                        </label>
                                        <input
                                            id="until"
                                            type="datetime-local"
                                            value={data.until}
                                            onChange={(e) => setData('until', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900"
                                        />
                                    </div>
                                </div>
                            )}

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full py-2 px-4 text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed bg-gray-900 hover:bg-gray-800 text-white"
                            >
                                {processing ? 'Enregistrement…' : data.active ? 'Activer la maintenance' : 'Désactiver la maintenance'}
                            </button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
