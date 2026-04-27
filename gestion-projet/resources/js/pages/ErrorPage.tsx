import { Link } from '@inertiajs/react';
import { HomeIcon, ArrowLeftIcon } from '@heroicons/react/24/outline';

interface ErrorPageProps {
    status: number;
}

const ERRORS: Record<number, { title: string; description: string }> = {
    403: {
        title: 'Accès refusé',
        description: "Vous n'avez pas les permissions nécessaires pour accéder à cette page.",
    },
    404: {
        title: 'Page introuvable',
        description: "La page que vous cherchez n'existe pas ou a été déplacée.",
    },
    419: {
        title: 'Session expirée',
        description: 'Votre session a expiré. Veuillez recharger la page et réessayer.',
    },
    500: {
        title: 'Erreur serveur',
        description: 'Une erreur inattendue s\'est produite. Notre équipe a été notifiée.',
    },
    503: {
        title: 'En maintenance',
        description: 'Le système est temporairement indisponible. Veuillez réessayer dans quelques instants.',
    },
};

export default function ErrorPage({ status }: ErrorPageProps) {
    const error = ERRORS[status] ?? {
        title: 'Erreur inattendue',
        description: 'Une erreur est survenue.',
    };

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-slate-950 flex items-center justify-center p-6">
            <div className="text-center max-w-md">
                <p className="text-8xl font-bold font-mono text-blue-600 dark:text-blue-500 select-none mb-6">
                    {status}
                </p>
                <h1 className="text-2xl font-bold text-slate-900 dark:text-white mb-3">
                    {error.title}
                </h1>
                <p className="text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">
                    {error.description}
                </p>
                <div className="flex items-center justify-center gap-3 flex-wrap">
                    <button
                        onClick={() => window.history.back()}
                        className="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl border border-gray-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                    >
                        <ArrowLeftIcon className="w-4 h-4" />
                        Retour
                    </button>
                    <Link
                        href="/"
                        className="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition-colors"
                    >
                        <HomeIcon className="w-4 h-4" />
                        Accueil
                    </Link>
                </div>
            </div>
        </div>
    );
}
