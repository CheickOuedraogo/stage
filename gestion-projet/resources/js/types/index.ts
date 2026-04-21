export interface User {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'daf' | 'ac' | 'porteur';
    role_label: string;
    is_active: boolean;
    avatar_url: string | null;
    telephone: string | null;
    unread_notifications: number;
}

export interface PageProps {
    auth: {
        user: User | null;
    };
    flash: {
        success?: string;
        error?: string;
        warning?: string;
    };
    maintenance: {
        active: boolean;
        until: string | null;
        reason: string | null;
    };
    [key: string]: unknown;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}
