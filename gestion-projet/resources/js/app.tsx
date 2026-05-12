import { createInertiaApp, router } from '@inertiajs/react';
import React from 'react';
import { createRoot, type Root } from 'react-dom/client';
import ErrorPage from './pages/ErrorPage';

const appName = import.meta.env.VITE_APP_NAME || 'CIFEU';

let appRoot: Root | null = null;

// Branded error pages — reuse existing root to avoid React double-root warning
router.on('httpException', (event) => {
    const status = (event as CustomEvent<{ response: { status: number } }>).detail?.response?.status ?? 500;
    if (appRoot) {
        appRoot.render(React.createElement(ErrorPage, { status }));
    } else {
        const el = document.getElementById('app');
        if (el) createRoot(el).render(React.createElement(ErrorPage, { status }));
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
        appRoot = createRoot(el);
        appRoot.render(<App {...props} />);
    },
    progress: {
        color: '#3b82f6',
    },
});
