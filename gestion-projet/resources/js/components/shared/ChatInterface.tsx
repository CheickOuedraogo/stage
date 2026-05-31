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
    const lastIdRef = useRef(initialMessages.length > 0 ? initialMessages[initialMessages.length - 1].id : 0);
    const form = useForm({ message: '' });

    // Sync when Inertia reloads props (e.g. after sending a message)
    useEffect(() => {
        setMessages((current) => {
            const initIds = new Set(initialMessages.map((m) => m.id));
            // Keep polled messages that are newer than what the server returned
            const lastInitId = initialMessages.length > 0 ? initialMessages[initialMessages.length - 1].id : 0;
            const extra = current.filter((m) => !initIds.has(m.id) && m.id > lastInitId);
            const merged = [...initialMessages, ...extra];
            if (merged.length > 0) {
                lastIdRef.current = merged[merged.length - 1].id;
            }
            return merged;
        });
    }, [initialMessages]);

    useEffect(() => {
        bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    // Poll every 3 seconds for incoming messages
    useEffect(() => {
        const poll = async () => {
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
        };

        const interval = setInterval(poll, 3000);
        return () => clearInterval(interval);
    }, [pollUrl]);

    const handleSend = (e: FormEvent) => {
        e.preventDefault();
        if (!form.data.message.trim()) return;
        form.post(sendUrl, {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <div className="w-full max-w-2xl bg-white dark:bg-slate-800 rounded-xl shadow-lg flex flex-col overflow-hidden h-full">
                {/* Header */}
                <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-800/60 shrink-0">
                    <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center shrink-0">
                            <span className="text-xs font-bold text-blue-600 dark:text-blue-400">
                                {contactName.slice(0, 1)}
                            </span>
                        </div>
                        <div>
                            <p className="text-sm font-semibold text-slate-900 dark:text-white">{contactName}</p>
                            <p className="text-xs text-slate-400">Les réponses peuvent prendre un moment</p>
                        </div>
                    </div>
                </div>

                {/* Messages */}
                <div className="flex-1 overflow-y-auto px-4 py-4 space-y-3">
                    {messages.length === 0 ? (
                        <p className="text-center text-sm text-slate-400 mt-8">Aucun message. Envoyez votre première question !</p>
                    ) : (
                        messages.map((msg) => (
                            <div key={msg.id} className={`flex ${msg.is_mine ? 'justify-end' : 'justify-start'}`}>
                                <div className={`max-w-[75%] rounded-2xl px-4 py-2.5 ${
                                    msg.is_mine
                                        ? 'bg-blue-600 text-white rounded-br-sm'
                                        : 'bg-gray-100 dark:bg-slate-800 text-slate-900 dark:text-white rounded-bl-sm'
                                }`}
                                >
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
