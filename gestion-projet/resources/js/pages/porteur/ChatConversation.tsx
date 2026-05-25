import AppLayout from '@/components/layout/AppLayout';
import { ChatInterface, type ChatMsg } from '@/components/shared/ChatInterface';
import { index as chatIndex, send as chatSend, poll as chatPoll } from '@/actions/App/Http/Controllers/Porteur/MessageChatControleur';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';

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

export default function PorteurChatConversation() {
    const { contact, messages } = usePage<Props>().props;

    return (
        <AppLayout title={`Discussion avec ${contact.utilisateur_nom}`}>
            <Head title={`${contact.utilisateur_nom} — Assistance — CIFEU`} />

            <div className="max-w-4xl mx-auto">
                <div className="mb-4">
                    <Link
                        href={chatIndex.url()}
                        className="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors"
                    >
                        <ArrowLeftIcon className="w-4 h-4" />
                        Retour à l'assistance
                    </Link>
                </div>

                <div className="flex items-center gap-3 mb-4">
                    <div className="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                        <span className="text-sm font-bold text-blue-600 dark:text-blue-400">
                            {contact.utilisateur_nom.slice(0, 1)}
                        </span>
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-slate-900 dark:text-white">
                            {contact.utilisateur_nom}
                        </p>
                        <span className="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            {contact.role}
                        </span>
                    </div>
                </div>

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
