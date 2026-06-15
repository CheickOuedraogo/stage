import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    ExclamationTriangleIcon,
    XMarkIcon,
} from '@heroicons/react/20/solid';
import { router, usePage } from '@inertiajs/react';
import React, {  useEffect, useState } from 'react';
import type {ReactNode} from 'react';
import { Navbar } from '@/components/layout/Navbar';
import type { PageProps } from '@/types';

interface AppLayoutProps {
    title: string;
    children: ReactNode;
}

type FlashType = 'success' | 'error' | 'warning';

interface FlashBannerProps {
    type: FlashType;
    message: string;
}

const FLASH_STYLES: Record<FlashType, string> = {
    success: 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300',
    error:   'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300',
    warning: 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300',
};

const FLASH_ICON_COMPONENTS = {
    success: CheckCircleIcon,
    error:   ExclamationCircleIcon,
    warning: ExclamationTriangleIcon,
} as const satisfies Record<FlashType, React.ComponentType<{ className?: string }>>;

function FlashBanner({ type, message }: FlashBannerProps) {
    const [visible, setVisible] = useState(true);
    const [fading, setFading] = useState(false);

    useEffect(() => {
        const fadeTimer = setTimeout(() => setFading(true), 3500);
        const hideTimer = setTimeout(() => setVisible(false), 4000);

        return () => {
 clearTimeout(fadeTimer); clearTimeout(hideTimer); 
};
    }, []);

    if (!visible) {
return null;
}

    const Icon = FLASH_ICON_COMPONENTS[type];

    return (
        <div
            className={`flex items-center gap-2 px-4 py-3 border rounded-lg text-sm transition-opacity duration-500 ${FLASH_STYLES[type]} ${fading ? 'opacity-0' : 'opacity-100'}`}
            role="alert"
        >
            <Icon className="w-4 h-4 shrink-0" aria-hidden="true" />
            <span className="flex-1">{message}</span>
            <button onClick={() => setVisible(false)} className="ml-2 opacity-60 hover:opacity-100 transition-opacity" aria-label="Fermer">
                <XMarkIcon className="w-4 h-4" />
            </button>
        </div>
    );
}

function NavigationProgress() {
    const [width, setWidth] = useState(0);

    useEffect(() => {
        let timer: ReturnType<typeof setTimeout>;

        const start = () => {
            setWidth(0);
            timer = setTimeout(() => setWidth(70), 50);
        };
        const finish = () => {
            setWidth(100);
            timer = setTimeout(() => setWidth(0), 300);
        };

        const offStart = router.on('start', start);
        const offFinish = router.on('finish', finish);

        return () => {
 offStart(); offFinish(); clearTimeout(timer); 
};
    }, []);

    if (width === 0) {
return null;
}

    return (
        <div
            className="fixed top-0 left-0 z-50 h-0.5 bg-blue-500 transition-all duration-300 ease-out"
            style={{ width: `${width}%`, opacity: width < 100 ? 1 : 0 }}
            aria-hidden="true"
        />
    );
}

export default function AppLayout({ title, children }: AppLayoutProps) {
    const { auth, flash } = usePage<PageProps>().props;
    const user = auth.user!;

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-slate-950 flex flex-col">
            <NavigationProgress />
            <Navbar user={user} title={title} />

            {/* Flash messages — auto-dismiss after 4s */}
            {(flash.success || flash.error || flash.warning) && (
                <div className="max-w-7xl mx-auto w-full px-4 lg:px-6 pt-4 space-y-2" aria-live="polite">
                    {flash.success && <FlashBanner type="success" message={flash.success} />}
                    {flash.error   && <FlashBanner type="error"   message={flash.error} />}
                    {flash.warning && <FlashBanner type="warning" message={flash.warning} />}
                </div>
            )}

            <main className="flex-1 max-w-7xl mx-auto w-full px-4 lg:px-6 py-6 overflow-x-hidden" id="main-content">
                {children}
            </main>
        </div>
    );
}
