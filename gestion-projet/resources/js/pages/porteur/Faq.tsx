import AppLayout from '@/components/layout/AppLayout';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { ChevronDownIcon, MagnifyingGlassIcon, QuestionMarkCircleIcon } from '@heroicons/react/24/outline';
import { useState } from 'react';
import ReactMarkdown from 'react-markdown';

interface FaqItem {
    id: number;
    question: string;
    reponse: string;
}

interface Props extends PageProps {
    items: FaqItem[];
}

function AccordionItem({ item, isOpen, onToggle }: { item: FaqItem; isOpen: boolean; onToggle: () => void }) {
    return (
        <div className={`border rounded-xl overflow-hidden transition-all duration-150 ${
            isOpen
                ? 'border-blue-200 dark:border-blue-700 shadow-sm'
                : 'border-gray-200 dark:border-slate-700'
        }`}>
            <button
                onClick={onToggle}
                className={`w-full flex items-center justify-between gap-4 px-5 py-4 text-left transition-colors ${
                    isOpen
                        ? 'bg-blue-50 dark:bg-blue-900/20'
                        : 'bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800'
                }`}
                aria-expanded={isOpen}
            >
                <div className="flex items-center gap-3 min-w-0">
                    <div className={`shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-colors ${
                        isOpen ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500'
                    }`}>
                        ?
                    </div>
                    <span className={`text-sm font-medium truncate ${isOpen ? 'text-blue-700 dark:text-blue-300' : 'text-slate-800 dark:text-white'}`}>
                        {item.question}
                    </span>
                </div>
                <ChevronDownIcon
                    className={`w-4 h-4 shrink-0 transition-transform duration-200 ${
                        isOpen ? 'rotate-180 text-blue-500' : 'text-slate-400'
                    }`}
                />
            </button>
            {isOpen && (
                <div className="px-5 pb-5 pt-3 bg-white dark:bg-slate-900 border-t border-blue-100 dark:border-blue-900/40">
                    <div className="ml-10 prose prose-sm dark:prose-invert max-w-none prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-li:text-slate-600 dark:prose-li:text-slate-300 prose-headings:text-slate-800 dark:prose-headings:text-white">
                        <ReactMarkdown>{item.reponse}</ReactMarkdown>
                    </div>
                </div>
            )}
        </div>
    );
}

export default function PorteurFaq() {
    const { items } = usePage<Props>().props;
    const [openId, setOpenId] = useState<number | null>(items[0]?.id ?? null);
    const [search, setSearch] = useState('');

    const toggle = (id: number) => setOpenId((prev) => (prev === id ? null : id));

    const filtered = search.trim()
        ? items.filter(
            (i) =>
                i.question.toLowerCase().includes(search.toLowerCase()) ||
                i.reponse.toLowerCase().includes(search.toLowerCase()),
        )
        : items;

    return (
        <AppLayout title="Assistance">
            <Head title="Assistance — CIFEU" />

            <div className="max-w-2xl mx-auto">
                {/* Header */}
                <div className="text-center mb-8">
                    <div className="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-900/40 mb-4">
                        <QuestionMarkCircleIcon className="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Centre d'assistance</h2>
                    <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Réponses aux questions fréquentes sur l'utilisation de CIFEU
                    </p>
                </div>

                {/* Search */}
                {items.length > 3 && (
                    <div className="relative mb-6">
                        <MagnifyingGlassIcon className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                        <input
                            type="search"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Rechercher une question…"
                            className="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-300 dark:focus:border-blue-600"
                        />
                    </div>
                )}

                {/* Content */}
                {items.length === 0 ? (
                    <div className="text-center py-16 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-2xl">
                        <QuestionMarkCircleIcon className="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                        <p className="text-sm font-medium text-slate-600 dark:text-slate-300">Aucune question disponible</p>
                        <p className="text-xs text-slate-400 mt-1">Revenez plus tard ou contactez l'administrateur.</p>
                    </div>
                ) : filtered.length === 0 ? (
                    <div className="text-center py-12 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-2xl">
                        <p className="text-sm text-slate-500">Aucun résultat pour « {search} »</p>
                        <button onClick={() => setSearch('')} className="text-xs text-blue-600 hover:underline mt-2">
                            Effacer la recherche
                        </button>
                    </div>
                ) : (
                    <div className="space-y-2">
                        {filtered.map((item) => (
                            <AccordionItem
                                key={item.id}
                                item={item}
                                isOpen={openId === item.id}
                                onToggle={() => toggle(item.id)}
                            />
                        ))}
                        <p className="text-center text-xs text-slate-400 dark:text-slate-500 pt-2">
                            {filtered.length} question{filtered.length !== 1 ? 's' : ''}
                            {search && ` pour « ${search} »`}
                        </p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
