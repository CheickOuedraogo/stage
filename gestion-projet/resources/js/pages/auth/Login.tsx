import { Head, useForm } from '@inertiajs/react';
import type { FormEvent} from 'react';
import { useState } from 'react';
import { login } from '@/routes';

export default function Login() {
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        utilisateur_email: '',
        utilisateur_mot_de_passe: '',
        remember: false,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(login.url());
    };

    return (
        <>
            <Head title="Connexion" />

            <div className="min-h-screen bg-white flex">
                {/* Left panel – form */}
                <div className="flex flex-col justify-center w-full max-w-md mx-auto px-8 py-12">
                    {/* Skip link */}
                    <a
                        href="#main-form"
                        className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:px-3 focus:py-1.5 focus:bg-black focus:text-white focus:rounded focus:z-50 focus:text-sm"
                    >
                        Aller au formulaire
                    </a>

                    <div className="mb-8">
                        <h1 className="text-2xl font-bold text-gray-900 tracking-tight">Connexion</h1>
                        <p className="text-sm text-gray-500 mt-1">Accédez à votre espace CIFEU</p>
                    </div>

                    <form
                        id="main-form"
                        onSubmit={submit}
                        className="space-y-5"
                        aria-label="Formulaire de connexion"
                        noValidate
                    >
                        {/* Email */}
                        <div className="space-y-1.5">
                            <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                                Adresse e-mail
                            </label>
                            <input
                                id="email"
                                type="email"
                                value={data.utilisateur_email}
                                onChange={(e) => setData('utilisateur_email', e.target.value)}
                                placeholder="vous@cifeu.bf"
                                autoComplete="email"
                                required
                                aria-required="true"
                                aria-invalid={!!errors.utilisateur_email}
                                aria-describedby={errors.utilisateur_email ? 'email-error' : undefined}
                                className="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-colors"
                            />
                            {errors.utilisateur_email && (
                                <p id="email-error" className="text-xs text-red-600">{errors.utilisateur_email}</p>
                            )}
                        </div>

                        {/* Password */}
                        <div className="space-y-1.5">
                            <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                                Mot de passe
                            </label>
                            <div className="relative">
                                <input
                                    id="password"
                                    type={showPassword ? 'text' : 'password'}
                                    value={data.utilisateur_mot_de_passe}
                                    onChange={(e) => setData('utilisateur_mot_de_passe', e.target.value)}
                                    placeholder="••••••••"
                                    autoComplete="current-password"
                                    required
                                    aria-required="true"
                                    aria-invalid={!!errors.utilisateur_mot_de_passe}
                                    className="w-full px-3.5 py-2.5 pr-10 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-colors"
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword((v) => !v)}
                                    aria-label={showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    {showPassword ? (
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
                                        </svg>
                                    ) : (
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    )}
                                </button>
                            </div>
                            {errors.utilisateur_mot_de_passe && (
                                <p className="text-xs text-red-600">{errors.utilisateur_mot_de_passe}</p>
                            )}
                        </div>

                        {/* Submit */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full py-2.5 px-4 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-all duration-150 motion-safe:hover:scale-[1.01] motion-safe:active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 flex items-center justify-center gap-2"
                        >
                            {processing && (
                                <svg className="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                            )}
                            {processing ? 'Connexion…' : 'Se connecter'}
                        </button>
                    </form>
                </div>

                {/* Right panel – UJKZ photo */}
                <div
                    className="hidden lg:block flex-1 relative overflow-hidden"
                    style={{
                        backgroundImage: 'url(/connection_bg.jpg)',
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                    }}
                    aria-hidden="true"
                />
            </div>
        </>
    );
}
