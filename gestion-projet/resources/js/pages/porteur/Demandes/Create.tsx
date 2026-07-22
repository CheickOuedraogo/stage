import { ArrowLeftIcon, DocumentArrowUpIcon, ExclamationTriangleIcon, InformationCircleIcon } from '@heroicons/react/24/outline';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useEffect } from 'react';
import { store as storeAction } from '@/actions/App/Http/Controllers/Porteur/DemandeDepenseController';
import AppLayout from '@/components/layout/AppLayout';
import { Button } from '@/components/ui/Button';
import { formatCurrency } from '@/lib/utils';
import { show as projetsShow } from '@/routes/porteur/projets';

interface Rubrique {
    id: number;
    libelle: string;
    montant_prevu: number;
    description: string | null;
}

interface Props {
    projet: { id: number; titre: string; statut: string; libelle_statut: string };
    convention: { id: number; titre: string; montant_fcfa: number };
    rubriques: Rubrique[];
}

export default function DemandeCreate({ projet, convention, rubriques }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm<{
        rubrique_id: string;
        montant: string;
        objet: string;
        description: string;
        justificatif: File | null;
    }>({
        rubrique_id: '',
        montant: '',
        objet: '',
        description: '',
        justificatif: null,
    });

    const selectedRubrique = rubriques.find((r) => r.id === Number(data.rubrique_id));

    function StatusBanner({ statut, libelleStatut }: { statut: string; libelleStatut: string }) {
        const messages: Record<string, { icon: React.ReactNode; title: string; text: string; className: string }> = {
            en_attente_financement: {
                icon: <InformationCircleIcon className="w-4 h-4 shrink-0" />,
                title: 'Projet en attente de mise en cours',
                text: 'Le DAF doit mettre le projet en cours pour que vous puissiez soumettre des demandes.',
                className: 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-400',
            },
            termine: {
                icon: <ExclamationTriangleIcon className="w-4 h-4 shrink-0" />,
                title: 'Projet terminé',
                text: 'Ce projet est clôturé. Aucune nouvelle demande de dépense ne peut être soumise.',
                className: 'bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-400',
            },
            annule: {
                icon: <ExclamationTriangleIcon className="w-4 h-4 shrink-0" />,
                title: 'Projet annulé',
                text: 'Ce projet a été annulé. Aucune nouvelle demande de dépense ne peut être soumise.',
                className: 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-400',
            },
            suspendu: {
                icon: <ExclamationTriangleIcon className="w-4 h-4 shrink-0" />,
                title: 'Projet suspendu',
                text: 'Ce projet est suspendu temporairement. Les demandes de dépenses sont bloquées jusqu\'à la reprise.',
                className: 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800 text-orange-800 dark:text-orange-400',
            },
        };

        const config = messages[statut];

        if (!config) {
return null;
}

        return (
            <div className={`mb-6 flex gap-3 p-4 rounded-xl border ${config.className}`}>
                {config.icon}
                <div className="flex-1 min-w-0">
                    <h3 className="text-sm font-semibold">{config.title}</h3>
                    <p className="text-xs mt-0.5">{config.text}</p>
                </div>
            </div>
        );
    }

    useEffect(() => {
        if (projet.statut !== 'en_cours') {
            router.visit(projetsShow.url(projet.id), {
                onError: () => {},
            });
        }
    }, [projet.statut]);

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(storeAction.url({ projet: projet.id, convention: convention.id }), {
            forceFormData: true,
        });
    }

    return (
        <AppLayout title="Nouvelle demande">
            <Head title="Nouvelle demande — CIFEU" />

            {/* Breadcrumb */}
            <div className="mb-6 flex items-center gap-2 text-sm">
                <Link
                    href={projetsShow.url(projet.id)}
                    className="flex items-center gap-1.5 text-gray-500 hover:text-gray-900 dark:text-slate-400 dark:hover:text-white transition-colors"
                >
                    <ArrowLeftIcon className="w-3.5 h-3.5" />
                    {projet.titre}
                </Link>
                <span className="text-gray-300 dark:text-slate-600">/</span>
                <span className="text-gray-500 dark:text-slate-400 truncate">{convention.titre}</span>
                <span className="text-gray-300 dark:text-slate-600">/</span>
                <span className="text-gray-900 dark:text-white font-medium">Nouvelle demande</span>
            </div>

            {/* Status banner */}
            <StatusBanner statut={projet.statut} libelleStatut={projet.libelle_statut} />

            <div className="mx-auto w-full max-w-2xl">
                <div className="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6">
                    <h2 className="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                        Nouvelle demande de dépense
                    </h2>

                    <form onSubmit={handleSubmit} className="space-y-5">
                        {/* Rubrique */}
                        <div>
                            <label htmlFor="rubrique_id" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                Rubrique budgétaire <span className="text-red-500">*</span>
                            </label>
                            <select
                                id="rubrique_id"
                                value={data.rubrique_id}
                                onChange={(e) => setData('rubrique_id', e.target.value)}
                                className="w-full px-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                            >
                                <option value="">Sélectionner une rubrique</option>
                                {rubriques.map((r) => (
                                    <option key={r.id} value={r.id}>
                                        {r.libelle} — {formatCurrency(r.montant_prevu)}
                                    </option>
                                ))}
                            </select>
                            {errors.rubrique_id && (
                                <p className="mt-1.5 text-xs text-red-600 dark:text-red-400">{errors.rubrique_id}</p>
                            )}
                            {selectedRubrique && (
                                <p className="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                                    Montant prévu : <span className="font-mono font-medium">{formatCurrency(selectedRubrique.montant_prevu)}</span>
                                </p>
                            )}
                        </div>

                        {/* Montant */}
                        <div>
                            <label htmlFor="montant" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                Montant demandé (FCFA) <span className="text-red-500">*</span>
                            </label>
                            <input
                                id="montant"
                                type="number"
                                min={1}
                                value={data.montant}
                                onChange={(e) => setData('montant', e.target.value)}
                                placeholder="Ex: 500000"
                                className="w-full px-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-mono placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                            />
                            {errors.montant && (
                                <p className="mt-1.5 text-xs text-red-600 dark:text-red-400">{errors.montant}</p>
                            )}
                        </div>

                        {/* Objet */}
                        <div>
                            <label htmlFor="objet" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                Objet de la dépense <span className="text-red-500">*</span>
                            </label>
                            <input
                                id="objet"
                                type="text"
                                value={data.objet}
                                onChange={(e) => setData('objet', e.target.value)}
                                placeholder="Résumé en quelques mots"
                                maxLength={255}
                                className="w-full px-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors"
                            />
                            {errors.objet && (
                                <p className="mt-1.5 text-xs text-red-600 dark:text-red-400">{errors.objet}</p>
                            )}
                        </div>

                        {/* Description */}
                        <div>
                            <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                Description <span className="text-gray-400">(optionnel)</span>
                            </label>
                            <textarea
                                id="description"
                                rows={4}
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                placeholder="Détails supplémentaires sur la dépense…"
                                maxLength={2000}
                                className="w-full px-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-colors resize-none"
                            />
                            {errors.description && (
                                <p className="mt-1.5 text-xs text-red-600 dark:text-red-400">{errors.description}</p>
                            )}
                        </div>

                        {/* Justificatif PDF */}
                        <div>
                            <label htmlFor="justificatif" className="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">
                                Justificatif (PDF) <span className="text-gray-400">(optionnel)</span>
                            </label>
                            <div className="relative">
                                <input
                                    id="justificatif"
                                    type="file"
                                    accept=".pdf"
                                    onChange={(e) => setData('justificatif', e.target.files?.[0] ?? null)}
                                    className="w-full px-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-400 hover:file:bg-blue-100 transition-colors"
                                />
                            </div>
                            <p className="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                                Format PDF uniquement, taille max 10 Mo
                            </p>
                            {errors.justificatif && (
                                <p className="mt-1.5 text-xs text-red-600 dark:text-red-400">{errors.justificatif}</p>
                            )}
                        </div>

                        <div className="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 dark:border-slate-700">
                            <Link
                                href={projetsShow.url(projet.id)}
                                className="px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                            >
                                Annuler
                            </Link>
                            <Button type="submit" loading={processing}>
                                <DocumentArrowUpIcon className="w-4 h-4" />
                                Soumettre la demande
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
