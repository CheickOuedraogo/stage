import {
    CheckIcon,
    PencilIcon,
    PlusIcon,
    TrashIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { Head, useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import {
    store as faqStore,
    update as faqUpdate,
    destroy as faqDestroy,
} from '@/actions/App/Http/Controllers/Administrateur/FaqController';
import AppLayout from '@/components/layout/AppLayout';
import { ConfirmModal } from '@/components/ui/ConfirmModal';
import type { PageProps } from '@/types';

interface Faq {
    id_faq: number;
    faq_question: string;
    faq_reponse: string;
    role_key: string | null;
}

interface Role {
    value: string;
    label: string;
}

interface Props extends PageProps {
    items: Faq[];
    roles: Role[];
}

const ROLE_BADGES: Record<string, { label: string; class: string }> = {
    porteur: { label: 'Porteur', class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' },
    daf: { label: 'DAF', class: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' },
    ac: { label: 'AC', class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' },
};

function RoleSelect({
    value,
    onChange,
}: {
    value: string;
    onChange: (v: string) => void;
}) {
    return (
        <div>
            <label className="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Visible par</label>
            <select
                value={value}
                onChange={(e) => onChange(e.target.value)}
                className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
            >
                <option value="" disabled>Choisir un rôle</option>
                {([{ value: 'porteur', label: 'Porteur' }, { value: 'daf', label: 'DAF' }, { value: 'ac', label: 'AC' }]).map((r) => (
                    <option key={r.value} value={r.value}>{r.label}</option>
                ))}
            </select>
        </div>
    );
}

function CreateForm({ onCancel }: { onCancel: () => void }) {
    const form = useForm({ question: '', reponse: '', role_key: '' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post(faqStore.url(), {
            onSuccess: () => {
                form.reset();
                onCancel();
            },
        });
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
                    maxLength={255}
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
            <RoleSelect
                value={form.data.role_key}
                onChange={(v) => form.setData('role_key', v)}
            />
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

function EditForm({ item, onCancel }: { item: Faq; onCancel: () => void }) {
    const form = useForm({
        question: item.faq_question,
        reponse: item.faq_reponse,
        role_key: item.role_key ?? '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.patch(faqUpdate.url(item.id_faq), { onSuccess: onCancel });
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
            <RoleSelect
                value={form.data.role_key}
                onChange={(v) => form.setData('role_key', v)}
            />
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

function DeleteButton({ item }: { item: Faq }) {
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
                message={`« ${item.faq_question} » sera définitivement supprimée de la FAQ.`}
                confirmLabel="Supprimer"
                onConfirm={() => destroy(faqDestroy.url(item.id_faq), { onSuccess: () => setOpen(false), onError: () => setOpen(false) })}
                onCancel={() => setOpen(false)}
            />
        </>
    );
}

export default function AdminFaqIndex() {
    const { items } = usePage<Props>().props;
    const [showCreate, setShowCreate] = useState(false);
    const [editingId, setEditingId] = useState<number | null>(null);

    const activeCount = items.filter((i) => i.role_key).length;

    return (
        <AppLayout title="Gestion FAQ">
            <Head title="Gestion FAQ — Admin — CIFEU" />

            <div className="max-w-3xl mx-auto">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Gestion de la FAQ</h2>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                            {items.length} question{items.length !== 1 ? 's' : ''} —
                            {activeCount} active{activeCount !== 1 ? 's' : ''}
                        </p>
                    </div>
                    <button
                        onClick={() => {
                            setShowCreate((v) => !v);
                            setEditingId(null);
                        }}
                        className="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors"
                    >
                        <PlusIcon className="w-4 h-4" />
                        {showCreate ? 'Annuler' : 'Nouvelle question'}
                    </button>
                </div>

                {showCreate && (
                    <div className="mb-4">
                        <CreateForm onCancel={() => setShowCreate(false)} />
                    </div>
                )}

                {items.length === 0 && !showCreate ? (
                    <div className="text-center py-16 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl">
                        <p className="text-slate-500 text-sm">Aucune question. Cliquez sur « Nouvelle question » pour commencer.</p>
                    </div>
                ) : (
                    <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl divide-y divide-gray-100 dark:divide-slate-800 overflow-hidden">
                        {items.map((item, idx) => (
                            <div key={item.id_faq}>
                                {editingId === item.id_faq ? (
                                    <div className="p-4 bg-amber-50/50 dark:bg-amber-900/10">
                                        <EditForm item={item} onCancel={() => setEditingId(null)} />
                                    </div>
                                ) : (
                                    <div className={`px-5 py-4 flex items-center gap-4 group hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors ${!item.role_key ? 'opacity-60' : ''}`}>
                                        <span className="shrink-0 w-6 h-6 rounded-full bg-gray-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500 text-xs font-bold flex items-center justify-center">
                                            {idx + 1}
                                        </span>
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center gap-2 flex-wrap">
                                                <p className="text-sm font-medium text-slate-900 dark:text-white truncate">{item.faq_question}</p>
                                                {!item.role_key && (
                                                    <span className="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-slate-700 dark:text-slate-400">
                                                        Inactive
                                                    </span>
                                                )}
                                                {item.role_key && ROLE_BADGES[item.role_key] && (
                                                    <span className={`shrink-0 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium ${ROLE_BADGES[item.role_key].class}`}>
                                                        {ROLE_BADGES[item.role_key].label}
                                                    </span>
                                                )}
                                            </div>
                                            <p className="text-xs text-slate-400 dark:text-slate-500 mt-0.5 line-clamp-1">{item.faq_reponse.replace(/[#*`]/g, '').slice(0, 90)}…</p>
                                        </div>
                                        <div className="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button
                                                onClick={() => {
                                                    setEditingId(item.id_faq);
                                                    setShowCreate(false);
                                                }}
                                                className="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                                title="Modifier"
                                            >
                                                <PencilIcon className="w-4 h-4" />
                                            </button>
                                            <DeleteButton item={item} />
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
