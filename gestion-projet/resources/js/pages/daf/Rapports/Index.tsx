import AppLayout from '@/components/layout/AppLayout';
import { executionBudgetaire, cloture as clotureRapport } from '@/actions/App/Http/Controllers/Daf/RapportController';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import {
    ArrowDownTrayIcon,
    ChartBarIcon,
    DocumentChartBarIcon,
} from '@heroicons/react/24/outline';
import { FormEvent, useState } from 'react';

interface Projet {
    id: number;
    titre: string;
    status: string;
    status_label: string;
}

interface Props extends PageProps {
    projets: Projet[];
}

function RapportForm({
    title,
    description,
    icon: Icon,
    projets,
    action,
    color,
}: {
    title: string;
    description: string;
    icon: React.ElementType;
    projets: Projet[];
    action: (options?: { query?: Record<string, string> }) => { url: string };
    color: string;
}) {
    const [projetId, setProjetId] = useState('');
    const [format, setFormat] = useState<'pdf' | 'excel'>('pdf');

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (!projetId) return;
        const url = action({ query: { projet_id: projetId, format } }).url;
        window.open(url, '_blank');
    };

    return (
        <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
            <div className="px-5 py-4 border-b border-gray-100 dark:border-slate-800 flex items-center gap-3">
                <div className={`w-9 h-9 rounded-lg flex items-center justify-center ${color}`}>
                    <Icon className="w-5 h-5" />
                </div>
                <div>
                    <h3 className="text-sm font-semibold text-slate-900 dark:text-white">{title}</h3>
                    <p className="text-xs text-slate-500 mt-0.5">{description}</p>
                </div>
            </div>
            <form onSubmit={handleSubmit} className="p-5 space-y-4">
                <div>
                    <label className="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Projet <span className="text-red-500">*</span>
                    </label>
                    <select
                        value={projetId}
                        onChange={(e) => setProjetId(e.target.value)}
                        required
                        className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                    >
                        <option value="">Sélectionner un projet…</option>
                        {projets.map((p) => (
                            <option key={p.id} value={p.id}>{p.titre} ({p.status_label})</option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Format</label>
                    <div className="flex gap-3">
                        {(['pdf', 'excel'] as const).map((f) => (
                            <label key={f} className="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    name={`format-${title}`}
                                    value={f}
                                    checked={format === f}
                                    onChange={() => setFormat(f)}
                                    className="text-blue-600"
                                />
                                <span className="text-sm text-slate-700 dark:text-slate-300 uppercase font-medium">{f}</span>
                            </label>
                        ))}
                    </div>
                </div>

                <button
                    type="submit"
                    disabled={!projetId}
                    className="w-full flex items-center justify-center gap-2 py-2.5 px-4 text-sm font-medium rounded-lg bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <ArrowDownTrayIcon className="w-4 h-4" />
                    Télécharger le rapport {format.toUpperCase()}
                </button>
            </form>
        </div>
    );
}

export default function DafRapportsIndex() {
    const { projets } = usePage<Props>().props;

    return (
        <AppLayout title="Rapports">
            <Head title="Rapports — DAF — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-slate-900 dark:text-white">Génération de rapports</h2>
                <p className="text-sm text-slate-500 mt-1">Exportez vos rapports financiers au format PDF ou Excel</p>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <RapportForm
                    title="Rapport d'exécution budgétaire"
                    description="Analyse des rubriques : prévu vs dépensé vs disponible"
                    icon={ChartBarIcon}
                    projets={projets}
                    action={executionBudgetaire}
                    color="bg-blue-100 text-blue-600"
                />

                <RapportForm
                    title="Rapport de clôture de projet"
                    description="Analyse des écarts temps et budget avec synthèse des conventions"
                    icon={DocumentChartBarIcon}
                    projets={projets}
                    action={clotureRapport}
                    color="bg-emerald-100 text-emerald-600"
                />
            </div>
        </AppLayout>
    );
}
