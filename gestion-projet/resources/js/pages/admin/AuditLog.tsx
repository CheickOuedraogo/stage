import AppLayout from '@/components/layout/AppLayout';
import { Card, CardContent } from '@/components/ui/Card';
import { auditLog as adminAuditLog } from '@/routes/admin';
import { formatDateTime, getInitials } from '@/lib/utils';
import type { PaginatedData } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ChevronDownIcon, FunnelIcon, UserIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { useState as useLocalState } from 'react';

interface AuditEntry {
    id: number;
    audit_action: string;
    audit_description: string | null;
    audit_adresse_ip: string | null;
    audit_entite_type: string | null;
    audit_entite_id: number | null;
    audit_anciennes_valeurs: Record<string, unknown> | null;
    audit_nouvelles_valeurs: Record<string, unknown> | null;
    cree_le: string;
    utilisateur: {
        id_utilisateur: number;
        utilisateur_nom: string;
        utilisateur_email: string;
        role_key: string | null;
    } | null;
}

interface UtilisateurOption {
    id: number;
    utilisateur_nom: string;
    utilisateur_email: string;
    role_key: string | null;
    label_role: string | null;
}

interface AuditLogProps {
    logs: PaginatedData<AuditEntry>;
    users: UtilisateurOption[];
    selectedUtilisateurId: number | null;
}

const actionLabels: Record<string, string> = {
    login: 'Connexion',
    logout: 'Déconnexion',
    created: 'Création',
    updated: 'Modification',
    deleted: 'Suppression',
    maintenance_enabled: 'Maintenance activée',
    maintenance_disabled: 'Maintenance désactivée',
    password_changed: 'Mot de passe changé',
    avatar_updated: 'Avatar mis à jour',
    profile_updated: 'Profil mis à jour',
    user_activated: 'Compte activé',
    user_deactivated: 'Compte désactivé',
    user_status_change: 'Statut modifié',
};

const actionColors: Record<string, string> = {
    login: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    logout: 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-400',
    created: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    updated: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    deleted: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    maintenance_enabled: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    maintenance_disabled: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    password_changed: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    profile_updated: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    avatar_updated: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    user_activated: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    user_deactivated: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    user_status_change: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
};

const FIELD_LABELS: Record<string, string> = {
    name: 'Nom',
    email: 'E-mail',
    role_key: 'Rôle',
    utilisateur_actif: 'Actif',
    telephone: 'Téléphone',
    projet_statut: 'Statut',
    demande_statut: 'Statut',
    convention_statut: 'Statut',
    demande_montant: 'Montant',
    versement_montant: 'Montant',
    paiement_montant: 'Montant',
    rubrique_montant: 'Montant prévu',
    convention_montant_fcfa: 'Montant FCFA',
    projet_titre: 'Titre',
    convention_titre: 'Titre',
    demande_objet: 'Objet',
    demande_description: 'Description',
    rubrique_description: 'Description',
    demande_motif_rejet: 'Motif rejet',
    projet_date_fin_reelle: 'Date clôture',
    versement_date_reception: 'Date réception',
    faq_reponse: 'Réponse',
    faq_question: 'Question',
    rubrique_libelle: 'Libellé',
};

function formatValue(value: unknown): string {
    if (value === null || value === undefined) return '—';
    if (typeof value === 'boolean') return value ? 'Oui' : 'Non';
    if (typeof value === 'number') return new Intl.NumberFormat('fr-FR').format(value);
    return String(value);
}

