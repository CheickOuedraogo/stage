import { cn } from '@/lib/utils';
import { type HTMLAttributes } from 'react';

interface CardProps extends HTMLAttributes<HTMLDivElement> {
    glass?: boolean;
    hover?: boolean;
}

export function Card({ className, glass = false, hover = false, children, ...props }: CardProps) {
    return (
        <div
            className={cn(
                'rounded-xl border p-6 transition-all duration-200',
                glass
                    ? 'backdrop-blur-sm bg-white/80 dark:bg-slate-800/80 border-white/20 dark:border-slate-700/50 shadow-lg shadow-black/5'
                    : 'bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-700 shadow-sm',
                hover && 'hover:shadow-md hover:border-blue-200 dark:hover:border-blue-700 cursor-pointer',
                className,
            )}
            {...props}
        >
            {children}
        </div>
    );
}

export function CardHeader({ className, children, ...props }: HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={cn('mb-4', className)} {...props}>
            {children}
        </div>
    );
}

export function CardTitle({ className, children, ...props }: HTMLAttributes<HTMLHeadingElement>) {
    return (
        <h3 className={cn('text-base font-semibold text-gray-900 dark:text-white', className)} {...props}>
            {children}
        </h3>
    );
}

export function CardContent({ className, children, ...props }: HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={cn(className)} {...props}>
            {children}
        </div>
    );
}
