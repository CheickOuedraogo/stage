import { cn } from '@/lib/utils';
import { type HTMLAttributes } from 'react';

type BadgeVariant =
    | 'default'
    | 'success'
    | 'warning'
    | 'danger'
    | 'info'
    | 'muted'
    | 'admin'
    | 'daf'
    | 'ac'
    | 'porteur';

interface BadgeProps extends HTMLAttributes<HTMLSpanElement> {
    variant?: BadgeVariant;
    dot?: boolean;
}

const variants: Record<BadgeVariant, string> = {
    default: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    success: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    warning: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    danger: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    info: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    muted: 'bg-slate-100 text-slate-700 dark:bg-slate-700/50 dark:text-slate-400',
    admin: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    daf: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
    ac: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
    porteur: 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
};

export function Badge({ className, variant = 'default', dot = false, children, ...props }: BadgeProps) {
    return (
        <span
            className={cn(
                'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium',
                variants[variant],
                className,
            )}
            {...props}
        >
            {dot && (
                <span className="w-1.5 h-1.5 rounded-full bg-current shrink-0" aria-hidden="true" />
            )}
            {children}
        </span>
    );
}

/** Map a user role to a badge variant */
export function roleToBadgeVariant(role: string): BadgeVariant {
    return (role as BadgeVariant) ?? 'default';
}
