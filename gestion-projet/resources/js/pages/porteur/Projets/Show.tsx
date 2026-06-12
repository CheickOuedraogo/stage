import AppLayout from '@/components/layout/AppLayout';
import { MarkdownRenderer } from '@/components/ui/MarkdownRenderer';
import { formatCurrency, formatDate, projectStatusClass, conventionStatusClass } from '@/lib/utils';
import { index as projetsIndex, show as projetsShow } from '@/routes/porteur/projets';
import { show as projetsConventionsShow } from '@/routes/porteur/projets/conventions';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeftIcon, BanknotesIcon, BuildingLibraryIcon, DocumentTextIcon, ExclamationTriangleIcon, InformationCircleIcon, LockClosedIcon } from '@heroicons/react/24/outline';

interface Convention {
    id: number;
    titre: string;
    bailleur: { nom: string; sigle: string };
    montant_fcfa: number;
    forme: string;
    forme_label: string;
    statut: string;
    libelle_statut: string;
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
    statut: string;
    libelle_statut: string;
    montant_estime: number;
    montant_conventions: number;
    total_versements: number;
    total_consomme: number;
    disponible_caisse: number;
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
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${projectStatusClass(projet.statut)}`}>
                            {projet.libelle_statut}
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

            {/* Banner restriction selon statut */}
            <StatusBanner statut={projet.statut} libelleStatut={projet.libelle_statut} />

            {/* Stats cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <StatCard
                    label="Fonds mobilisés"
                    value={formatCurrency(projet.montant_conventions)}
                    sub={`${projet.pourcentage_financement}% du budget estimé`}
                    icon={<BuildingLibraryIcon className="w-5 h-5 text-gray-400 dark:text-slate-500" />}
                    progressValue={projet.pourcentage_financement}
                />
                <StatCard
                    label="Versements reçus"
                    value={formatCurrency(projet.total_versements)}
                    sub={`${tauxVersements}% des conventions`}
                    icon={<BanknotesIcon className="w-5 h-5 text-gray-400 dark:text-slate-500" />}
                    progressValue={tauxVersements}
                />
                <StatCard
                    label="Dépensé total"
                    value={formatCurrency(projet.total_consomme)}
                    sub="Paiements effectués"
                    icon={<DocumentTextIcon className="w-5 h-5 text-blue-400" />}
                />
                <StatCard
                    label="Disponible en caisse"
                    value={formatCurrency(projet.disponible_caisse)}
                    sub="Fonds réellement dépensables"
                    icon={<BanknotesIcon className="w-5 h-5 text-emerald-400" />}
                    variant={projet.disponible_caisse < 0 ? 'danger' : 'success'}
                />
            </div>

            <div className="space-y-6">
                {/* Conventions — pleine largeur, grille sur PC */}
                <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <div className="px-5 py-3.5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 className="text-sm font-semibold text-gray-900 dark:text-white">
                            Conventions ({projet.conventions.length})
                        </h3>
                    </div>
                    <div className="p-5">
                        {projet.conventions.length === 0 ? (
                            <p className="text-sm text-gray-400 dark:text-slate-500 text-center py-6">Aucune convention</p>
                        ) : (
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                {projet.conventions.map((c) => (
                                    <Link
                                        key={c.id}
                                        href={projetsConventionsShow.url({ projet: projet.id, convention: c.id })}
                                        className="block group h-full"
                                    >
                                        <div className="h-full flex flex-col border border-gray-200 dark:border-slate-700 rounded-lg p-3 hover:border-blue-300 dark:hover:border-blue-700 hover:bg-blue-50/50 dark:hover:bg-blue-950/20 transition-all">
                                            <div className="flex items-start justify-between gap-2 mb-2">
                                                <p className="text-xs font-semibold text-gray-900 dark:text-white leading-tight">
                                                    {c.bailleur.sigle ?? c.bailleur.nom}
                                                </p>
                                                <span className={`shrink-0 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium ${conventionStatusClass(c.statut)}`}>
                                                    {c.libelle_statut}
                                                </span>
                                            </div>
                                            <p className="text-xs text-gray-500 dark:text-slate-400 mb-3 line-clamp-2 flex-1">{c.titre}</p>
                                            <div className="flex items-center justify-between text-xs mt-auto">
                                                <span className="font-mono font-medium text-gray-900 dark:text-white">{formatCurrency(c.montant_fcfa)}</span>
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
                    </div>
                </div>

                {/* Textes — pleine largeur empilés */}
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
        </AppLayout>
    );
}

function StatCard({ label, value, sub, icon, progressValue, variant }: {
    label: string;
    value: string;
    sub?: string;
    icon: React.ReactNode;
    progressValue?: number;
    variant?: 'success' | 'danger' | 'default';
}) {
    return (
        <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5">
            <div className="flex items-center gap-2 mb-2">
                {icon}
                <p className="text-xs text-gray-500 dark:text-slate-400">{label}</p>
            </div>
            <p className={`text-lg font-mono font-bold ${
                variant === 'danger' ? 'text-red-600' : 
                variant === 'success' ? 'text-emerald-600' : 
                'text-gray-900 dark:text-white'
            }`}>{value}</p>
            {sub && <p className="text-xs text-gray-400 dark:text-slate-500 mt-0.5">{sub}</p>}
            {progressValue !== undefined && (
                <div className="mt-2 w-full h-1.5 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div className="h-full bg-blue-500 dark:bg-blue-400 rounded-full" style={{ width: `${progressValue}%` }} />
                </div>
            )}
        </div>
    );
}

function StatusBanner({ statut, libelleStatut }: { statut: string; libelleStatut: string }) {
    const messages: Record<string, { icon: React.ReactNode; title: string; text: string; className: string }> = {
        en_attente_financement: {
            icon: <InformationCircleIcon className="w-4 h-4 shrink-0" />,
            title: 'Projet en attente de mise en cours',
            text: 'Le DAF doit mettre le projet en cours pour que vous puissiez soumettre des demandes de dépenses. Les conventions peuvent être signées et des versements reçus, mais aucune demande ne peut être créée tant que le statut n\'est pas « En cours ».',
            className: 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-400',
        },
        termine: {
            icon: <LockClosedIcon className="w-4 h-4 shrink-0" />,
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
    if (!config) return null;

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
