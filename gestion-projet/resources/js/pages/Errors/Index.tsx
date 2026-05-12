import { Head, Link } from '@inertiajs/react';

interface ErrorProps {
    status: number;
}

interface ErrorInfo {
    title: string;
    description: string;
    action: string;
    color: string;
    reload?: boolean;
}

export default function ErrorPage({ status }: ErrorProps) {
    const infos: Record<number, ErrorInfo> = {
        503: {
            title: 'Service Indisponible',
            description: "L'application est actuellement en maintenance. Veuillez réessayer dans quelques instants.",
            action: 'Actualiser la page',
            color: 'bg-amber-50',
            reload: true,
        },
        500: {
            title: 'Erreur Serveur',
            description: "Une erreur interne est survenue. Notre équipe technique a été notifiée et travaille à la résolution.",
            action: "Retour à l'accueil",
            color: 'bg-red-50',
        },
        404: {
            title: 'Page Introuvable',
            description: "La page que vous recherchez n'existe pas ou a été déplacée.",
            action: "Retour à l'accueil",
            color: 'bg-blue-50',
        },
        403: {
            title: 'Accès Refusé',
            description: "Vous n'avez pas les permissions nécessaires pour accéder à cette ressource.",
            action: "Retour à l'accueil",
            color: 'bg-orange-50',
        },
        419: {
            title: 'Session Expirée',
            description: 'Votre session a expiré. Veuillez recharger la page pour continuer.',
            action: 'Recharger la page',
            color: 'bg-purple-50',
            reload: true,
        },
    };

    const info = infos[status] ?? {
        title: 'Erreur Inattendue',
        description: "Une erreur inattendue s'est produite.",
        action: "Retour à l'accueil",
        color: 'bg-gray-50',
    };

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-slate-950 flex flex-col justify-center items-center px-6 py-12 relative overflow-hidden">
            <Head title={`${status} — ${info.title}`} />

            <div className={`absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px] ${info.color} rounded-full blur-3xl opacity-60 mix-blend-multiply dark:opacity-10 pointer-events-none`} />
            <div className="absolute top-1/2 left-1/2 translate-x-16 -translate-y-32 w-[500px] h-[500px] bg-emerald-50 dark:bg-emerald-900/10 rounded-full blur-3xl opacity-40 mix-blend-multiply pointer-events-none" />

            <div className="relative z-10 w-full max-w-md text-center">
                <div className="text-9xl font-black text-gray-200 dark:text-slate-800 mb-6 tracking-tighter select-none leading-none">
                    {status}
                </div>

                <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-3 tracking-tight">
                    {info.title}
                </h1>

                <p className="text-base text-gray-500 dark:text-slate-400 mb-10 leading-relaxed">
                    {info.description}
                </p>

                <div className="flex items-center justify-center gap-3">
                    {info.reload ? (
                        <button
                            onClick={() => window.location.reload()}
                            className="inline-flex items-center justify-center px-6 py-2.5 text-sm font-medium rounded-xl text-white bg-gray-900 dark:bg-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 dark:focus:ring-white"
                        >
                            {info.action}
                        </button>
                    ) : (
                        <Link
                            href="/"
                            className="inline-flex items-center justify-center px-6 py-2.5 text-sm font-medium rounded-xl text-white bg-gray-900 dark:bg-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 dark:focus:ring-white"
                        >
                            {info.action}
                        </Link>
                    )}
                    <button
                        onClick={() => window.history.back()}
                        className="inline-flex items-center justify-center px-6 py-2.5 text-sm font-medium rounded-xl border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 dark:focus:ring-slate-700"
                    >
                        Page précédente
                    </button>
                </div>
            </div>
        </div>
    );
}
