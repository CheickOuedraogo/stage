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
            'prose-headings:font-semibold prose-headings:text-gray-900',
            'prose-h2:text-base prose-h3:text-sm',
            'prose-p:text-gray-700 prose-p:leading-relaxed',
            'prose-strong:text-gray-900 prose-strong:font-semibold',
            'prose-ul:text-gray-700 prose-ol:text-gray-700',
            'prose-li:marker:text-gray-400',
            'prose-a:text-blue-600 prose-a:no-underline hover:prose-a:underline',
            className,
        ].join(' ')}>
            <ReactMarkdown remarkPlugins={[remarkGfm]}>
                {content}
            </ReactMarkdown>
        </div>
    );
}
