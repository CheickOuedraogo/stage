import AppLayout from '@/components/layout/AppLayout';
import { Card, CardContent } from '@/components/ui/Card';
import { auditLog as adminAuditLog } from '@/routes/admin';
import { formatDateTime, getInitials } from '@/lib/utils';
import type { PaginatedData } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { FunnelIcon, UserIcon, XMarkIcon } from '@heroicons/react/24/outline';

interface AuditEntry {
    id: number;
    action: string;
    description: string | null;
    ip_address: string | null;
    created_at: string;
    user: {
        id: number;
        name: string;
        email: string;
        role: string | null;
    } | null;
}

interface UserOption {
    id: number;
    name: string;
    email: string;
    role: string | null;
    role_label: string | null;
}

interface AuditLogProps {
    logs: PaginatedData<AuditEntry>;
    users: UserOption[];
    selectedUserId: number | null;
}

const actionLabels: Record<string, string> = {
    login: 'Connexion',
    logout: 'Déconnexion',
    created: 'Création',
    updated: 'Modification',
    deleted: 'Suppression',
    maintenance_enabled: 'Maintenance activée',
    maintenance_disabled: 'Maintenance désactivée',
    password_changed: 'Mot de passe changé',
    avatar_updated: 'Avatar mis à jour',
    profile_updated: 'Profil mis à jour',
    user_activated: 'Compte activé',
    user_deactivated: 'Compte désactivé',
};

const actionColors: Record<string, string> = {
    login: 'bg-blue-100 text-blue-700',
    logout: 'bg-gray-100 text-gray-600',
    created: 'bg-emerald-100 text-emerald-700',
    updated: 'bg-amber-100 text-amber-700',
    deleted: 'bg-red-100 text-red-700',
    maintenance_enabled: 'bg-amber-100 text-amber-700',
    maintenance_disabled: 'bg-emerald-100 text-emerald-700',
    password_changed: 'bg-purple-100 text-purple-700',
    profile_updated: 'bg-purple-100 text-purple-700',
    avatar_updated: 'bg-purple-100 text-purple-700',
    user_activated: 'bg-emerald-100 text-emerald-700',
    user_deactivated: 'bg-red-100 text-red-700',
};

