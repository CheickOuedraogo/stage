import AppLayout from '@/components/layout/AppLayout';
import { Button } from '@/components/ui/Button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { Input } from '@/components/ui/Input';
import { index as usersIndex, store as usersStore, update as usersUpdate } from '@/routes/admin/users';
import type { Utilisateur } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { FormEvent } from 'react';

interface Role {
    value: string;
    label: string;
}

interface UtilisateurFormProps {
    user?: Partial<Utilisateur> & { id?: number };
    roles: Role[];
}

export default function UtilisateurForm({ user, roles }: UtilisateurFormProps) {
    const isEditing = !!user?.id;

    const { data, setData, post, patch, processing, errors } = useForm({
        utilisateur_nom: user?.utilisateur_nom ?? '',
        utilisateur_email: user?.utilisateur_email ?? '',
        utilisateur_mot_de_passe: '',
        role_key: user?.role_key ?? 'porteur',
        utilisateur_telephone: user?.utilisateur_telephone ?? '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditing && user?.id != null) {
            patch(usersUpdate.url(user.id_utilisateur), { preserveScroll: true });
        } else {
            post(usersStore.url());
        }
    };

    return (
        <AppLayout title={isEditing ? 'Modifier l\'utilisateur' : 'Créer un utilisateur'}>
            <Head title={`${isEditing ? 'Modifier' : 'Créer'} un utilisateur — CIFEU`} />

            <div className="mb-6 flex items-center gap-3">
                <Link href={usersIndex.url()}>
                    <Button variant="ghost" size="sm" aria-label="Retour à la liste des utilisateurs">
                        <ArrowLeftIcon className="w-4 h-4" />
                        Retour
                    </Button>
                </Link>
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
                    {isEditing ? `Modifier ${user?.utilisateur_nom}` : 'Nouvel utilisateur'}
                </h2>
            </div>

            <div className="max-w-lg">
                <Card>
                    <CardHeader>
                        <CardTitle>Informations du compte</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-5" aria-label={isEditing ? 'Formulaire de modification' : 'Formulaire de création'} noValidate>
                            <Input
                                label="Nom complet"
                                type="text"
                                value={data.utilisateur_nom}
                                onChange={(e) => setData('utilisateur_nom', e.target.value)}
                                error={errors.utilisateur_nom}
                                required
                                placeholder="Ex : Pr. Jean-Baptiste OUÉDRAOGO"
                                autoComplete="name"
                            />

                            <Input
                                label="Adresse e-mail"
                                type="email"
                                value={data.utilisateur_email}
                                onChange={(e) => setData('utilisateur_email', e.target.value)}
                                error={errors.utilisateur_email}
                                required
                                placeholder="utilisateur@cifeu.bf"
                                autoComplete="email"
                            />

                            <div className="space-y-1">
                                <label htmlFor="role_key" className="block text-sm font-medium text-gray-700 dark:text-slate-300">
                                    Rôle <span className="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <select
                                    id="role_key"
                                    value={data.role_key}
                                    onChange={(e) => setData('role_key', e.target.value as Utilisateur['role_key'])}
                                    aria-label="Sélectionner le rôle"
                                    aria-invalid={!!errors.role_key}
                                    className="w-full px-3.5 py-2.5 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                >
                                    {roles.map((r) => (
                                        <option key={r.value} value={r.value}>{r.label}</option>
                                    ))}
                                </select>
                                {errors.role_key && <p className="text-xs text-red-600">{errors.role_key}</p>}
                            </div>

                            <Input
                                label="Téléphone"
                                type="tel"
                                value={data.utilisateur_telephone}
                                onChange={(e) => setData('utilisateur_telephone', e.target.value)}
                                error={errors.utilisateur_telephone}
                                placeholder="+226 70 00 00 00"
                                autoComplete="tel"
                            />

                            <Input
                                label={isEditing ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe'}
                                type="password"
                                value={data.utilisateur_mot_de_passe}
                                onChange={(e) => setData('utilisateur_mot_de_passe', e.target.value)}
                                error={errors.utilisateur_mot_de_passe}
                                required={!isEditing}
                                placeholder="••••••••"
                                hint={isEditing ? 'Laissez vide pour conserver le mot de passe actuel.' : undefined}
                                autoComplete={isEditing ? 'new-password' : 'new-password'}
                            />

                            <div className="flex gap-3 pt-2">
                                <Button type="submit" loading={processing}>
                                    {isEditing ? 'Enregistrer les modifications' : 'Créer le compte'}
                                </Button>
                                <Link href={usersIndex.url()}>
                                    <Button variant="secondary" type="button">
                                        Annuler
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
