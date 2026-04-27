import { Navbar } from '@/components/layout/Navbar';
import type { PageProps } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { type ReactNode, useEffect, useState } from 'react';
import { XMarkIcon } from '@heroicons/react/24/outline';

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

const FLASH_ICONS: Record<FlashType, string> = {
    success: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z',
    error:   'M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z',
    warning: 'M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z',
};

function FlashBanner({ type, message }: FlashBannerProps) {
    const [visible, setVisible] = useState(true);
    const [fading, setFading] = useState(false);

    useEffect(() => {
        const fadeTimer = setTimeout(() => setFading(true), 3500);
        const hideTimer = setTimeout(() => setVisible(false), 4000);
        return () => { clearTimeout(fadeTimer); clearTimeout(hideTimer); };
    }, []);

    if (!visible) return null;

    return (
        <div
            className={`flex items-center gap-2 px-4 py-3 border rounded-lg text-sm transition-opacity duration-500 ${FLASH_STYLES[type]} ${fading ? 'opacity-0' : 'opacity-100'}`}
            role="alert"
        >
            <svg className="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fillRule="evenodd" d={FLASH_ICONS[type]} clipRule="evenodd" />
            </svg>
            <span className="flex-1">{message}</span>
            <button onClick={() => setVisible(false)} className="ml-2 opacity-60 hover:opacity-100 transition-opacity" aria-label="Fermer">
                <XMarkIcon className="w-4 h-4" />
            </button>
        </div>
    );
}

function NavigationProgress() {
    const [loading, setLoading] = useState(false);
    const [width, setWidth] = useState(0);

    useEffect(() => {
        let timer: ReturnType<typeof setTimeout>;

        const start = () => {
            setWidth(0);
            setLoading(true);
            timer = setTimeout(() => setWidth(70), 50);
        };
        const finish = () => {
            setWidth(100);
            timer = setTimeout(() => { setLoading(false); setWidth(0); }, 300);
        };

        const offStart = router.on('start', start);
        const offFinish = router.on('finish', finish);

        return () => {
            offStart();
            offFinish();
            clearTimeout(timer);
        };
    }, []);

    if (!loading && width === 0) return null;

    return (
        <div
            className="fixed top-0 left-0 z-50 h-0.5 bg-blue-500 transition-all duration-300 ease-out"
            style={{ width: `${width}%`, opacity: loading ? 1 : 0 }}
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

            <main className="flex-1 max-w-7xl mx-auto w-full px-4 lg:px-6 py-6" id="main-content">
                {children}
            </main>
        </div>
    );
}
