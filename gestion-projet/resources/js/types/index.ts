export interface User {
    id: number;
    utilisateur_nom: string;
    utilisateur_email: string;
    utilisateur_role: 'admin' | 'daf' | 'ac' | 'porteur';
    label_role: string;
    utilisateur_actif: boolean;
    url_avatar: string | null;
    utilisateur_telephone: string | null;
    notifications_non_lues: number;
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

export interface Utilisateur {
    id?: number;
    id_utilisateur?: number;
    utilisateur_nom?: string;
    utilisateur_email?: string;
    utilisateur_role?: 'admin' | 'daf' | 'ac' | 'porteur';
    utilisateur_telephone?: string | null;
    utilisateur_actif?: boolean;
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
