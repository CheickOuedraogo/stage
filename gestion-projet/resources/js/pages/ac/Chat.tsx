import AppLayout from '@/components/layout/AppLayout';
import { ChatInterface, type ChatMsg } from '@/components/shared/ChatInterface';
import { send as chatSend, poll as chatPoll } from '@/actions/App/Http/Controllers/Ac/ChatController';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';

interface Props extends PageProps {
    messages: ChatMsg[];
}

export default function AcChat() {
    const { messages } = usePage<Props>().props;

    return (
        <AppLayout title="Assistance">
            <Head title="Assistance — Agent Comptable — CIFEU" />

            <div className="max-w-2xl mx-auto">
                <div className="mb-5">
                    <h2 className="text-xl font-bold text-slate-900 dark:text-white">Assistance</h2>
                    <p className="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Questions directement à l'administrateur</p>
                </div>
                <ChatInterface
                    messages={messages}
                    sendUrl={chatSend.url()}
                    pollUrl={chatPoll.url()}
                    contactName="Administrateur"
                />
            </div>
        </AppLayout>
    );
}
