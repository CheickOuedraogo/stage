import { Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';

interface MaintenanceProps {
    reason?: string;
    until?: string;
}

function useCountdown(until?: string) {
    const [remaining, setRemaining] = useState<number | null>(null);

    useEffect(() => {
        if (!until) {
return;
}

        const target = new Date(until).getTime();
        if (Number.isNaN(target)) {
            setRemaining(null);
            return;
        }

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
    const d = Math.floor(totalSec / 86_400);
    const h = Math.floor((totalSec % 86_400) / 3600);
    const m = Math.floor((totalSec % 3600) / 60);
    const s = totalSec % 60;

    return { d, h, m, s };
}

function CountUnit({ value, label }: { value: number; label: string }) {
    return (
        <div className="flex flex-col items-center gap-1">
            <div className="w-16 h-16 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center">
                <span className="text-2xl font-mono font-bold text-white tabular-nums">
                    {String(value).padStart(2, '0')}
                </span>
            </div>
            <span className="text-xs text-white/50 uppercase tracking-widest">{label}</span>
        </div>
    );
}

export default function Maintenance({ reason, until }: MaintenanceProps) {
    const remaining = useCountdown(until);
    const { d, h, m, s } = remaining != null ? formatCountdown(remaining) : { d: 0, h: 0, m: 0, s: 0 };
    const finished = remaining === 0;

    return (
        <>
            <Head title="Maintenance en cours" />
            <div className="min-h-screen bg-gray-950 flex flex-col items-center justify-center p-6 overflow-hidden relative">
                {/* Animated background blocks */}
                <div className="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
                    <div
                        className="absolute w-16 h-16 border border-white/10 rounded-xl"
                        style={{ top: '10%', left: '8%', animation: 'floatA 8s ease-in-out infinite' }}
                    />
                    <div
                        className="absolute w-10 h-10 bg-white/5 rounded-lg"
                        style={{ top: '20%', right: '12%', animation: 'floatB 6s ease-in-out infinite 1s' }}
                    />
                    <div
                        className="absolute w-24 h-24 border border-white/5 rounded-2xl"
                        style={{ bottom: '15%', left: '15%', animation: 'floatA 10s ease-in-out infinite 2s' }}
                    />
                    <div
                        className="absolute w-8 h-8 bg-white/5 rounded-md"
                        style={{ bottom: '25%', right: '10%', animation: 'floatC 7s ease-in-out infinite 0.5s' }}
                    />
                    <div
                        className="absolute w-20 h-20 border border-white/5 rounded-xl"
                        style={{ top: '45%', left: '5%', animation: 'floatB 9s ease-in-out infinite 3s' }}
                    />
                    <div
                        className="absolute w-12 h-12 bg-white/3 rounded-lg"
                        style={{ top: '35%', right: '6%', animation: 'floatC 11s ease-in-out infinite 1.5s' }}
                    />
                    <div
                        className="absolute w-6 h-6 border border-white/10 rounded"
                        style={{ top: '60%', left: '25%', animation: 'floatA 5s ease-in-out infinite 2.5s' }}
                    />
                    <div
                        className="absolute w-14 h-14 border border-white/8 rounded-xl"
                        style={{ top: '15%', left: '45%', animation: 'floatB 12s ease-in-out infinite 4s' }}
                    />
                    <div
                        className="absolute w-9 h-9 bg-white/4 rounded-lg"
                        style={{ bottom: '10%', left: '45%', animation: 'floatC 8s ease-in-out infinite 1s' }}
                    />
                </div>

                {/* Content */}
                <div className="relative z-10 flex flex-col items-center text-center max-w-sm">
                    {/* Icon */}
                    <div className="w-16 h-16 rounded-2xl border border-white/20 bg-white/5 flex items-center justify-center mb-8">
                        <svg className="w-7 h-7 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                        </svg>
                    </div>

                    <h1 className="text-2xl font-bold text-white mb-2 tracking-tight">
                        {finished ? 'Maintenance terminée' : 'En maintenance'}
                    </h1>

                    {reason && (
                        <p className="text-sm text-white/50 mb-8 leading-relaxed">{reason}</p>
                    )}

                    {/* Countdown */}
                    {until && !finished && remaining != null && (
                        <div className="mb-8">
                            <p className="text-xs text-white/30 uppercase tracking-widest mb-4">Reprise dans</p>
                            <div className="flex items-end gap-3">
                                {d > 0 && (
                                    <>
                                        <CountUnit value={d} label="j" />
                                        <span className="text-white/30 text-xl font-mono mb-4">:</span>
                                    </>
                                )}
                                <CountUnit value={h} label="h" />
                                <span className="text-white/30 text-xl font-mono mb-4">:</span>
                                <CountUnit value={m} label="min" />
                                <span className="text-white/30 text-xl font-mono mb-4">:</span>
                                <CountUnit value={s} label="sec" />
                            </div>
                        </div>
                    )}

                    {until && !remaining && !finished && (
                        <p className="text-sm text-white/50 mb-8">
                            Reprise prévue : {new Date(until).toLocaleString('fr-FR')}
                        </p>
                    )}

                    {finished ? (
                        <a
                            href="/"
                            className="mt-4 px-4 py-2 bg-white text-gray-900 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            Accéder à l'application
                        </a>
                    ) : (
                        <a
                            href="/connexion"
                            className="mt-8 px-4 py-2 bg-white/5 border border-white/10 text-white/70 hover:text-white hover:bg-white/10 text-sm font-medium rounded-lg transition-colors"
                        >
                            Se connecter (Admin)
                        </a>
                    )}
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
