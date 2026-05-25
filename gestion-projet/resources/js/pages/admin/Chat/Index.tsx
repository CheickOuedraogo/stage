import AppLayout from '@/components/layout/AppLayout';
import { ContactList, type Contact } from '@/components/shared/ContactList';
import { show as chatShow } from '@/actions/App/Http/Controllers/Administrateur/MessageChatControleur';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { ChatBubbleLeftRightIcon } from '@heroicons/react/24/outline';

interface Props extends PageProps {
    conversations: Contact[];
}

export default function AdminChatIndex() {
    const { conversations } = usePage<Props>().props;

    return (
        <AppLayout title="Messages">
            <Head title="Messages — Admin — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Messages</h2>
                <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Conversations avec les DAF, Agent Comptable et Porteurs de projet
                </p>
            </div>

            <div className="max-w-2xl">
                <ContactList
                    contacts={conversations}
                    getConversationUrl={(userId) => chatShow.url({ user: userId })}
                    emptyMessage="Aucune conversation pour le moment."
                />
            </div>
        </AppLayout>
    );
}
