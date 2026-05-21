import AppLayout from '@/components/layout/AppLayout';
import { show as chatShow } from '@/actions/App/Http/Controllers/Admin/ChatController';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ChatBubbleLeftRightIcon, UserCircleIcon } from '@heroicons/react/24/outline';

interface Conversation {
    id_utilisateur: number;
    utilisateur_nom: string;
    role: string;
    unread: number;
    last_message: string | null;
    last_at: string | null;
}

interface Props extends PageProps {
    conversations: Conversation[];
}

export default function AdminChatIndex() {
    const { conversations } = usePage<Props>().props;

    return (
        <AppLayout title="Messages">
            <Head title="Messages — Admin — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Messages</h2>
                <p className="text-sm text-slate-500 mt-1">Conversations avec les DAF et Agent Comptable</p>
            </div>

            <div className="max-w-2xl space-y-3">
                {conversations.length === 0 ? (
                    <div className="text-center py-16 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl">
                        <ChatBubbleLeftRightIcon className="w-10 h-10 text-slate-300 mx-auto mb-3" />
                        <p className="text-slate-500">Aucune conversation pour le moment.</p>
                    </div>
                ) : (
                    conversations.map((conv) => (
                        <Link
                            key={conv.id_utilisateur}
                            href={chatShow.url(conv.id_utilisateur)}
                            className="flex items-center gap-4 p-4 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl hover:border-blue-300 hover:shadow-sm transition-all"
                        >
                            <div className="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                                <UserCircleIcon className="w-6 h-6 text-blue-600" />
                            </div>
                            <div className="flex-1 min-w-0">
                                <div className="flex items-center justify-between gap-2">
                                    <p className="text-sm font-semibold text-slate-900 dark:text-white truncate">{conv.utilisateur_nom}</p>
                                    {conv.last_at && <p className="text-xs text-slate-400 shrink-0">{conv.last_at}</p>}
                                </div>
                                <div className="flex items-center justify-between gap-2 mt-0.5">
                                    <p className="text-xs text-slate-500 truncate">
                                        {conv.last_message ?? 'Aucun message'}
                                    </p>
                                    <div className="flex items-center gap-2 shrink-0">
                                        <span className="text-xs px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">{conv.role}</span>
                                        {conv.unread > 0 && (
                                            <span className="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-600 text-white text-xs font-bold">
                                                {conv.unread}
                                            </span>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </Link>
                    ))
                )}
            </div>
        </AppLayout>
    );
}
