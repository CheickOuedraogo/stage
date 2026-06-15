import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    index as chatIndex,
    send as chatSend,
    poll as chatPoll,
} from '@/actions/App/Http/Controllers/Administrateur/MessageChatControleur';
import AppLayout from '@/components/layout/AppLayout';
import { ChatInterface  } from '@/components/shared/ChatInterface';
import type {ChatMsg} from '@/components/shared/ChatInterface';
import type { PageProps } from '@/types';

interface Contact {
    id: number;
    utilisateur_nom: string;
    utilisateur_email: string;
    role: string;
}

interface Props extends PageProps {
    contact: Contact;
    messages: ChatMsg[];
}

export default function AdminChatShow() {
    const { contact, messages } = usePage<Props>().props;

    return (
        <AppLayout title={`Messages — ${contact.utilisateur_nom}`}>
            <Head title={`Messages — ${contact.utilisateur_nom} — Admin — CIFEU`} />

            <div className="mb-6 flex items-center gap-3">
                <Link
                    href={chatIndex.url()}
                    className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-500 dark:text-slate-400 transition-colors"
                >
                    <ArrowLeftIcon className="w-4 h-4" />
                </Link>
                <div>
                    <h2 className="text-xl font-bold text-slate-900 dark:text-white">{contact.utilisateur_nom}</h2>
                    <p className="text-sm text-slate-500">{contact.role}</p>
                </div>
            </div>

            <div className="max-w-2xl">
                <ChatInterface
                    messages={messages}
                    sendUrl={chatSend.url({ user: contact.id })}
                    pollUrl={chatPoll.url({ user: contact.id })}
                    contactName={contact.utilisateur_nom}
                />
            </div>
        </AppLayout>
    );
}
