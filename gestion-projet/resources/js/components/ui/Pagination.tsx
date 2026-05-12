import { Link } from '@inertiajs/react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationProps {
    currentPage: number;
    lastPage: number;
    total: number;
    links: PaginationLink[];
    perPage?: number;
}

export function Pagination({ currentPage, lastPage, total, links, perPage }: PaginationProps) {
    if (lastPage <= 1) return null;

    const from = perPage ? (currentPage - 1) * perPage + 1 : null;
    const to = perPage ? Math.min(currentPage * perPage, total) : null;

    return (
        <div className="px-5 py-3.5 border-t border-gray-100 dark:border-slate-800 flex items-center justify-between gap-4 text-sm flex-wrap">
            <p className="text-slate-500 text-xs shrink-0">
                {from && to
                    ? `${from}–${to} sur ${total} résultat${total !== 1 ? 's' : ''}`
                    : `Page ${currentPage} / ${lastPage}`}
            </p>
            <div className="flex gap-1 flex-wrap">
                {links.map((link, i) => (
                    link.url ? (
                        <Link
                            key={i}
                            href={link.url}
                            className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-colors ${
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : 'text-slate-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800'
                            }`}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ) : (
                        <span
                            key={i}
                            className="px-3 py-1.5 text-xs text-slate-300 dark:text-slate-600"
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    )
                ))}
            </div>
        </div>
    );
}