export default function AuditLog({ logs, users, selectedUserId }: AuditLogProps) {
    const selectedUser = selectedUserId ? users.find((u) => u.id === selectedUserId) : null;

    const filterByUser = (userId: number | null) => {
        router.get(adminAuditLog.url(), userId ? { user_id: userId } : {}, { preserveState: false });
    };

    return (
        <AppLayout title="Journal d'audit">
            <Head title="Journal d'audit — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-gray-900">Journal d'audit</h2>
                <p className="text-sm text-gray-500 mt-1">
                    Historique de toutes les actions effectuées sur la plateforme
                </p>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {/* User filter sidebar */}
                <div className="lg:col-span-1">
                    <Card>
                        <CardContent>
                            <div className="flex items-center gap-2 mb-3">
                                <FunnelIcon className="w-4 h-4 text-gray-500" />
                                <p className="text-sm font-semibold text-gray-700">Filtrer par utilisateur</p>
                            </div>

                            <div className="space-y-1">
                                <button
                                    onClick={() => filterByUser(null)}
                                    className={`w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors text-left ${
                                        !selectedUserId
                                            ? 'bg-gray-900 text-white'
                                            : 'text-gray-600 hover:bg-gray-100'
                                    }`}
                                >
                                    <UserIcon className="w-4 h-4 shrink-0" />
                                    <span>Tous les utilisateurs</span>
                                </button>

                                {users.map((user) => (
                                    <button
                                        key={user.id}
                                        onClick={() => filterByUser(user.id)}
                                        className={`w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors text-left ${
                                            selectedUserId === user.id
                                                ? 'bg-gray-900 text-white'
                                                : 'text-gray-600 hover:bg-gray-100'
                                        }`}
                                    >
                                        <div className={`w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 ${
                                            selectedUserId === user.id ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-600'
                                        }`}>
                                            {getInitials(user.name)}
                                        </div>
                                        <span className="truncate">{user.name.split(' ')[0]}</span>
                                    </button>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Logs list */}
                <div className="lg:col-span-3 space-y-4">
                    {/* Filter indicator */}
                    {selectedUser && (
                        <div className="flex items-center gap-2 px-4 py-2.5 bg-gray-100 rounded-lg">
                            <div className="w-6 h-6 rounded-full bg-gray-700 text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                                {getInitials(selectedUser.name)}
                            </div>
                            <span className="text-sm text-gray-700 font-medium flex-1">
                                Actions de {selectedUser.name}
                            </span>
                            <button
                                onClick={() => filterByUser(null)}
                                className="p-1 rounded hover:bg-gray-200 transition-colors"
                                aria-label="Effacer le filtre"
                            >
                                <XMarkIcon className="w-4 h-4 text-gray-500" />
                            </button>
                        </div>
                    )}

                    {logs.data.length === 0 ? (
                        <Card>
                            <CardContent>
                                <div className="flex flex-col items-center justify-center py-12 text-center">
                                    <div className="w-12 h-12 mb-4 text-gray-300">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                        </svg>
                                    </div>
                                    <p className="text-gray-500 text-sm">Aucune action enregistrée</p>
                                </div>
                            </CardContent>
                        </Card>
                    ) : (
                        <Card>
                            <CardContent>
                                <div className="divide-y divide-gray-100">
                                    {logs.data.map((log) => (
                                        <div key={log.id} className="py-3 first:pt-0 last:pb-0">
                                            <div className="flex items-start gap-3">
                                                {/* Avatar */}
                                                {log.user ? (
                                                    <button
                                                        onClick={() => filterByUser(log.user!.id)}
                                                        className="w-8 h-8 rounded-full bg-gray-200 text-gray-700 text-xs font-bold flex items-center justify-center shrink-0 hover:bg-gray-300 transition-colors mt-0.5"
                                                        title={`Filtrer par ${log.user.name}`}
                                                    >
                                                        {getInitials(log.user.name)}
                                                    </button>
                                                ) : (
                                                    <div className="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center shrink-0 mt-0.5">
                                                        <UserIcon className="w-4 h-4" />
                                                    </div>
                                                )}

                                                {/* Content */}
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex flex-wrap items-center gap-2 mb-0.5">
                                                        <span className="text-sm font-medium text-gray-900">
                                                            {log.user?.name ?? 'Système'}
                                                        </span>
                                                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
                                                            actionColors[log.action] ?? 'bg-gray-100 text-gray-600'
                                                        }`}>
                                                            {actionLabels[log.action] ?? log.action}
                                                        </span>
                                                    </div>
                                                    {log.description && (
                                                        <p className="text-xs text-gray-500 truncate">{log.description}</p>
                                                    )}
                                                    <div className="flex items-center gap-3 mt-1">
                                                        <span className="text-xs text-gray-400">{formatDateTime(log.created_at)}</span>
                                                        {log.ip_address && (
                                                            <span className="text-xs text-gray-300 font-mono">{log.ip_address}</span>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Pagination */}
                    {logs.last_page > 1 && (
                        <div className="flex items-center justify-between">
                            <p className="text-xs text-gray-500">
                                Entrées {logs.from}–{logs.to} sur {logs.total}
                            </p>
                            <div className="flex gap-1">
                                {logs.links.map((link, i) => (
                                    <Link
                                        key={i}
                                        href={link.url ?? '#'}
                                        preserveScroll
                                        className={`px-3 py-1.5 text-xs rounded-lg border transition-colors ${
                                            link.active
                                                ? 'bg-gray-900 text-white border-gray-900'
                                                : link.url
                                                ? 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
                                                : 'bg-white text-gray-300 border-gray-100 cursor-not-allowed pointer-events-none'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
