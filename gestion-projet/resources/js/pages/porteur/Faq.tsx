import AppLayout from '@/components/layout/AppLayout';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { ChevronDownIcon, QuestionMarkCircleIcon } from '@heroicons/react/24/outline';
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
        <div className="border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
            <button
                onClick={onToggle}
                className="w-full flex items-center justify-between gap-4 px-5 py-4 text-left bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors"
                aria-expanded={isOpen}
            >
                <div className="flex items-center gap-3">
                    <QuestionMarkCircleIcon className="w-5 h-5 text-blue-500 shrink-0" />
                    <span className="text-sm font-semibold text-slate-900 dark:text-white">{item.question}</span>
                </div>
                <ChevronDownIcon
                    className={`w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`}
                />
            </button>
            {isOpen && (
                <div className="px-5 py-4 bg-blue-50/50 dark:bg-slate-800/50 border-t border-gray-100 dark:border-slate-700">
                    <div className="ml-8 prose prose-sm dark:prose-invert max-w-none prose-p:text-slate-600 dark:prose-p:text-slate-300 prose-li:text-slate-600 dark:prose-li:text-slate-300">
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

    const toggle = (id: number) => setOpenId((prev) => (prev === id ? null : id));

    return (
        <AppLayout title="Assistance">
            <Head title="Assistance — CIFEU" />

            <div className="mb-8">
                <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Centre d'assistance</h2>
                <p className="text-sm text-slate-500 mt-1">Réponses aux questions fréquentes sur l'utilisation de CIFEU</p>
            </div>

            {items.length === 0 ? (
                <div className="text-center py-16">
                    <QuestionMarkCircleIcon className="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <p className="text-slate-500">Aucune question disponible pour le moment.</p>
                </div>
            ) : (
                <div className="max-w-3xl space-y-3">
                    {items.map((item) => (
                        <AccordionItem
                            key={item.id}
                            item={item}
                            isOpen={openId === item.id}
                            onToggle={() => toggle(item.id)}
                        />
                    ))}
                </div>
            )}
        </AppLayout>
    );
}
