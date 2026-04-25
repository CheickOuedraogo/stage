import { useForm } from '@inertiajs/react';
import { PaperAirplaneIcon } from '@heroicons/react/24/outline';
import { FormEvent, useEffect, useRef, useState } from 'react';

export interface ChatMsg {
    id: number;
    message: string;
    is_mine: boolean;
    sender_name: string;
    created_at: string;
}

interface Props {
    messages: ChatMsg[];
    sendUrl: string;
    pollUrl: string;
    contactName: string;
}

export function ChatInterface({ messages: initialMessages, sendUrl, pollUrl, contactName }: Props) {
    const [messages, setMessages] = useState<ChatMsg[]>(initialMessages);
    const bottomRef = useRef<HTMLDivElement>(null);
    const form = useForm({ message: '' });

    const lastId = messages.length > 0 ? messages[messages.length - 1].id : 0;
    const lastIdRef = useRef(lastId);

    useEffect(() => {
        bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    // Polling toutes les 4 secondes pour les nouveaux messages
    useEffect(() => {
        const interval = setInterval(async () => {
            try {
                const res = await fetch(`${pollUrl}?since=${lastIdRef.current}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const data = await res.json() as { messages: ChatMsg[] };
                if (data.messages.length > 0) {
                    setMessages((prev) => {
                        const ids = new Set(prev.map((m) => m.id));
                        const newMsgs = data.messages.filter((m) => !ids.has(m.id));
                        if (newMsgs.length === 0) return prev;
                        lastIdRef.current = Math.max(...newMsgs.map((m) => m.id));
                        return [...prev, ...newMsgs];
                    });
                }
            } catch {
                // ignore network errors silently
            }
        }, 4000);

        return () => clearInterval(interval);
    }, [pollUrl]);

    const handleSend = (e: FormEvent) => {
        e.preventDefault();
        if (!form.data.message.trim()) return;
        form.post(sendUrl, {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <div className="flex flex-col h-[600px] bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
            {/* Header */}
            <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                <p className="text-sm font-semibold text-slate-900 dark:text-white">Conversation avec {contactName}</p>
                <p className="text-xs text-slate-400 mt-0.5">Les réponses peuvent prendre un moment</p>
            </div>

            {/* Messages */}
            <div className="flex-1 overflow-y-auto px-4 py-4 space-y-3">
                {messages.length === 0 ? (
                    <p className="text-center text-sm text-slate-400 mt-8">
                        Aucun message. Envoyez votre première question !
                    </p>
                ) : (
                    messages.map((msg) => (
                        <div key={msg.id} className={`flex ${msg.is_mine ? 'justify-end' : 'justify-start'}`}>
                            <div className={`max-w-[75%] rounded-2xl px-4 py-2.5 ${
                                msg.is_mine
                                    ? 'bg-blue-600 text-white rounded-br-sm'
                                    : 'bg-gray-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-bl-sm'
                            }`}>
                                <p className="text-sm whitespace-pre-wrap wrap-break-word">{msg.message}</p>
                                <p className={`text-xs mt-1 ${msg.is_mine ? 'text-blue-200' : 'text-slate-400'}`}>
                                    {new Date(msg.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
                                </p>
                            </div>
                        </div>
                    ))
                )}
                <div ref={bottomRef} />
            </div>

            {/* Input */}
            <form onSubmit={handleSend} className="px-4 py-3 border-t border-gray-100 dark:border-slate-800 flex gap-3 bg-white dark:bg-slate-900">
                <textarea
                    value={form.data.message}
                    onChange={(e) => form.setData('message', e.target.value)}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleSend(e as unknown as FormEvent); }
                    }}
                    placeholder="Écrivez votre message… (Entrée pour envoyer)"
                    rows={2}
                    className="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 resize-none"
                />
                <button
                    type="submit"
                    disabled={form.processing || !form.data.message.trim()}
                    className="self-end p-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <PaperAirplaneIcon className="w-5 h-5" />
                </button>
            </form>
        </div>
    );
}
