import { createInertiaApp, router } from '@inertiajs/react';
import React from 'react';
import { createRoot } from 'react-dom/client';
import ErrorPage from './pages/ErrorPage';

const appName = import.meta.env.VITE_APP_NAME || 'CIFEU';

// Branded error pages for 403, 404, 419, 500, 503
router.on('httpException', (event) => {
    const status = (event as CustomEvent<{ response: { status: number } }>).detail?.response?.status ?? 500;
    const el = document.getElementById('app');
    if (el) {
        createRoot(el).render(React.createElement(ErrorPage, { status }));
    }
});

createInertiaApp({
    title: (title) => (title ? `${title} | ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.tsx', { eager: true }) as Record<
            string,
            { default: React.ComponentType }
        >;
        const page = pages[`./pages/${name}.tsx`];
        if (!page) throw new Error(`Page introuvable : ${name}`);
        return page;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#3b82f6',
    },
});
