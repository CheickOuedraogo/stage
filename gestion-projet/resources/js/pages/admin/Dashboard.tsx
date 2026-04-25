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
    ClipboardDocumentListIcon,
    FolderIcon,
    ShieldCheckIcon,
    UsersIcon,
    UserGroupIcon,
} from '@heroicons/react/24/outline';
import { FormEvent } from 'react';

interface AuditLogEntry {
    id: number;
    action: string;
    description: string | null;
    user: string;
    created_at: string;
}

interface AdminDashboardProps {
    stats: {
        total_users: number;
        active_users: number;
        users_by_role: Array<{ role: string; label: string; count: number }>;
        total_projets: number;
        projets_actifs: number;
        total_conventions: number;
        demandes_en_cours: number;
    };
    recent_audit_logs: AuditLogEntry[];
}

type BadgeVariant = 'admin' | 'daf' | 'ac' | 'porteur' | 'default';

const roleVariants: Record<string, BadgeVariant> = {
    Admin: 'admin',
    DAF: 'daf',
    AC: 'ac',
    Porteur: 'porteur',
};

export default function AdminDashboard({ stats, recent_audit_logs }: AdminDashboardProps) {
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

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
                    Bonjour, {auth.user?.name.split(' ')[0]}
                </h2>
                <p className="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    Vue d'ensemble de l'administration du système CIFEU
                </p>
            </div>

            {/* Stats globales */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <Link href={usersIndex.url()} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 hover:border-blue-300 hover:shadow-sm transition-all">
                    <div className="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center mb-3">
                        <UsersIcon className="w-5 h-5 text-blue-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.total_users}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Utilisateurs</p>
                    {inactive > 0 && (
                        <p className="text-xs text-amber-600 mt-0.5">{inactive} désactivé{inactive > 1 ? 's' : ''}</p>
                    )}
                </Link>

                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <div className="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center mb-3">
                        <FolderIcon className="w-5 h-5 text-emerald-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.total_projets}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Projets</p>
                    <p className="text-xs text-emerald-600 mt-0.5">{stats.projets_actifs} en cours</p>
                </div>

                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <div className="w-9 h-9 rounded-lg bg-violet-100 flex items-center justify-center mb-3">
                        <ClipboardDocumentListIcon className="w-5 h-5 text-violet-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.total_conventions}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Conventions</p>
                </div>

                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
                    <div className="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center mb-3">
                        <ClipboardDocumentCheckIcon className="w-5 h-5 text-amber-600" />
                    </div>
                    <p className="text-2xl font-bold font-mono text-slate-900 dark:text-white">{stats.demandes_en_cours}</p>
                    <p className="text-xs text-slate-500 mt-0.5">Demandes en cours</p>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                {/* Répartition par rôle */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle>Utilisateurs par rôle</CardTitle>
                            <div className="w-9 h-9 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                                <UserGroupIcon className="w-5 h-5 text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {stats.users_by_role.map((r) => (
                                <div key={r.role} className="flex items-center justify-between">
                                    <Badge variant={(roleVariants[r.role] ?? 'default') as BadgeVariant}>
                                        {r.role}
                                    </Badge>
                                    <div className="flex items-center gap-2">
                                        <div className="w-20 h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                            <div
                                                className="h-full bg-blue-500 rounded-full"
                                                style={{ width: stats.total_users > 0 ? `${Math.round((r.count / stats.total_users) * 100)}%` : '0%' }}
                                            />
                                        </div>
                                        <span className="text-sm font-mono font-semibold text-gray-700 dark:text-slate-300 w-4 text-right">{r.count}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                        <div className="mt-4 pt-3 border-t border-gray-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                            <span>{stats.active_users} actif{stats.active_users !== 1 ? 's' : ''}</span>
                            <Link href={usersIndex.url()} className="text-blue-600 hover:text-blue-700 font-medium">Gérer →</Link>
                        </div>
                    </CardContent>
                </Card>

                {/* Quick links */}
                <div className="space-y-4">
                    <Link
                        href={usersIndex.url()}
                        className="group flex items-center gap-4 p-5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-sm transition-all duration-200"
                    >
                        <div className="w-11 h-11 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                            <UsersIcon className="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p className="font-semibold text-gray-900 dark:text-white text-sm">Gérer les utilisateurs</p>
                            <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Créer, modifier, activer/désactiver</p>
                        </div>
                    </Link>

                    <Link
                        href={adminAuditLog.url()}
                        className="group flex items-center gap-4 p-5 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-sm transition-all duration-200"
                    >
                        <div className="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                            <ClipboardDocumentCheckIcon className="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <p className="font-semibold text-gray-900 dark:text-white text-sm">Journal d'audit</p>
                            <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Consulter l'activité des utilisateurs</p>
                        </div>
                    </Link>
                </div>

                {/* Maintenance toggle */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle>Mode maintenance</CardTitle>
                            <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${
                                maintenance.active
                                    ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'
                                    : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                            }`}>
                                <span className="w-1.5 h-1.5 rounded-full bg-current" aria-hidden="true" />
                                {maintenance.active ? 'Actif' : 'Inactif'}
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submitMaintenance} className="space-y-4" noValidate>
                            <div className="flex items-center justify-between p-3 bg-gray-50 dark:bg-slate-800 rounded-lg">
                                <div>
                                    <p className="text-sm font-medium text-gray-700 dark:text-slate-300">
                                        {data.active ? 'Désactiver la maintenance' : 'Activer la maintenance'}
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
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
                                    className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-slate-900 focus:ring-amber-500 ${
                                        data.active ? 'bg-amber-500' : 'bg-gray-300 dark:bg-slate-600'
                                    }`}
                                    aria-label="Activer/désactiver le mode maintenance"
                                >
                                    <span className={`inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform ${
                                        data.active ? 'translate-x-6' : 'translate-x-1'
                                    }`} />
                                </button>
                            </div>

                            {data.active && (
                                <div className="space-y-3">
                                    <div className="space-y-1">
                                        <label htmlFor="reason" className="block text-xs font-medium text-gray-700 dark:text-slate-300">
                                            Raison <span className="text-red-500" aria-hidden="true">*</span>
                                        </label>
                                        <textarea
                                            id="reason"
                                            value={data.reason}
                                            onChange={(e) => setData('reason', e.target.value)}
                                            rows={2}
                                            required
                                            placeholder="Ex : Mise à jour du système…"
                                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 resize-none"
                                        />
                                    </div>
                                    <div className="space-y-1">
                                        <label htmlFor="until" className="block text-xs font-medium text-gray-700 dark:text-slate-300">
                                            Fin estimée
                                        </label>
                                        <input
                                            id="until"
                                            type="datetime-local"
                                            value={data.until}
                                            onChange={(e) => setData('until', e.target.value)}
                                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                        />
                                    </div>
                                </div>
                            )}

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full py-2 px-4 text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-600 text-white"
                            >
                                {processing ? 'Enregistrement…' : data.active ? 'Activer la maintenance' : 'Désactiver la maintenance'}
                            </button>
                        </form>
                    </CardContent>
                </Card>
            </div>

            {/* Mini journal d'audit */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        <ShieldCheckIcon className="w-4 h-4 text-indigo-500" />
                        <h3 className="text-sm font-semibold text-slate-900 dark:text-white">Dernières actions du système</h3>
                    </div>
                    <Link href={adminAuditLog.url()} className="flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                        Journal complet →
                    </Link>
                </div>
                {recent_audit_logs.length === 0 ? (
                    <p className="p-8 text-center text-sm text-slate-500">Aucune action enregistrée</p>
                ) : (
                    <div className="divide-y divide-gray-100 dark:divide-slate-800">
                        {recent_audit_logs.map((log) => (
                            <div key={log.id} className="flex items-start gap-4 px-5 py-3">
                                <div className="shrink-0 w-2 h-2 mt-1.5 rounded-full bg-indigo-400" />
                                <div className="min-w-0 flex-1">
                                    <p className="text-sm text-slate-900 dark:text-white truncate">
                                        <span className="font-medium">{log.user}</span>
                                        {log.description && <span className="text-slate-500"> — {log.description}</span>}
                                    </p>
                                    <p className="text-xs text-slate-400 mt-0.5 font-mono">{log.action} · {log.created_at}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
