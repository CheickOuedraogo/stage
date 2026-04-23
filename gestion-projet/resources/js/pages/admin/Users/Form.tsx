import AppLayout from '@/components/layout/AppLayout';
import { Button } from '@/components/ui/Button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/Card';
import { Input } from '@/components/ui/Input';
import { index as usersIndex, store as usersStore, update as usersUpdate } from '@/routes/admin/users';
import type { User } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeftIcon } from '@heroicons/react/24/outline';
import { FormEvent } from 'react';

interface Role {
    value: string;
    label: string;
}

interface UserFormProps {
    user?: Partial<User> & { id?: number };
    roles: Role[];
}

export default function UserForm({ user, roles }: UserFormProps) {
    const isEditing = !!user?.id;

    const { data, setData, post, patch, processing, errors } = useForm({
        name: user?.name ?? '',
        email: user?.email ?? '',
        password: '',
        role: user?.role ?? 'porteur',
        telephone: user?.telephone ?? '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (isEditing && user?.id != null) {
            patch(usersUpdate.url(user.id), { preserveScroll: true });
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
                    {isEditing ? `Modifier ${user?.name}` : 'Nouvel utilisateur'}
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
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                error={errors.name}
                                required
                                placeholder="Ex : Pr. Jean-Baptiste OUÉDRAOGO"
                                autoComplete="name"
                            />

                            <Input
                                label="Adresse e-mail"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                error={errors.email}
                                required
                                placeholder="utilisateur@cifeu.bf"
                                autoComplete="email"
                            />

                            <div className="space-y-1">
                                <label htmlFor="role" className="block text-sm font-medium text-gray-700 dark:text-slate-300">
                                    Rôle <span className="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <select
                                    id="role"
                                    value={data.role}
                                    onChange={(e) => setData('role', e.target.value as User['role'])}
                                    aria-label="Sélectionner le rôle"
                                    aria-invalid={!!errors.role}
                                    className="w-full px-3.5 py-2.5 text-sm border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                                >
                                    {roles.map((r) => (
                                        <option key={r.value} value={r.value}>{r.label}</option>
                                    ))}
                                </select>
                                {errors.role && <p className="text-xs text-red-600">{errors.role}</p>}
                            </div>

                            <Input
                                label="Téléphone"
                                type="tel"
                                value={data.telephone}
                                onChange={(e) => setData('telephone', e.target.value)}
                                error={errors.telephone}
                                placeholder="+226 70 00 00 00"
                                autoComplete="tel"
                            />

                            <Input
                                label={isEditing ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe'}
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                error={errors.password}
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
