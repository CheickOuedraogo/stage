import { ExclamationTriangleIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { useEffect, useRef } from 'react';

interface ConfirmModalProps {
    open: boolean;
    title: string;
    message: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'danger' | 'warning';
    onConfirm: () => void;
    onCancel: () => void;
}

export function ConfirmModal({
    open,
    title,
    message,
    confirmLabel = 'Confirmer',
    cancelLabel = 'Annuler',
    variant = 'danger',
    onConfirm,
    onCancel,
}: ConfirmModalProps) {
    const confirmRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (open) {
            confirmRef.current?.focus();
        }
    }, [open]);

    useEffect(() => {
        const handler = (e: KeyboardEvent) => {
            if (!open) return;
            if (e.key === 'Escape') onCancel();
        };
        document.addEventListener('keydown', handler);
        return () => document.removeEventListener('keydown', handler);
    }, [open, onCancel]);

    if (!open) return null;

    const confirmStyles = variant === 'danger'
        ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white'
        : 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500 text-white';

    const iconColor = variant === 'danger' ? 'text-red-600' : 'text-amber-600';
    const iconBg = variant === 'danger' ? 'bg-red-50 dark:bg-red-900/20' : 'bg-amber-50 dark:bg-amber-900/20';

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="confirm-title"
            aria-describedby="confirm-message"
        >
            {/* Backdrop */}
            <div
                className="absolute inset-0 bg-black/40 backdrop-blur-sm"
                onClick={onCancel}
                aria-hidden="true"
            />

            {/* Modal */}
            <div className="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-slate-700 p-6 animate-in fade-in zoom-in-95 duration-150">
                <button
                    onClick={onCancel}
                    className="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                    aria-label="Fermer"
                >
                    <XMarkIcon className="w-4 h-4" />
                </button>

                <div className="flex items-start gap-4">
                    <div className={`shrink-0 w-10 h-10 rounded-full flex items-center justify-center ${iconBg}`}>
                        <ExclamationTriangleIcon className={`w-5 h-5 ${iconColor}`} />
                    </div>
                    <div className="flex-1 min-w-0">
                        <h2 id="confirm-title" className="text-base font-semibold text-slate-900 dark:text-white">
                            {title}
                        </h2>
                        <p id="confirm-message" className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            {message}
                        </p>
                    </div>
                </div>

                <div className="flex gap-3 mt-6 justify-end">
                    <button
                        onClick={onCancel}
                        className="px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors"
                    >
                        {cancelLabel}
                    </button>
                    <button
                        ref={confirmRef}
                        onClick={onConfirm}
                        className={`px-4 py-2 text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-colors ${confirmStyles}`}
                    >
                        {confirmLabel}
                    </button>
                </div>
            </div>
        </div>
    );
}
