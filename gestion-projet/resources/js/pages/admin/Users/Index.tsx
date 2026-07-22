import { MagnifyingGlassIcon, PlusIcon, UserIcon } from '@heroicons/react/24/outline';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/components/layout/AppLayout';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { formatDate, getInitials } from '@/lib/utils';
import {
    create as usersCreate,
    edit as usersEdit,
    index as usersIndex,
    toggleActive as usersToggleActive,
} from '@/routes/admin/users';
import type { PageProps, PaginatedData, Utilisateur } from '@/types';

interface Role {
    value: string;
    label: string;
}

interface UtilisateursIndexProps {
    utilisateurs: PaginatedData<Utilisateur & { cree_le: string }>;
    filters: { role?: string; search?: string; active?: string };
    roles: Role[];
}

export default function UtilisateursIndex({ utilisateurs, filters, roles }: UtilisateursIndexProps) {
    const [search, setSearch] = useState(filters.search ?? '');

    const applyFilter = (params: Record<string, string | undefined>) => {
        router.get(usersIndex.url(), { ...filters, ...params }, { preserveState: true });
    };

    return (
        <AppLayout title="Gestion des utilisateurs">
            <Head title="Utilisateurs — CIFEU" />

            <div className="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Utilisateurs</h2>
                    <p className="text-sm text-gray-500 dark:text-slate-400 mt-0.5">
                        {utilisateurs.total} utilisateur{utilisateurs.total !== 1 ? 's' : ''} au total
                    </p>
                </div>
                <Link href={usersCreate.url()}>
                    <Button aria-label="Créer un nouvel utilisateur">
                        <PlusIcon className="w-4 h-4" />
                        Nouvel utilisateur
                    </Button>
                </Link>
            </div>

            {/* Filters */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 mb-6">
                <div className="flex flex-col sm:flex-row gap-3">
                    <div className="flex-1 relative">
                        <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-slate-500" aria-hidden="true" />
                        <input
                            type="search"
                            placeholder="Rechercher par nom ou email…"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && applyFilter({ search })}
                            aria-label="Rechercher un utilisateur"
                            className="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                        />
                    </div>
                    <select
                        value={filters.role ?? ''}
                        onChange={(e) => applyFilter({ role: e.target.value || undefined })}
                        aria-label="Filtrer par rôle"
                        className="px-3 py-2.5 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                    >
                        <option value="">Tous les rôles</option>
                        {roles.map((r) => (
                            <option key={r.value} value={r.value}>{r.label}</option>
                        ))}
                    </select>
                    <select
                        value={filters.active ?? ''}
                        onChange={(e) => applyFilter({ active: e.target.value || undefined })}
                        aria-label="Filtrer par statut"
                        className="px-3 py-2.5 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                    >
                        <option value="">Tous les statuts</option>
                        <option value="1">Actifs</option>
                        <option value="0">Désactivés</option>
                    </select>
                </div>
            </div>

            {/* Table */}
            <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full text-sm" aria-label="Liste des utilisateurs">
                        <thead>
                            <tr className="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Utilisateur</th>
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Rôle</th>
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Statut</th>
                                <th className="text-left px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Créé le</th>
                                <th className="text-right px-6 py-3.5 text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                            {utilisateurs.data.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="px-6 py-16 text-center text-gray-400 dark:text-slate-500">
                                        <UserIcon className="w-10 h-10 mx-auto mb-2 opacity-40" />
                                        <p>Aucun utilisateur trouvé</p>
                                    </td>
                                </tr>
                            ) : (
                                utilisateurs.data.map((utilisateur) => <UtilisateurRow key={utilisateur.id_utilisateur} utilisateur={utilisateur} />)
                            )}
                        </tbody>
                    </table>
                </div>

                {utilisateurs.last_page > 1 && (
                    <div className="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-slate-700">
                        <p className="text-xs text-gray-500 dark:text-slate-400">
                            {utilisateurs.from}–{utilisateurs.to} sur {utilisateurs.total}
                        </p>
                        <div className="flex gap-1.5">
                            {utilisateurs.links.map((link, i) => (
                                <Link
                                    key={i}
                                    href={link.url ?? '#'}
                                    preserveScroll
                                    className={[
                                        'px-3 py-1.5 rounded-lg text-xs font-medium transition-colors',
                                        link.active
                                            ? 'bg-slate-800 dark:bg-slate-700 text-white'
                                            : link.url
                                            ? 'bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800'
                                            : 'text-gray-300 dark:text-slate-600 cursor-default pointer-events-none',
                                    ].join(' ')}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

function UtilisateurRow({ utilisateur }: { utilisateur: Utilisateur & { cree_le: string } }) {
    const { auth } = usePage<PageProps>().props;
    const isSelf = auth.user?.id === utilisateur.id_utilisateur;

    const handleToggle = () => {
        if (isSelf) {
return;
}

        router.patch(usersToggleActive.url(utilisateur.id_utilisateur), {}, { preserveScroll: true });
    };

    return (
        <tr className="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
            <td className="px-6 py-4">
                <div className="flex items-center gap-3">
                    {utilisateur.url_avatar ? (
                        <img src={utilisateur.url_avatar} alt="" className="w-9 h-9 rounded-full object-cover" />
                    ) : (
                        <div className="w-9 h-9 rounded-full bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-slate-300 text-xs font-bold flex items-center justify-center shrink-0">
                            {getInitials(utilisateur.utilisateur_nom)}
                        </div>
                    )}
                    <div>
                        <p className="font-medium text-gray-900 dark:text-white">{utilisateur.utilisateur_nom}</p>
                        <p className="text-xs text-gray-500 dark:text-slate-400">{utilisateur.utilisateur_email}</p>
                    </div>
                </div>
            </td>
            <td className="px-6 py-4"><Badge variant={utilisateur.role_key as any}>{utilisateur.label_role}</Badge></td>
            <td className="px-6 py-4">
                <Badge variant={utilisateur.utilisateur_actif ? 'success' : 'muted'} dot>
                    {utilisateur.utilisateur_actif ? 'Actif' : 'Désactivé'}
                </Badge>
            </td>
            <td className="px-6 py-4 hidden md:table-cell text-gray-500 dark:text-slate-400 text-xs">
                {formatDate(utilisateur.cree_le)}
            </td>
            <td className="px-6 py-4">
                <div className="flex items-center justify-end gap-2">
                    <Link href={usersEdit.url(utilisateur.id_utilisateur)}>
                        <Button variant="ghost" size="sm" aria-label={`Modifier ${utilisateur.utilisateur_nom}`}>Modifier</Button>
                    </Link>
                    {!isSelf && (
                        <Button
                            variant={utilisateur.utilisateur_actif ? 'outline' : 'secondary'}
                            size="sm"
                            onClick={handleToggle}
                            aria-label={utilisateur.utilisateur_actif ? `Désactiver ${utilisateur.utilisateur_nom}` : `Activer ${utilisateur.utilisateur_nom}`}
                        >
                            {utilisateur.utilisateur_actif ? 'Désactiver' : 'Activer'}
                        </Button>
                    )}
                </div>
            </td>
        </tr>
    );
}
