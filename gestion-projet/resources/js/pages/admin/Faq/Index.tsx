import AppLayout from '@/components/layout/AppLayout';
import { ConfirmModal } from '@/components/ui/ConfirmModal';
import {
    store as faqStore,
    update as faqUpdate,
    destroy as faqDestroy,
} from '@/actions/App/Http/Controllers/Admin/FaqController';
import type { PageProps } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import {
    ArrowsUpDownIcon,
    CheckIcon,
    PencilIcon,
    PlusIcon,
    TrashIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { FormEvent, useState } from 'react';

interface FaqItem {
    id: number;
    question: string;
    reponse: string;
    ordre: number;
    is_active: boolean;
}

interface Props extends PageProps {
    items: FaqItem[];
}

function CreateForm({ onCancel }: { onCancel: () => void }) {
    const form = useForm({ question: '', reponse: '', ordre: '' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post(faqStore.url(), { onSuccess: () => { form.reset(); onCancel(); } });
    };

    return (
        <form onSubmit={submit} className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-5 space-y-4">
            <h3 className="text-sm font-semibold text-blue-800 dark:text-blue-300">Nouvelle question</h3>
            <div>
                <label className="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Question <span className="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    value={form.data.question}
                    onChange={(e) => form.setData('question', e.target.value)}
                    required
                    maxLength={300}
                    placeholder="Ex : Comment soumettre une demande ?"
                    className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                />
                {form.errors.question && <p className="text-xs text-red-600 mt-1">{form.errors.question}</p>}
            </div>
            <div>
                <label className="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                    Réponse (Markdown) <span className="text-red-500">*</span>
                </label>
                <textarea
                    value={form.data.reponse}
                    onChange={(e) => form.setData('reponse', e.target.value)}
                    required
                    rows={5}
                    placeholder="Rédigez la réponse en Markdown…"
                    className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 resize-y font-mono"
                />
                {form.errors.reponse && <p className="text-xs text-red-600 mt-1">{form.errors.reponse}</p>}
            </div>
            <div className="flex gap-3">
                <button
                    type="submit"
                    disabled={form.processing}
                    className="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50 transition-colors"
                >
                    <CheckIcon className="w-4 h-4" />
                    {form.processing ? 'Enregistrement…' : 'Ajouter'}
                </button>
                <button
                    type="button"
                    onClick={onCancel}
                    className="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                >
                    <XMarkIcon className="w-4 h-4" />
                    Annuler
                </button>
            </div>
        </form>
    );
}

function EditForm({ item, onCancel }: { item: FaqItem; onCancel: () => void }) {
    const form = useForm({
        question: item.question,
        reponse: item.reponse,
        ordre: String(item.ordre),
        is_active: item.is_active,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.patch(faqUpdate.url(item.id), { onSuccess: onCancel });
    };

    return (
        <form onSubmit={submit} className="space-y-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl">
            <div>
                <label className="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Question</label>
                <input
                    type="text"
                    value={form.data.question}
                    onChange={(e) => form.setData('question', e.target.value)}
                    required
                    className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50"
                />
            </div>
            <div>
                <label className="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Réponse (Markdown)</label>
                <textarea
                    value={form.data.reponse}
                    onChange={(e) => form.setData('reponse', e.target.value)}
                    required
                    rows={5}
                    className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/50 resize-y font-mono"
                />
            </div>
            <div className="flex items-center justify-between">
                <label className="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        checked={form.data.is_active}
                        onChange={(e) => form.setData('is_active', e.target.checked)}
                        className="rounded text-blue-600"
                    />
                    <span className="text-sm text-slate-700 dark:text-slate-300">Visible dans la FAQ</span>
                </label>
                <div className="flex items-center gap-1.5">
                    <label className="text-xs text-slate-500">Ordre :</label>
                    <input
                        type="number"
                        value={form.data.ordre}
                        onChange={(e) => form.setData('ordre', e.target.value)}
                        min={0}
                        className="w-16 px-2 py-1 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none"
                    />
                </div>
            </div>
            <div className="flex gap-3">
                <button
                    type="submit"
                    disabled={form.processing}
                    className="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-amber-600 hover:bg-amber-700 text-white disabled:opacity-50 transition-colors"
                >
                    <CheckIcon className="w-4 h-4" />
                    {form.processing ? 'Enregistrement…' : 'Mettre à jour'}
                </button>
                <button
                    type="button"
                    onClick={onCancel}
                    className="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                >
                    Annuler
                </button>
            </div>
        </form>
    );
}

function DeleteButton({ item }: { item: FaqItem }) {
    const { delete: destroy, processing } = useForm({});
    const [open, setOpen] = useState(false);

    const handleDelete = () => setOpen(true);

    return (
        <>
            <button
                onClick={handleDelete}
                disabled={processing}
                className="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                title="Supprimer"
            >
                <TrashIcon className="w-4 h-4" />
            </button>
            <ConfirmModal
                open={open}
                title="Supprimer la question"
                message={`« ${item.question} » sera définitivement supprimée de la FAQ.`}
                confirmLabel="Supprimer"
                onConfirm={() => { destroy(faqDestroy.url(item.id)); setOpen(false); }}
                onCancel={() => setOpen(false)}
            />
        </>
    );
}

export default function AdminFaqIndex() {
    const { items } = usePage<Props>().props;
    const [showCreate, setShowCreate] = useState(false);
    const [editingId, setEditingId] = useState<number | null>(null);

    return (
        <AppLayout title="Gestion FAQ">
            <Head title="Gestion FAQ — Admin — CIFEU" />

            <div className="mb-6 flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Gestion de la FAQ</h2>
                    <p className="text-sm text-slate-500 mt-1">{items.length} question{items.length !== 1 ? 's' : ''} enregistrée{items.length !== 1 ? 's' : ''}</p>
                </div>
                <button
                    onClick={() => { setShowCreate(true); setEditingId(null); }}
                    className="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors"
                >
                    <PlusIcon className="w-4 h-4" />
                    Nouvelle question
                </button>
            </div>

            <div className="space-y-4">
                {showCreate && (
                    <CreateForm onCancel={() => setShowCreate(false)} />
                )}

                {items.length === 0 && !showCreate ? (
                    <div className="text-center py-16 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl">
                        <p className="text-slate-500">Aucune question. Cliquez sur « Nouvelle question » pour commencer.</p>
                    </div>
                ) : (
                    items.map((item) => (
                        <div key={item.id} className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                            {editingId === item.id ? (
                                <div className="p-4">
                                    <EditForm item={item} onCancel={() => setEditingId(null)} />
                                </div>
                            ) : (
                                <div className="px-5 py-4">
                                    <div className="flex items-start justify-between gap-4">
                                        <div className="flex items-center gap-2 min-w-0 flex-1">
                                            <ArrowsUpDownIcon className="w-4 h-4 text-slate-300 shrink-0" />
                                            <div className="min-w-0">
                                                <div className="flex items-center gap-2 flex-wrap">
                                                    <p className="text-sm font-semibold text-slate-900 dark:text-white">{item.question}</p>
                                                    {!item.is_active && (
                                                        <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-slate-400">
                                                            Masquée
                                                        </span>
                                                    )}
                                                </div>
                                                <p className="text-xs text-slate-400 mt-0.5 font-mono line-clamp-1">{item.reponse.slice(0, 100)}…</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-1 shrink-0">
                                            <span className="text-xs text-slate-400 font-mono mr-2">#{item.ordre}</span>
                                            <button
                                                onClick={() => { setEditingId(item.id); setShowCreate(false); }}
                                                className="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                                title="Modifier"
                                            >
                                                <PencilIcon className="w-4 h-4" />
                                            </button>
                                            <DeleteButton item={item} />
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    ))
                )}
            </div>
        </AppLayout>
    );
}
