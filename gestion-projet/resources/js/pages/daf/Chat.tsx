import { ChatBubbleLeftRightIcon } from '@heroicons/react/24/outline';
import { Head, Link, usePage } from '@inertiajs/react';
import { index as chatIndex, show as chatShow } from '@/actions/App/Http/Controllers/Daf/MessageChatControleur';
import AppLayout from '@/components/layout/AppLayout';
import { ContactList  } from '@/components/shared/ContactList';
import type {Contact} from '@/components/shared/ContactList';
import type { PageProps } from '@/types';

interface Props extends PageProps {
    contacts: Contact[];
}

export default function DafChat() {
    const { contacts } = usePage<Props>().props;

    return (
        <AppLayout title="Assistance">
            <Head title="Assistance — DAF — CIFEU" />

            <div className="max-w-4xl mx-auto">
                <div className="mb-6">
                    <div className="flex items-center gap-3 mb-1">
                        <ChatBubbleLeftRightIcon className="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        <h2 className="text-xl font-bold text-slate-900 dark:text-white">Assistance</h2>
                    </div>
                    <p className="text-sm text-slate-500 dark:text-slate-400 ml-9">
                        Échangez avec les porteurs de projets et l'administrateur
                    </p>
                </div>

                <ContactList
                    contacts={contacts}
                    getConversationUrl={(userId) => chatShow.url({ user: userId })}
                    emptyMessage="Aucun contact disponible pour le moment."
                />
            </div>
        </AppLayout>
    );
}
