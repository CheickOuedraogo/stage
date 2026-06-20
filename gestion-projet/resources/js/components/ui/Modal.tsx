import { XMarkIcon } from '@heroicons/react/24/outline';
import { useEffect, useId, useRef } from 'react';

interface ModalProps {
    open: boolean;
    title: string;
    onClose: () => void;
    children: React.ReactNode;
}

export function Modal({ open, title, onClose, children }: ModalProps) {
    const id = useId();
    const modalRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!open) return;
        
        // Trap focus and handle escape key
        const handler = (e: KeyboardEvent) => { 
            if (e.key === 'Escape') onClose(); 
        };
        
        document.addEventListener('keydown', handler);
        return () => document.removeEventListener('keydown', handler);
    }, [open, onClose]);

    if (!open) return null;

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby={`${id}-title`}
        >
            <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} aria-hidden="true" />

            <div 
                ref={modalRef}
                className="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-slate-700 animate-in fade-in zoom-in-95 duration-150 flex flex-col max-h-full"
            >
                <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700/60 shrink-0">
                    <h2 id={`${id}-title`} className="text-lg font-semibold text-slate-900 dark:text-white">
                        {title}
                    </h2>
                    <button
                        onClick={onClose}
                        className="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                        aria-label="Fermer"
                    >
                        <XMarkIcon className="w-5 h-5" />
                    </button>
                </div>

                <div className="px-6 py-6 overflow-y-auto">
                    {children}
                </div>
            </div>
        </div>
    );
}
