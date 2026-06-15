import { useCallback, useState } from 'react';

const STORAGE_KEY = 'cifeu-theme';

function getInitialDark(): boolean {
    if (typeof window === 'undefined') {
return false;
}

    return document.documentElement.classList.contains('dark');
}

export function useDarkMode() {
    const [isDark, setIsDark] = useState<boolean>(getInitialDark);

    const toggle = useCallback(() => {
        const next = !isDark;

        if (next) {
            document.documentElement.classList.add('dark');
            localStorage.setItem(STORAGE_KEY, 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem(STORAGE_KEY, 'light');
        }

        setIsDark(next);
    }, [isDark]);

    return { isDark, toggle };
}
