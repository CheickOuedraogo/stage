import { login } from '@/routes';
import type { PageProps } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEvent, useEffect, useState } from 'react';

interface LoginProps {
    maintenanceActive: boolean;
    maintenanceReason: string | null;
    maintenanceUntil: string | null;
}

function useCountdown(until?: string | null) {
    const [remaining, setRemaining] = useState<number | null>(null);

    useEffect(() => {
        if (!until) return;
        const target = new Date(until).getTime();
        const update = () => {
            const diff = target - Date.now();
            setRemaining(diff > 0 ? diff : 0);
        };
        update();
        const id = setInterval(update, 1000);
        return () => clearInterval(id);
    }, [until]);

    return remaining;
}

function formatCountdown(ms: number) {
    const totalSec = Math.floor(ms / 1000);
    const h = Math.floor(totalSec / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;
    return { h, m, s };
}

function CountUnit({ value, label }: { value: number; label: string }) {
    return (
        <div className="flex flex-col items-center gap-1">
            <div className="w-14 h-14 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center">
                <span className="text-xl font-mono font-bold text-white tabular-nums">
                    {String(value).padStart(2, '0')}
                </span>
            </div>
            <span className="text-[10px] text-white/40 uppercase tracking-widest">{label}</span>
        </div>
    );
}

export default function Login({ maintenanceActive, maintenanceReason, maintenanceUntil }: LoginProps) {
    const { flash } = usePage<PageProps>().props;
    const [showPassword, setShowPassword] = useState(false);
    const [showAdminForm, setShowAdminForm] = useState(false);
    const isMaintenanceMode = maintenanceActive || flash.success === 'maintenance';

    const remaining = useCountdown(isMaintenanceMode ? maintenanceUntil : null);
    const { h, m, s } = remaining != null ? formatCountdown(remaining) : { h: 0, m: 0, s: 0 };

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(login.url());
    };

    if (isMaintenanceMode && !showAdminForm) {
        return (
            <>
                <Head title="Maintenance en cours" />
                <div className="min-h-screen bg-gray-950 flex flex-col items-center justify-center p-6 overflow-hidden relative">
                    {/* Animated background blocks */}
                    <div className="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
                        <div className="absolute w-16 h-16 border border-white/10 rounded-xl" style={{ top: '10%', left: '8%', animation: 'floatA 8s ease-in-out infinite' }} />
                        <div className="absolute w-10 h-10 bg-white/5 rounded-lg" style={{ top: '20%', right: '12%', animation: 'floatB 6s ease-in-out infinite 1s' }} />
                        <div className="absolute w-24 h-24 border border-white/5 rounded-2xl" style={{ bottom: '15%', left: '15%', animation: 'floatA 10s ease-in-out infinite 2s' }} />
                        <div className="absolute w-8 h-8 bg-white/5 rounded-md" style={{ bottom: '25%', right: '10%', animation: 'floatC 7s ease-in-out infinite 0.5s' }} />
                        <div className="absolute w-20 h-20 border border-white/5 rounded-xl" style={{ top: '45%', left: '5%', animation: 'floatB 9s ease-in-out infinite 3s' }} />
                        <div className="absolute w-12 h-12 rounded-lg" style={{ top: '35%', right: '6%', animation: 'floatC 11s ease-in-out infinite 1.5s', background: 'rgba(255,255,255,0.03)' }} />
                    </div>

                    <div className="relative z-10 flex flex-col items-center text-center max-w-sm w-full">
                        <div className="w-14 h-14 rounded-2xl border border-white/20 bg-white/5 flex items-center justify-center mb-8">
                            <svg className="w-6 h-6 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                            </svg>
                        </div>

                        <h1 className="text-2xl font-bold text-white mb-2 tracking-tight">En maintenance</h1>

                        {maintenanceReason && (
                            <p className="text-sm text-white/50 mb-8 leading-relaxed">{maintenanceReason}</p>
                        )}

                        {maintenanceUntil && remaining != null && remaining > 0 && (
                            <div className="mb-8">
                                <p className="text-xs text-white/30 uppercase tracking-widest mb-4">Reprise dans</p>
                                <div className="flex items-end gap-3">
                                    <CountUnit value={h} label="h" />
                                    <span className="text-white/30 text-xl font-mono mb-4">:</span>
                                    <CountUnit value={m} label="min" />
                                    <span className="text-white/30 text-xl font-mono mb-4">:</span>
                                    <CountUnit value={s} label="sec" />
                                </div>
                            </div>
                        )}

                        {maintenanceUntil && (remaining === null || remaining === 0) && (
                            <p className="text-xs text-white/30 mb-8">
                                Reprise prévue : {new Date(maintenanceUntil).toLocaleString('fr-FR')}
                            </p>
                        )}

                        <button
                            onClick={() => setShowAdminForm(true)}
                            className="text-xs text-white/20 hover:text-white/40 transition-colors mt-4"
                        >
                            Administration
                        </button>
                    </div>

                    <style>{`
                        @keyframes floatA {
                            0%, 100% { transform: translate(0, 0) rotate(0deg); }
                            33% { transform: translate(12px, -18px) rotate(8deg); }
                            66% { transform: translate(-8px, 10px) rotate(-5deg); }
                        }
                        @keyframes floatB {
                            0%, 100% { transform: translate(0, 0) rotate(0deg); }
                            40% { transform: translate(-14px, 16px) rotate(-10deg); }
                            70% { transform: translate(10px, -8px) rotate(6deg); }
                        }
                        @keyframes floatC {
                            0%, 100% { transform: translate(0, 0) rotate(0deg); }
                            30% { transform: translate(8px, 20px) rotate(12deg); }
                            60% { transform: translate(-12px, -10px) rotate(-8deg); }
                        }
                    `}</style>
                </div>
            </>
        );
    }

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

                    {/* Admin login back button when maintenance mode is on */}
                    {isMaintenanceMode && (
                        <button
                            onClick={() => setShowAdminForm(false)}
                            className="flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors mb-8 self-start"
                        >
                            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Retour
                        </button>
                    )}

                    <div className="mb-8">
                        <h1 className="text-2xl font-bold text-gray-900 tracking-tight">Connexion</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            {isMaintenanceMode ? 'Accès administrateur uniquement' : 'Accédez à votre espace CIFEU'}
                        </p>
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
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="vous@cifeu.bf"
                                autoComplete="email"
                                required
                                aria-required="true"
                                aria-invalid={!!errors.email}
                                aria-describedby={errors.email ? 'email-error' : undefined}
                                className="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-lg bg-white text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-gray-900 transition-colors"
                            />
                            {errors.email && (
                                <p id="email-error" className="text-xs text-red-600">{errors.email}</p>
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
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="••••••••"
                                    autoComplete="current-password"
                                    required
                                    aria-required="true"
                                    aria-invalid={!!errors.password}
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
                            {errors.password && (
                                <p className="text-xs text-red-600">{errors.password}</p>
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
