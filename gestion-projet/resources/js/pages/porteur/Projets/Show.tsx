import AppLayout from '@/components/layout/AppLayout';
import { MarkdownRenderer } from '@/components/ui/MarkdownRenderer';
import { formatCurrency, formatDate, projectStatusClass, conventionStatusClass } from '@/lib/utils';
import { index as projetsIndex, show as projetsShow } from '@/routes/porteur/projets';
import { show as projetsConventionsShow } from '@/routes/porteur/projets/conventions';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon, BanknotesIcon, BuildingLibraryIcon, DocumentTextIcon } from '@heroicons/react/24/outline';

interface Convention {
    id: number;
    titre: string;
    bailleur: { nom: string; sigle: string };
    montant_fcfa: number;
    forme: string;
    forme_label: string;
    status: string;
    status_label: string;
    date_fin: string | null;
    total_versements: number;
    versements_count: number;
}

interface Projet {
    id: number;
    titre: string;
    description: string | null;
    objectifs: string | null;
    activites: string | null;
    status: string;
    status_label: string;
    montant_estime: number;
    montant_conventions: number;
    total_versements: number;
    pourcentage_financement: number;
    date_debut: string | null;
    date_fin_prevue: string | null;
    date_fin_reelle: string | null;
    conventions: Convention[];
}

interface Props {
    projet: Projet;
}

export default function ProjetShow({ projet }: Props) {
    const tauxVersements = projet.montant_conventions > 0
        ? Math.min(100, Math.round((projet.total_versements / projet.montant_conventions) * 100))
        : 0;

    return (
        <AppLayout title={projet.titre}>
            <Head title={`${projet.titre} — CIFEU`} />

            <div className="mb-6 flex items-center gap-3">
                <Link
                    href={projetsIndex.url()}
                    className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-500 dark:text-slate-400 transition-colors"
                    aria-label="Retour à mes projets"
                >
                    <ArrowLeftIcon className="w-4 h-4" />
                </Link>
                <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-3 flex-wrap">
                        <h2 className="text-2xl font-bold text-gray-900 dark:text-white truncate">{projet.titre}</h2>
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${projectStatusClass(projet.status)}`}>
                            {projet.status_label}
                        </span>
                    </div>
                    {projet.date_debut && (
                        <p className="text-sm text-gray-500 dark:text-slate-400 mt-0.5">
                            Début : {formatDate(projet.date_debut)}
                            {projet.date_fin_prevue && ` · Fin prévue : ${formatDate(projet.date_fin_prevue)}`}
                            {projet.date_fin_reelle && ` · Terminé le : ${formatDate(projet.date_fin_reelle)}`}
                        </p>
                    )}
                </div>
            </div>

            {/* Stats cards */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <StatCard
                    label="Montant estimé"
                    value={formatCurrency(projet.montant_estime)}
                    icon={<BanknotesIcon className="w-5 h-5 text-gray-400 dark:text-slate-500" />}
                />
                <StatCard
                    label="Fonds mobilisés (conventions)"
                    value={formatCurrency(projet.montant_conventions)}
                    sub={`${projet.pourcentage_financement}% du budget estimé`}
                    icon={<BuildingLibraryIcon className="w-5 h-5 text-gray-400 dark:text-slate-500" />}
                    progressValue={projet.pourcentage_financement}
                />
                <StatCard
                    label="Versements reçus"
                    value={formatCurrency(projet.total_versements)}
                    sub={`${tauxVersements}% des conventions`}
                    icon={<DocumentTextIcon className="w-5 h-5 text-gray-400 dark:text-slate-500" />}
                    progressValue={tauxVersements}
                />
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-5">
                    {projet.description && (
                        <Section title="Description">
                            <MarkdownRenderer content={projet.description} />
                        </Section>
                    )}
                    {projet.objectifs && (
                        <Section title="Objectifs">
                            <MarkdownRenderer content={projet.objectifs} />
                        </Section>
                    )}
                    {projet.activites && (
                        <Section title="Activités">
                            <MarkdownRenderer content={projet.activites} />
                        </Section>
                    )}
                </div>

                <div>
                    <Section title={`Conventions (${projet.conventions.length})`}>
                        {projet.conventions.length === 0 ? (
                            <p className="text-sm text-gray-400 dark:text-slate-500 text-center py-6">Aucune convention</p>
                        ) : (
                            <div className="space-y-3">
                                {projet.conventions.map((c) => (
                                    <Link
                                        key={c.id}
                                        href={projetsConventionsShow.url({ projet: projet.id, convention: c.id })}
                                        className="block group"
                                    >
                                        <div className="border border-gray-200 dark:border-slate-700 rounded-lg p-3 hover:border-gray-300 dark:hover:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-800 transition-all">
                                            <div className="flex items-start justify-between gap-2 mb-2">
                                                <p className="text-xs font-semibold text-gray-900 dark:text-white leading-tight">
                                                    {c.bailleur.sigle ?? c.bailleur.nom}
                                                </p>
                                                <span className={`shrink-0 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium ${conventionStatusClass(c.status)}`}>
                                                    {c.status_label}
                                                </span>
                                            </div>
                                            <p className="text-xs text-gray-500 dark:text-slate-400 mb-2 line-clamp-1">{c.titre}</p>
                                            <div className="flex items-center justify-between text-xs">
                                                <span className="font-mono text-gray-900 dark:text-white">{formatCurrency(c.montant_fcfa)}</span>
                                                <span className="text-gray-400 dark:text-slate-500">{c.forme_label}</span>
                                            </div>
                                            <div className="mt-1.5 text-xs text-gray-400 dark:text-slate-500">
                                                {c.versements_count} versement{c.versements_count !== 1 ? 's' : ''} · {formatCurrency(c.total_versements)} reçus
                                            </div>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        )}
                    </Section>
                </div>
            </div>
        </AppLayout>
    );
}

function StatCard({ label, value, sub, icon, progressValue }: {
    label: string;
    value: string;
    sub?: string;
    icon: React.ReactNode;
    progressValue?: number;
}) {
    return (
        <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5">
            <div className="flex items-center gap-2 mb-2">
                {icon}
                <p className="text-xs text-gray-500 dark:text-slate-400">{label}</p>
            </div>
            <p className="text-lg font-mono font-bold text-gray-900 dark:text-white">{value}</p>
            {sub && <p className="text-xs text-gray-400 dark:text-slate-500 mt-0.5">{sub}</p>}
            {progressValue !== undefined && (
                <div className="mt-2 w-full h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div className="h-full bg-blue-500 dark:bg-blue-400 rounded-full" style={{ width: `${progressValue}%` }} />
                </div>
            )}
        </div>
    );
}

function Section({ title, children }: { title: string; children: React.ReactNode }) {
    return (
        <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
            <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-white">{title}</h3>
            </div>
            <div className="p-5">{children}</div>
        </div>
    );
}
