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
                    <label htmlFor={inputId} className="block text-sm font-medium text-gray-700">
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
                        'w-full px-3.5 py-2.5 text-sm rounded-lg bg-white text-gray-900 placeholder:text-gray-400',
                        'focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-gray-900',
                        'transition-colors duration-150',
                        'disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed',
                        error
                            ? 'border border-red-400 text-red-900'
                            : 'border border-gray-300',
                        className,
                    )}
                    aria-invalid={!!error}
                    aria-describedby={error ? `${inputId}-error` : hint ? `${inputId}-hint` : undefined}
                    {...props}
                />
                {error && (
                    <p id={`${inputId}-error`} className="text-xs text-red-600">{error}</p>
                )}
                {hint && !error && (
                    <p id={`${inputId}-hint`} className="text-xs text-gray-500">{hint}</p>
                )}
            </div>
        );
    },
);

Input.displayName = 'Input';
