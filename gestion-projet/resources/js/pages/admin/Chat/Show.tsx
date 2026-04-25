import AppLayout from '@/components/layout/AppLayout';
import { ChatInterface, type ChatMsg } from '@/components/shared/ChatInterface';
import {
    index as chatIndex,
    send as chatSend,
    poll as chatPoll,
} from '@/actions/App/Http/Controllers/Admin/ChatController';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';

interface Contact {
    id: number;
    name: string;
    role: string;
}

interface Props extends PageProps {
    contact: Contact;
    messages: ChatMsg[];
}

export default function AdminChatShow() {
    const { contact, messages } = usePage<Props>().props;

    return (
        <AppLayout title={`Messages — ${contact.name}`}>
            <Head title={`Messages — ${contact.name} — Admin — CIFEU`} />

            <div className="mb-6 flex items-center gap-3">
                <Link
                    href={chatIndex.url()}
                    className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-500 dark:text-slate-400 transition-colors"
                >
                    <ArrowLeftIcon className="w-4 h-4" />
                </Link>
                <div>
                    <h2 className="text-xl font-bold text-slate-900 dark:text-white">{contact.name}</h2>
                    <p className="text-sm text-slate-500">{contact.role}</p>
                </div>
            </div>

            <div className="max-w-2xl">
                <ChatInterface
                    messages={messages}
                    sendUrl={chatSend.url(contact.id)}
                    pollUrl={chatPoll.url(contact.id)}
                    contactName={contact.name}
                />
            </div>
        </AppLayout>
    );
}
