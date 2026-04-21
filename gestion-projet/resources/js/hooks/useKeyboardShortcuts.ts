import { useEffect } from 'react';

type ShortcutMap = Record<string, (e: KeyboardEvent) => void>;

/**
 * Register keyboard shortcuts.
 * Keys are normalized: e.g. 'ctrl+k', 'escape', 'ctrl+shift+u'
 */
export function useKeyboardShortcuts(shortcuts: ShortcutMap): void {
    useEffect(() => {
        const handler = (e: KeyboardEvent) => {
            const parts: string[] = [];

            if (e.ctrlKey || e.metaKey) parts.push('ctrl');
            if (e.shiftKey) parts.push('shift');
            if (e.altKey) parts.push('alt');
            parts.push(e.key.toLowerCase());

            const key = parts.join('+');
            const shortcutFn = shortcuts[key];

            if (shortcutFn) {
                // Don't trigger if user is typing in an input/textarea
                const target = e.target as HTMLElement;
                const isInputField = ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName);
                if (isInputField && key !== 'escape') return;

                e.preventDefault();
                shortcutFn(e);
            }
        };

        window.addEventListener('keydown', handler);

        return () => window.removeEventListener('keydown', handler);
    }, [shortcuts]);
}
