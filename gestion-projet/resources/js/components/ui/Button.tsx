import { cn } from '@/lib/utils';
import { type ButtonHTMLAttributes, forwardRef } from 'react';

interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: 'primary' | 'secondary' | 'danger' | 'ghost' | 'outline';
    size?: 'sm' | 'md' | 'lg';
    loading?: boolean;
}

const variants = {
    primary:
        'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500/50 disabled:bg-blue-400',
    secondary:
        'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 focus:ring-slate-500/50',
    danger:
        'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500/50 disabled:bg-red-400',
    ghost:
        'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 focus:ring-slate-500/50',
    outline:
        'border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 focus:ring-slate-500/50',
};

const sizes = {
    sm: 'px-3 py-1.5 text-xs gap-1.5',
    md: 'px-4 py-2.5 text-sm gap-2',
    lg: 'px-5 py-3 text-base gap-2',
};

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(
    ({ className, variant = 'primary', size = 'md', loading, disabled, children, ...props }, ref) => {
        return (
            <button
                ref={ref}
                disabled={loading || disabled}
                className={cn(
                    'inline-flex items-center justify-center font-medium rounded-lg',
                    'transition-all duration-200',
                    'motion-safe:hover:scale-[1.02] motion-safe:active:scale-[0.98]',
                    'focus:outline-none focus:ring-2',
                    'disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100',
                    variants[variant],
                    sizes[size],
                    className,
                )}
                {...props}
            >
                {loading && (
                    <svg
                        className="animate-spin -ml-1 h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                        <path
                            className="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                        />
                    </svg>
                )}
                {children}
            </button>
        );
    },
);

Button.displayName = 'Button';
