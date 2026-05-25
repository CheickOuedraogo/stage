import AppLayout from '@/components/layout/AppLayout';
import { ContactList, type Contact } from '@/components/shared/ContactList';
import { index as chatIndex, show as chatShow } from '@/actions/App/Http/Controllers/Daf/MessageChatControleur';
import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ChatBubbleLeftRightIcon, ChevronDownIcon, MagnifyingGlassIcon, QuestionMarkCircleIcon } from '@heroicons/react/24/outline';
import { useState } from 'react';
import ReactMarkdown from 'react-markdown';

interface FaqItem {
    id: number;
    question: string;
    reponse: string;
}

interface Props extends PageProps {
    contacts: Contact[];
    faq_items: FaqItem[];
}

function FaqAccordion({ items }: { items: FaqItem[] }) {
    const [openId, setOpenId] = useState<number | null>(null);
    const [search, setSearch] = useState('');

    const filtered = search.trim()
        ? items.filter(
              (i) =>
                  i.question.toLowerCase().includes(search.toLowerCase()) ||
                  i.reponse.toLowerCase().includes(search.toLowerCase()),
          )
        : items;

    if (items.length === 0) return null;

    return (
        <div className="mb-8">
            <div className="flex items-center gap-2 mb-3">
                <QuestionMarkCircleIcon className="w-5 h-5 text-blue-500" />
                <h3 className="text-sm font-semibold text-slate-900 dark:text-white">Questions fréquentes</h3>
            </div>
            {items.length > 3 && (
                <div className="relative mb-3">
                    <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                    <input
                        type="search"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Rechercher…"
                        className="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                    />
                </div>
            )}
            <div className="space-y-1.5">
                {filtered.map((item) => (
                    <div
                        key={item.id}
                        className={`border rounded-lg overflow-hidden transition-all duration-150 ${
                            openId === item.id
                                ? 'border-blue-200 dark:border-blue-700'
                                : 'border-gray-200 dark:border-slate-700'
                        }`}
                    >
                        <button
                            onClick={() => setOpenId((prev) => (prev === item.id ? null : item.id))}
                            className={`w-full flex items-center justify-between gap-3 px-4 py-3 text-left text-sm transition-colors ${
                                openId === item.id
                                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                                    : 'bg-white dark:bg-slate-900 text-slate-800 dark:text-white hover:bg-gray-50 dark:hover:bg-slate-800'
                            }`}
                            aria-expanded={openId === item.id}
                        >
                            <span className="font-medium">{item.question}</span>
                            <ChevronDownIcon
                                className={`w-4 h-4 shrink-0 transition-transform duration-200 ${openId === item.id ? 'rotate-180' : ''}`}
                            />
                        </button>
                        {openId === item.id && (
                            <div className="px-4 pb-4 pt-2 bg-white dark:bg-slate-900 border-t border-blue-100 dark:border-blue-900/40">
                                <div className="prose prose-sm dark:prose-invert max-w-none prose-p:text-slate-600 dark:prose-p:text-slate-300">
                                    <ReactMarkdown>{item.reponse}</ReactMarkdown>
                                </div>
                            </div>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}

export default function DafChat() {
    const { contacts, faq_items } = usePage<Props>().props;

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

                <FaqAccordion items={faq_items} />

                <ContactList
                    contacts={contacts}
                    getConversationUrl={(userId) => chatShow.url({ user: userId })}
                    emptyMessage="Aucun contact disponible pour le moment."
                />
            </div>
        </AppLayout>
    );
}
