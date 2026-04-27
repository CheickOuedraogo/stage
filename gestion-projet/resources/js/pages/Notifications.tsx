import AppLayout from '@/components/layout/AppLayout';
import {
    index as notificationsIndex,
    readAll as notifReadAll,
} from '@/routes/notifications';
import type { PageProps } from '@/types';
import { EmptyState } from '@/components/ui/EmptyState';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import {
    BellIcon,
    CheckIcon,
    ClipboardDocumentListIcon,
} from '@heroicons/react/24/outline';

interface NotifData {
    demande_id?: number;
    objet?: string;
    status_label?: string;
    motif?: string;
}

interface Notif {
    id: string;
    data: NotifData;
    read_at: string | null;
    created_at: string;
    created_at_full: string;
}

interface PaginatedNotifs {
    data: Notif[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Props extends PageProps {
    notifications: PaginatedNotifs;
}

const STATUS_COLORS: Record<string, string> = {
    validee_daf: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    rejetee_daf: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    validee_ac: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    rejetee_ac: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    payee: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    rapport_soumis: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    terminee: 'bg-slate-100 text-slate-700 dark:bg-slate-700/30 dark:text-slate-400',
};

export default function Notifications() {
    const { auth, notifications } = usePage<Props>().props;
    const readAllForm = useForm({});

    const handleReadAll = () => {
        readAllForm.patch(notifReadAll.url(), { preserveScroll: true });
    };

    const unreadCount = notifications.data.filter((n) => !n.read_at).length;

    return (
        <AppLayout title="Notifications">
            <Head title="Notifications — CIFEU" />

            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Notifications</h2>
                    <p className="text-sm text-slate-500 mt-1">{notifications.total} notification{notifications.total !== 1 ? 's' : ''} au total</p>
                </div>
                {unreadCount > 0 && (
                    <button
                        onClick={handleReadAll}
                        disabled={readAllForm.processing}
                        className="flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-gray-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                    >
                        <CheckIcon className="w-4 h-4" />
                        Tout marquer comme lu
                    </button>
                )}
            </div>

            <div className="max-w-2xl">
                {notifications.data.length === 0 ? (
                    <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl">
                        <EmptyState
                            icon={BellIcon}
                            title="Aucune notification"
                            description="Vous serez notifié ici dès qu'une demande change de statut dans le circuit de validation."
                        />
                    </div>
                ) : (
                    <div className="space-y-2">
                        {notifications.data.map((notif) => {
                            const status = notif.data.status_label ?? '';
                            const statusKey = Object.keys(STATUS_COLORS).find(
                                (k) => notif.data.status_label?.toLowerCase().includes(k.replace('_', ' '))
                            ) ?? '';

                            return (
                                <div
                                    key={notif.id}
                                    className={`flex items-start gap-4 p-4 rounded-xl border transition-all ${
                                        notif.read_at
                                            ? 'bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-700'
                                            : 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800'
                                    }`}
                                >
                                    <div className={`shrink-0 w-9 h-9 rounded-full flex items-center justify-center ${notif.read_at ? 'bg-slate-100 dark:bg-slate-800' : 'bg-blue-100 dark:bg-blue-900/40'}`}>
                                        <ClipboardDocumentListIcon className={`w-5 h-5 ${notif.read_at ? 'text-slate-400' : 'text-blue-600'}`} />
                                    </div>

                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center gap-2 flex-wrap">
                                            <p className="text-sm font-semibold text-slate-900 dark:text-white truncate">
                                                {notif.data.objet ?? 'Demande de dépense'}
                                            </p>
                                            {status && (
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${STATUS_COLORS[statusKey] ?? 'bg-slate-100 text-slate-600'}`}>
                                                    {status}
                                                </span>
                                            )}
                                            {!notif.read_at && (
                                                <span className="w-2 h-2 rounded-full bg-blue-500 shrink-0" />
                                            )}
                                        </div>

                                        {notif.data.motif && (
                                            <p className="text-xs text-red-600 dark:text-red-400 mt-0.5">
                                                Motif : {notif.data.motif}
                                            </p>
                                        )}

                                        <p className="text-xs text-slate-400 mt-1" title={notif.created_at_full}>
                                            {notif.created_at}
                                        </p>
                                    </div>
                                </div>
                            );
                        })}

                        {/* Pagination */}
                        {notifications.last_page > 1 && (
                            <div className="pt-2 flex items-center justify-between text-sm">
                                <p className="text-slate-500">Page {notifications.current_page} / {notifications.last_page}</p>
                                <div className="flex gap-1">
                                    {notifications.links.map((link, i) => (
                                        link.url ? (
                                            <Link
                                                key={i}
                                                href={link.url}
                                                className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-colors ${
                                                    link.active
                                                        ? 'bg-blue-600 text-white'
                                                        : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800'
                                                }`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ) : (
                                            <span key={i} className="px-3 py-1.5 text-xs text-slate-300 dark:text-slate-600" dangerouslySetInnerHTML={{ __html: link.label }} />
                                        )
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
