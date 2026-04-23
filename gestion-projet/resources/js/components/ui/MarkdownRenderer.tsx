import ReactMarkdown from 'react-markdown';
import remarkGfm from 'remark-gfm';

interface MarkdownRendererProps {
    content: string;
    className?: string;
}

export function MarkdownRenderer({ content, className = '' }: MarkdownRendererProps) {
    return (
        <div className={[
            'prose prose-sm max-w-none',
            'prose-headings:font-semibold prose-headings:text-gray-900 dark:prose-headings:text-white',
            'prose-h2:text-base prose-h3:text-sm',
            'prose-p:text-gray-700 dark:prose-p:text-slate-300 prose-p:leading-relaxed',
            'prose-strong:text-gray-900 dark:prose-strong:text-white prose-strong:font-semibold',
            'prose-ul:text-gray-700 dark:prose-ul:text-slate-300 prose-ol:text-gray-700 dark:prose-ol:text-slate-300',
            'prose-li:marker:text-gray-400 dark:prose-li:marker:text-slate-500',
            'prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-a:no-underline hover:prose-a:underline',
            'prose-code:text-gray-800 dark:prose-code:text-slate-200 prose-code:bg-gray-100 dark:prose-code:bg-slate-800',
            'prose-pre:bg-gray-900 dark:prose-pre:bg-slate-950',
            className,
        ].join(' ')}>
            <ReactMarkdown remarkPlugins={[remarkGfm]}>
                {content}
            </ReactMarkdown>
        </div>
    );
}
