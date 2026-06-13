import { BellIcon, ChatBubbleLeftRightIcon, CheckIcon, ClipboardDocumentListIcon, CreditCardIcon, FolderIcon, PaperAirplaneIcon, UserPlusIcon } from '@heroicons/react/24/outline';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/components/layout/AppLayout';
import { EmptyState } from '@/components/ui/EmptyState';
import { readAll as notifReadAll } from '@/routes/notifications';
import type { PageProps } from '@/types';

interface Notif {
    id_notification: number;
    type_notification: 'demande_statut_change' | 'nouvelle_demande' | 'paiement_effectue' | 'rapport_soumis' | 'projet_cloture' | 'projet_mis_en_cours' | 'message_recu' | 'utilisateur_cree';
    id_demande: number | null;
    id_projet: number | null;
    notification_objet: string | null;
    notification_libelle_statut: string | null;
    notification_motif: string | null;
    lu_le: string | null;
    cree_le: string;
    cree_le_complet: string;
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

const STATUT_COULEURS: Record<string, string> = {
    'Validée DAF':       'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    'Rejetée DAF':       'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    'Validée AC':        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    'Rejetée AC':        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    'Payée':             'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    'Rapport soumis':    'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    'Terminée':          'bg-slate-100 text-slate-700 dark:bg-slate-700/30 dark:text-slate-400',
};

export default function Notifications() {
    const { notifications } = usePage<Props>().props;
    const readAllForm = useForm({});

    const handleReadAll = () => {
        readAllForm.patch(notifReadAll.url(), { preserveScroll: true });
    };

    const unreadCount = notifications.data.filter((n) => !n.lu_le).length;

    return (
        <AppLayout title="Notifications">
            <Head title="Notifications — CIFEU" />

            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Notifications</h2>
                    <p className="text-sm text-slate-500 mt-1">
                        {notifications.total} notification{notifications.total !== 1 ? 's' : ''} au total
                    </p>
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

            <div className="max-w-2xl mx-auto">
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
                            const typeInfo = (() => {
                                switch (notif.type_notification) {
                                    case 'projet_cloture':
                                        return { icon: FolderIcon, badge: 'Projet clôturé', badgeClass: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' };
                                    case 'projet_mis_en_cours':
                                        return { icon: FolderIcon, badge: 'Projet mis en cours', badgeClass: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' };
                                    case 'nouvelle_demande':
                                        return { icon: PaperAirplaneIcon, badge: 'Nouvelle demande', badgeClass: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' };
                                    case 'paiement_effectue':
                                        return { icon: CreditCardIcon, badge: 'Paiement effectué', badgeClass: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' };
                                    case 'rapport_soumis':
                                        return { icon: ClipboardDocumentListIcon, badge: 'Rapport soumis', badgeClass: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' };
                                    case 'message_recu':
                                        return { icon: ChatBubbleLeftRightIcon, badge: 'Nouveau message', badgeClass: 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400' };
                                    case 'utilisateur_cree':
                                        return { icon: UserPlusIcon, badge: 'Nouvel utilisateur', badgeClass: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' };
                                    default:
                                        return { icon: ClipboardDocumentListIcon, badge: null, badgeClass: '' };
                                }
                            })();
                            const Icon = typeInfo.icon;
                            const couleurStatut = notif.notification_libelle_statut
                                ? (STATUT_COULEURS[notif.notification_libelle_statut] ?? 'bg-slate-100 text-slate-600')
                                : null;

                            return (
                                <div
                                    key={notif.id_notification}
                                    className={`flex items-start gap-4 p-4 rounded-xl border transition-all ${
                                        notif.lu_le
                                            ? 'bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-700'
                                            : 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800'
                                    }`}
                                >
                                    <div className={`shrink-0 w-9 h-9 rounded-full flex items-center justify-center ${notif.lu_le ? 'bg-slate-100 dark:bg-slate-800' : 'bg-blue-100 dark:bg-blue-900/40'}`}>
                                        <Icon className={`w-5 h-5 ${notif.lu_le ? 'text-slate-400' : 'text-blue-600'}`} />
                                    </div>

                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center gap-2 flex-wrap">
                                            <p className="text-sm font-semibold text-slate-900 dark:text-white truncate">
                                                {notif.notification_objet ?? 'Notification'}
                                            </p>
                                            {notif.notification_libelle_statut && couleurStatut && (
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${couleurStatut}`}>
                                                    {notif.notification_libelle_statut}
                                                </span>
                                            )}
                                            {typeInfo.badge && (
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${typeInfo.badgeClass}`}>
                                                    {typeInfo.badge}
                                                </span>
                                            )}
                                            {!notif.lu_le && (
                                                <span className="w-2 h-2 rounded-full bg-blue-500 shrink-0" />
                                            )}
                                        </div>

                                        {notif.notification_motif && (
                                            <p className="text-xs text-red-600 dark:text-red-400 mt-0.5">
                                                Motif : {notif.notification_motif}
                                            </p>
                                        )}

                                        <p className="text-xs text-slate-400 mt-1" title={notif.cree_le_complet}>
                                            {notif.cree_le}
                                        </p>
                                    </div>
                                </div>
                            );
                        })}

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
