import AppLayout from '@/components/layout/AppLayout';
import { ContactList, type Contact } from '@/components/shared/ContactList';
import { index as chatIndex, show as chatShow } from '@/actions/App/Http/Controllers/Porteur/MessageChatControleur';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { ChatBubbleLeftRightIcon } from '@heroicons/react/24/outline';

interface Props extends PageProps {
    contacts: Contact[];
}

export default function PorteurChat() {
    const { contacts } = usePage<Props>().props;

    return (
        <AppLayout title="Assistance">
            <Head title="Assistance — CIFEU" />

            <div className="max-w-4xl mx-auto">
                <div className="mb-6">
                    <div className="flex items-center gap-3 mb-1">
                        <ChatBubbleLeftRightIcon className="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        <h2 className="text-xl font-bold text-slate-900 dark:text-white">Assistance</h2>
                    </div>
                    <p className="text-sm text-slate-500 dark:text-slate-400 ml-9">
                        Contactez vos interlocuteurs (DAF, Agent Comptable, Administrateur)
                    </p>
                </div>

                <div className="mb-6">
                    <h3 className="text-sm font-semibold text-slate-900 dark:text-white mb-3">Mes contacts</h3>
                    <ContactList
                        contacts={contacts}
                        getConversationUrl={(userId) => chatShow.url({ user: userId })}
                        emptyMessage="Aucun contact disponible."
                    />
                </div>
            </div>
        </AppLayout>
    );
}
