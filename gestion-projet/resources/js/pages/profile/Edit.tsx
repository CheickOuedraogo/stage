import AppLayout from '@/components/layout/AppLayout';
import { password as profilePassword, update as profileUpdate } from '@/routes/profile';
import type { PageProps } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { CameraIcon, CheckIcon, KeyIcon, UserIcon } from '@heroicons/react/24/outline';
import { FormEvent, useRef, useState } from 'react';

function getInitials(name: string) {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((n) => n[0])
        .join('')
        .toUpperCase();
}

export default function ProfileEdit() {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user!;
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);

    const profileForm = useForm({
        name: user.name,
        telephone: user.telephone ?? '',
        avatar: null as File | null,
    });

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0] ?? null;
        profileForm.setData('avatar', file);
        if (file) {
            const url = URL.createObjectURL(file);
            setPreviewUrl(url);
        }
    };

    const submitProfile = (e: FormEvent) => {
        e.preventDefault();
        profileForm.patch(profileUpdate.url(), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => setPreviewUrl(null),
        });
    };

    const submitPassword = (e: FormEvent) => {
        e.preventDefault();
        passwordForm.patch(profilePassword.url(), {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        });
    };

    const avatarSrc = previewUrl ?? user.avatar_url;
    const roleLabels: Record<string, string> = {
        admin: 'Administrateur',
        daf: 'Direction Administration et Finances',
        ac: 'Agent Comptable',
        porteur: 'Porteur de projet',
    };

    return (
        <AppLayout title="Mon profil">
            <Head title="Mon profil — CIFEU" />

            {/* Page header */}
            <div className="mb-8 max-w-2xl mx-auto">
                <h2 className="text-2xl font-bold text-gray-900">Mon profil</h2>
                <p className="text-sm text-gray-500 mt-1">
                    Gérez vos informations personnelles et votre mot de passe
                </p>
            </div>

            <div className="max-w-2xl mx-auto space-y-6">
                {/* Identity card */}
                <div className="bg-white border border-gray-200 rounded-xl p-6">
                    <div className="flex items-center gap-6">
                        {/* Avatar */}
                        <div className="relative shrink-0">
                            <div className="w-20 h-20 rounded-full overflow-hidden ring-4 ring-gray-100">
                                {avatarSrc ? (
                                    <img
                                        src={avatarSrc}
                                        alt={`Avatar de ${user.name}`}
                                        className="w-full h-full object-cover"
                                    />
                                ) : (
                                    <div className="w-full h-full bg-gray-900 text-white text-xl font-bold flex items-center justify-center">
                                        {getInitials(user.name)}
                                    </div>
                                )}
                            </div>
                            <button
                                type="button"
                                onClick={() => fileInputRef.current?.click()}
                                className="absolute -bottom-1 -right-1 w-7 h-7 bg-gray-900 text-white rounded-full flex items-center justify-center shadow-md hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2"
                                aria-label="Changer la photo de profil"
                            >
                                <CameraIcon className="w-3.5 h-3.5" />
                            </button>
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                className="hidden"
                                tabIndex={-1}
                                aria-hidden="true"
                                onChange={handleFileChange}
                            />
                        </div>

                        <div>
                            <p className="text-lg font-semibold text-gray-900">{user.name}</p>
                            <p className="text-sm text-gray-500">{user.email}</p>
                            <p className="text-xs text-gray-400 mt-1">{roleLabels[user.role] ?? user.role_label}</p>
                            {previewUrl && (
                                <p className="text-xs text-blue-600 mt-1 font-medium">
                                    Nouvelle photo sélectionnée — enregistrez pour appliquer
                                </p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Personal info form */}
                <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div className="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                        <div className="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                            <UserIcon className="w-4 h-4 text-gray-600" />
                        </div>
                        <h3 className="text-sm font-semibold text-gray-900">Informations personnelles</h3>
                    </div>

                    <form onSubmit={submitProfile} className="p-6 space-y-5" noValidate>
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            {/* Name */}
                            <div className="space-y-1.5">
                                <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                    Nom complet <span className="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    value={profileForm.data.name}
                                    onChange={(e) => profileForm.setData('name', e.target.value)}
                                    required
                                    aria-invalid={!!profileForm.errors.name}
                                    className="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-colors"
                                />
                                {profileForm.errors.name && (
                                    <p className="text-xs text-red-600">{profileForm.errors.name}</p>
                                )}
                            </div>

                            {/* Phone */}
                            <div className="space-y-1.5">
                                <label htmlFor="telephone" className="block text-sm font-medium text-gray-700">
                                    Téléphone
                                </label>
                                <input
                                    id="telephone"
                                    type="tel"
                                    value={profileForm.data.telephone}
                                    onChange={(e) => profileForm.setData('telephone', e.target.value)}
                                    placeholder="+226 70 00 00 00"
                                    aria-invalid={!!profileForm.errors.telephone}
                                    className="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-colors"
                                />
                                {profileForm.errors.telephone && (
                                    <p className="text-xs text-red-600">{profileForm.errors.telephone}</p>
                                )}
                            </div>
                        </div>

                        {/* Email (read-only) */}
                        <div className="space-y-1.5">
                            <label className="block text-sm font-medium text-gray-700">
                                Adresse e-mail
                            </label>
                            <input
                                type="email"
                                value={user.email}
                                disabled
                                readOnly
                                aria-label="Adresse e-mail (non modifiable)"
                                className="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed"
                            />
                            <p className="text-xs text-gray-400">L'adresse e-mail ne peut pas être modifiée.</p>
                        </div>

                        <div className="flex justify-end pt-1">
                            <button
                                type="submit"
                                disabled={profileForm.processing}
                                className="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {profileForm.processing ? (
                                    <>
                                        <svg className="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                        </svg>
                                        Enregistrement…
                                    </>
                                ) : (
                                    <>
                                        <CheckIcon className="w-4 h-4" />
                                        Enregistrer
                                    </>
                                )}
                            </button>
                        </div>
                    </form>
                </div>

                {/* Password form */}
                <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div className="flex items-center gap-3 px-6 py-4 border-b border-gray-100">
                        <div className="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                            <KeyIcon className="w-4 h-4 text-gray-600" />
                        </div>
                        <h3 className="text-sm font-semibold text-gray-900">Changer le mot de passe</h3>
                    </div>

                    <form onSubmit={submitPassword} className="p-6 space-y-5" noValidate>
                        <div className="space-y-1.5">
                            <label htmlFor="current_password" className="block text-sm font-medium text-gray-700">
                                Mot de passe actuel <span className="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="current_password"
                                type="password"
                                value={passwordForm.data.current_password}
                                onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                                autoComplete="current-password"
                                required
                                aria-invalid={!!passwordForm.errors.current_password}
                                className="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-colors"
                            />
                            {passwordForm.errors.current_password && (
                                <p className="text-xs text-red-600">{passwordForm.errors.current_password}</p>
                            )}
                        </div>

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div className="space-y-1.5">
                                <label htmlFor="new_password" className="block text-sm font-medium text-gray-700">
                                    Nouveau mot de passe <span className="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input
                                    id="new_password"
                                    type="password"
                                    value={passwordForm.data.password}
                                    onChange={(e) => passwordForm.setData('password', e.target.value)}
                                    autoComplete="new-password"
                                    required
                                    aria-invalid={!!passwordForm.errors.password}
                                    placeholder="Min. 8 caractères"
                                    className="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-colors"
                                />
                                {passwordForm.errors.password && (
                                    <p className="text-xs text-red-600">{passwordForm.errors.password}</p>
                                )}
                            </div>

                            <div className="space-y-1.5">
                                <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                                    Confirmer <span className="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    value={passwordForm.data.password_confirmation}
                                    onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                                    autoComplete="new-password"
                                    required
                                    aria-invalid={!!passwordForm.errors.password_confirmation}
                                    className="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-colors"
                                />
                                {passwordForm.errors.password_confirmation && (
                                    <p className="text-xs text-red-600">{passwordForm.errors.password_confirmation}</p>
                                )}
                            </div>
                        </div>

                        <p className="text-xs text-gray-400">
                            Minimum 8 caractères avec majuscules, minuscules et chiffres.
                        </p>

                        <div className="flex justify-end pt-1">
                            <button
                                type="submit"
                                disabled={passwordForm.processing}
                                className="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {passwordForm.processing ? (
                                    <>
                                        <svg className="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                        </svg>
                                        Mise à jour…
                                    </>
                                ) : (
                                    <>
                                        <KeyIcon className="w-4 h-4" />
                                        Mettre à jour
                                    </>
                                )}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
