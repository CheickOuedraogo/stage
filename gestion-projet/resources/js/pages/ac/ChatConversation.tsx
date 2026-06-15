import { ArrowLeftIcon, InformationCircleIcon } from '@heroicons/react/24/outline';
import { Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { index as chatIndex, send as chatSend, poll as chatPoll } from '@/actions/App/Http/Controllers/AgentComptable/MessageChatControleur';
import AppLayout from '@/components/layout/AppLayout';
import { ChatInterface  } from '@/components/shared/ChatInterface';
import type {ChatMsg} from '@/components/shared/ChatInterface';
import type { PageProps } from '@/types';

interface Contact {
    id: number;
    utilisateur_nom: string;
    utilisateur_email: string;
    role: string;
    role_key: string;
    projets?: { id: number; titre: string }[];
}

interface Props extends PageProps {
    contact: Contact;
    messages: ChatMsg[];
}

function InfoPopover({ contact }: { contact: Contact }) {
    const [open, setOpen] = useState(false);

    return (
        <div className="relative">
            <button
                onClick={() => setOpen(!open)}
                className="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                aria-label="Voir les informations"
                title="Informations"
            >
                <InformationCircleIcon className="w-5 h-5" />
            </button>
            {open && (
                <>
                    <div className="fixed inset-0 z-10" onClick={() => setOpen(false)} />
                    <div className="absolute right-0 top-full mt-2 z-20 w-72 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-lg p-4">
                        <p className="text-sm font-semibold text-slate-900 dark:text-white mb-2">
                            {contact.utilisateur_nom}
                        </p>
                        <p className="text-xs text-slate-500 dark:text-slate-400 mb-1">
                            {contact.utilisateur_email}
                        </p>
                        <span className="inline-block px-2 py-0.5 text-[10px] font-semibold rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 mb-3">
                            {contact.role}
                        </span>
                        {contact.projets && contact.projets.length > 0 && (
                            <div>
                                <p className="text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                    Projets :
                                </p>
                                <ul className="space-y-1">
                                    {contact.projets.map((p) => (
                                        <li key={p.id} className="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                            <span className="w-1 h-1 rounded-full bg-blue-400 shrink-0" />
                                            {p.titre}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                    </div>
                </>
            )}
        </div>
    );
}

export default function AcChatConversation() {
    const { contact, messages } = usePage<Props>().props;

    return (
        <AppLayout title={`Discussion avec ${contact.utilisateur_nom}`}>
            <Head title={`${contact.utilisateur_nom} — Assistance AC — CIFEU`} />

            <div className="max-w-4xl mx-auto h-full flex flex-col">
                <div className="mb-4 shrink-0">
                    <Link
                        href={chatIndex.url()}
                        className="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors"
                    >
                        <ArrowLeftIcon className="w-4 h-4" />
                        Retour à l'assistance
                    </Link>
                </div>

                <div className="flex items-center justify-between mb-4 shrink-0">
                    <div className="flex items-center gap-3">
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
                    {contact.role_key === 'porteur' && <InfoPopover contact={contact} />}
                </div>

                <div className="flex-1 min-h-0">
                    <ChatInterface
                    messages={messages}
                    sendUrl={chatSend.url({ user: contact.id })}
                    pollUrl={chatPoll.url({ user: contact.id })}
                    contactName={contact.utilisateur_nom}
                />
                </div>
            </div>
        </AppLayout>
    );
}