function ValueDiff({ oldValues, newValues }: { oldValues: Record<string, unknown> | null; newValues: Record<string, unknown> | null; }) {
    const [expanded, setExpanded] = useLocalState(false);
    if (!oldValues && !newValues) return null;

    const allKeys = Array.from(new Set([
        ...Object.keys(oldValues ?? {}),
        ...Object.keys(newValues ?? {}),
    ])).filter((k) => !['id', 'created_at', 'updated_at', 'remember_token', 'password'].includes(k));

    if (allKeys.length === 0) return null;

    return (
        <div className="mt-1.5">
            <button
                onClick={() => setExpanded((v) => !v)}
                className="flex items-center gap-1 text-xs text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 transition-colors"
            >
                <ChevronDownIcon className={`w-3 h-3 transition-transform ${expanded ? 'rotate-180' : ''}`} />
                {expanded ? 'Masquer les détails' : `Voir les détails (${allKeys.length} champ${allKeys.length > 1 ? 's' : ''})`}
            </button>

            {expanded && (
                <div className="mt-2 rounded-lg border border-gray-100 dark:border-slate-800 overflow-hidden text-xs">
                    <table className="w-full">
                        <thead>
                            <tr className="bg-gray-50 dark:bg-slate-800">
                                <th className="text-left px-3 py-1.5 font-semibold text-gray-600 dark:text-slate-400 w-1/4">Champ</th>
                                {oldValues && <th className="text-left px-3 py-1.5 font-semibold text-red-600 dark:text-red-400 w-[37.5%]">Avant</th>}
                                {newValues && <th className="text-left px-3 py-1.5 font-semibold text-emerald-600 dark:text-emerald-400 w-[37.5%]">Après</th>}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                            {allKeys.map((key) => (
                                <tr key={key} className="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                                    <td className="px-3 py-1.5 font-medium text-gray-600 dark:text-slate-400">
                                        {FIELD_LABELS[key] ?? key}
                                    </td>
                                    {oldValues && (
                                        <td className={`px-3 py-1.5 font-mono ${oldValues[key] !== undefined ? 'text-red-700 dark:text-red-400 bg-red-50/50 dark:bg-red-900/10' : 'text-gray-400 dark:text-slate-600'}`}>
                                            {oldValues[key] !== undefined ? formatValue(oldValues[key]) : '—'}
                                        </td>
                                    )}
                                    {newValues && (
                                        <td className={`px-3 py-1.5 font-mono ${newValues[key] !== undefined ? 'text-emerald-700 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-900/10' : 'text-gray-400 dark:text-slate-600'}`}>
                                            {newValues[key] !== undefined ? formatValue(newValues[key]) : '—'}
                                        </td>
                                    )}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}

export default function AuditLog({ logs, users, selectedUtilisateurId }: AuditLogProps) {
    const selectedUtilisateur = selectedUtilisateurId ? users.find((u) => u.id === selectedUtilisateurId) : null;

    const filterByUtilisateur = (userId: number | null) => {
        router.get(adminAuditLog.url(), userId ? { id_utilisateur: userId } : {}, { preserveState: false });
    };

    return (
        <AppLayout title="Journal d'audit">
            <Head title="Journal d'audit — CIFEU" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Journal d'audit</h2>
                <p className="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    Historique de toutes les actions effectuées sur la plateforme
                </p>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {/* Utilisateur filter sidebar */}
                <div className="lg:col-span-1">
                    <Card>
                        <CardContent>
                            <div className="flex items-center gap-2 mb-3">
                                <FunnelIcon className="w-4 h-4 text-gray-500 dark:text-slate-400" />
                                <p className="text-sm font-semibold text-gray-700 dark:text-slate-300">Filtrer par utilisateur</p>
                            </div>

                            <div className="space-y-1">
                                <button
                                    onClick={() => filterByUtilisateur(null)}
                                    className={`w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors text-left ${
                                        !selectedUtilisateurId
                                            ? 'bg-slate-800 dark:bg-slate-700 text-white'
                                            : 'text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800'
                                    }`}
                                >
                                    <UserIcon className="w-4 h-4 shrink-0" />
                                    <span>Tous les utilisateurs</span>
                                </button>

                                {users.map((user) => (
                                    <button
                                        key={user.id}
                                        onClick={() => filterByUtilisateur(user.id)}
                                        className={`w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors text-left ${
                                            selectedUtilisateurId === user.id
                                                ? 'bg-slate-800 dark:bg-slate-700 text-white'
                                                : 'text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800'
                                        }`}
                                    >
                                        <div className={`w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 ${
                                            selectedUtilisateurId === user.id
                                                ? 'bg-white/20 text-white'
                                                : 'bg-gray-200 dark:bg-slate-700 text-gray-600 dark:text-slate-300'
                                        }`}>
                                            {getInitials(user.utilisateur_nom)}
                                        </div>
                                        <span className="truncate">{user.utilisateur_nom.split(' ')[0]}</span>
                                    </button>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Logs list */}
                <div className="lg:col-span-3 space-y-4">
                    {/* Filter indicator */}
                    {selectedUtilisateur && (
                        <div className="flex items-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-slate-800 rounded-lg">
                            <div className="w-6 h-6 rounded-full bg-slate-700 dark:bg-slate-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                                {getInitials(selectedUtilisateur.utilisateur_nom)}
                            </div>
                            <span className="text-sm text-gray-700 dark:text-slate-300 font-medium flex-1">
                                Actions de {selectedUtilisateur.utilisateur_nom}
                            </span>
                            <button
                                onClick={() => filterByUtilisateur(null)}
                                className="p-1 rounded hover:bg-gray-200 dark:hover:bg-slate-700 transition-colors"
                                aria-label="Effacer le filtre"
                            >
                                <XMarkIcon className="w-4 h-4 text-gray-500 dark:text-slate-400" />
                            </button>
                        </div>
                    )}

                    {logs.data.length === 0 ? (
                        <Card>
                            <CardContent>
                                <div className="flex flex-col items-center justify-center py-12 text-center">
                                    <div className="w-12 h-12 mb-4 text-gray-300 dark:text-slate-600">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                        </svg>
                                    </div>
                                    <p className="text-gray-500 dark:text-slate-400 text-sm">Aucune action enregistrée</p>
                                </div>
                            </CardContent>
                        </Card>
                    ) : (
                        <Card>
                            <CardContent>
                                <div className="divide-y divide-gray-100 dark:divide-slate-800">
                                    {logs.data.map((log) => (
                                        <div key={log.id} className="py-3 first:pt-0 last:pb-0">
                                            <div className="flex items-start gap-3">
                                                {log.utilisateur ? (
                                                    <button
                                                        onClick={() => filterByUtilisateur(log.utilisateur!.id_utilisateur)}
                                                        className="w-8 h-8 rounded-full bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-slate-300 text-xs font-bold flex items-center justify-center shrink-0 hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors mt-0.5"
                                                        title={`Filtrer par ${log.utilisateur.utilisateur_nom}`}
                                                    >
                                                        {getInitials(log.utilisateur.utilisateur_nom)}
                                                    </button>
                                                ) : (
                                                    <div className="w-8 h-8 rounded-full bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500 flex items-center justify-center shrink-0 mt-0.5">
                                                        <UserIcon className="w-4 h-4" />
                                                    </div>
                                                )}

                                                <div className="flex-1 min-w-0">
                                                    <div className="flex flex-wrap items-center gap-2 mb-0.5">
                                                        <span className="text-sm font-medium text-gray-900 dark:text-white">
                                                            {log.utilisateur?.utilisateur_nom ?? 'Système'}
                                                        </span>
                                                        <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
                                                            actionColors[log.audit_action] ?? 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-400'
                                                        }`}>
                                                            {actionLabels[log.audit_action] ?? log.audit_action}
                                                        </span>
                                                        {log.utilisateur?.role_key && (
                                                            <span className="text-xs text-gray-400 dark:text-slate-500 bg-gray-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">
                                                                {log.utilisateur.role_key}
                                                            </span>
                                                        )}
                                                    </div>
                                                    {log.audit_description && (
                                                        <p className="text-xs text-gray-600 dark:text-slate-300">{log.audit_description}</p>
                                                    )}
                                                    <ValueDiff oldValues={log.audit_anciennes_valeurs} newValues={log.audit_nouvelles_valeurs} />
                                                    <div className="flex items-center gap-3 mt-1">
                                                        <span className="text-xs text-gray-400 dark:text-slate-500">{formatDateTime(log.cree_le)}</span>
                                                        {log.audit_adresse_ip && (
                                                            <span className="text-xs text-gray-300 dark:text-slate-600 font-mono">{log.audit_adresse_ip}</span>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Pagination */}
                    {logs.last_page > 1 && (
                        <div className="flex items-center justify-between">
                            <p className="text-xs text-gray-500 dark:text-slate-400">
                                Entrées {logs.from}–{logs.to} sur {logs.total}
                            </p>
                            <div className="flex gap-1">
                                {logs.links.map((link, i) => (
                                    <Link
                                        key={i}
                                        href={link.url ?? '#'}
                                        preserveScroll
                                        className={`px-3 py-1.5 text-xs rounded-lg border transition-colors ${
                                            link.active
                                                ? 'bg-slate-800 dark:bg-slate-700 text-white border-slate-800 dark:border-slate-700'
                                                : link.url
                                                ? 'bg-white dark:bg-slate-900 text-gray-700 dark:text-slate-300 border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800'
                                                : 'bg-white dark:bg-slate-900 text-gray-300 dark:text-slate-600 border-gray-100 dark:border-slate-800 cursor-not-allowed pointer-events-none'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
