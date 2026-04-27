interface SkeletonProps {
    className?: string;
}

export function Skeleton({ className = '' }: SkeletonProps) {
    return (
        <div
            className={`animate-pulse rounded bg-slate-200 dark:bg-slate-700 ${className}`}
            aria-hidden="true"
        />
    );
}

export function SkeletonRow({ cols = 4 }: { cols?: number }) {
    return (
        <tr aria-hidden="true">
            {Array.from({ length: cols }).map((_, i) => (
                <td key={i} className="px-5 py-4">
                    <Skeleton className="h-4 w-full" />
                </td>
            ))}
        </tr>
    );
}

export function SkeletonCard() {
    return (
        <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-5 space-y-3" aria-hidden="true">
            <Skeleton className="h-4 w-2/3" />
            <Skeleton className="h-3 w-full" />
            <Skeleton className="h-3 w-4/5" />
        </div>
    );
}

export function SkeletonStatCard() {
    return (
        <div className="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4" aria-hidden="true">
            <Skeleton className="w-9 h-9 rounded-lg mb-3" />
            <Skeleton className="h-7 w-12 mb-1" />
            <Skeleton className="h-3 w-24" />
        </div>
    );
}
