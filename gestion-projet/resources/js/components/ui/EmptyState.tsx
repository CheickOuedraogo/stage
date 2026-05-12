import type { ComponentType, SVGProps, ReactNode } from 'react';

interface EmptyStateProps {
    icon: ComponentType<SVGProps<SVGSVGElement>>;
    title: string;
    description?: string;
    action?: ReactNode;
}

export function EmptyState({ icon: Icon, title, description, action }: EmptyStateProps) {
    return (
        <div className="flex flex-col items-center justify-center py-14 px-6 text-center">
            <div className="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                <Icon className="w-7 h-7 text-slate-400 dark:text-slate-500" />
            </div>
            <p className="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">{title}</p>
            {description && (
                <p className="text-xs text-slate-500 dark:text-slate-400 max-w-xs">{description}</p>
            )}
            {action && <div className="mt-5">{action}</div>}
        </div>
    );
}
