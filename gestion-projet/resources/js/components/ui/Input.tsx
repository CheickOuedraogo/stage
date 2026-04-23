import { cn } from '@/lib/utils';
import { type InputHTMLAttributes, forwardRef } from 'react';

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
    label?: string;
    error?: string;
    hint?: string;
}

export const Input = forwardRef<HTMLInputElement, InputProps>(
    ({ className, label, error, hint, id, ...props }, ref) => {
        const inputId = id ?? label?.toLowerCase().replace(/\s+/g, '-');

        return (
            <div className="space-y-1.5">
                {label && (
                    <label htmlFor={inputId} className="block text-sm font-medium text-gray-700 dark:text-slate-300">
                        {label}
                        {props.required && (
                            <span className="ml-1 text-red-500" aria-hidden="true">*</span>
                        )}
                    </label>
                )}
                <input
                    ref={ref}
                    id={inputId}
                    className={cn(
                        'w-full px-3.5 py-2.5 text-sm rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-slate-500',
                        'focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 dark:focus:ring-blue-400/50 dark:focus:border-blue-400',
                        'transition-colors duration-150',
                        'disabled:bg-gray-50 dark:disabled:bg-slate-800 disabled:text-gray-500 dark:disabled:text-slate-500 disabled:cursor-not-allowed',
                        error
                            ? 'border border-red-400 dark:border-red-500 text-red-900 dark:text-red-300'
                            : 'border border-gray-300 dark:border-slate-600',
                        className,
                    )}
                    aria-invalid={!!error}
                    aria-describedby={error ? `${inputId}-error` : hint ? `${inputId}-hint` : undefined}
                    {...props}
                />
                {error && (
                    <p id={`${inputId}-error`} className="text-xs text-red-600 dark:text-red-400">{error}</p>
                )}
                {hint && !error && (
                    <p id={`${inputId}-hint`} className="text-xs text-gray-500 dark:text-slate-400">{hint}</p>
                )}
            </div>
        );
    },
);

Input.displayName = 'Input';
