import { clsx  } from 'clsx';
import type {ClassValue} from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]): string {
    return twMerge(clsx(inputs));
}

/**
 * Format a number as FCFA currency.
 * e.g. formatCurrency(1250000) → "1 250 000 FCFA"
 */
export function formatCurrency(amount: number): string {
    return (
        new Intl.NumberFormat('fr-FR', {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount) + ' FCFA'
    );
}

/**
 * Format a date string to French locale.
 * e.g. formatDate('2026-04-21') → "21 avril 2026"
 */
export function formatDate(date: string | Date): string {
    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(date));
}

/**
 * Format a datetime string to French locale.
 */
export function formatDateTime(date: string | Date): string {
    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date));
}

/**
 * Truncate a string with ellipsis.
 */
export function truncate(str: string, length: number): string {
    return str.length > length ? str.slice(0, length) + '…' : str;
}

/**
 * Get initials from a name (e.g. "Jean-Baptiste Ouédraogo" → "JO")
 */
export function getInitials(name: string): string {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((n) => n[0])
        .join('')
        .toUpperCase();
}

export function clampPercent(part: number, total: number): number {
    if (total === 0) {
return 0;
}

    return Math.min(100, Math.round((part / total) * 100));
}

const versementTypeStyles: Record<string, string> = {
    avance: 'bg-blue-100 text-blue-700',
    solde: 'bg-emerald-100 text-emerald-700',
};

export function versementTypeClass(type: string): string {
    return versementTypeStyles[type] ?? 'bg-gray-100 text-gray-600';
}

const projectStatusStyles: Record<string, string> = {
    en_cours: 'bg-emerald-100 text-emerald-700',
    en_attente_financement: 'bg-amber-100 text-amber-700',
    suspendu: 'bg-orange-100 text-orange-700',
    termine: 'bg-gray-100 text-gray-600',
    annule: 'bg-red-100 text-red-700',
};

export function projectStatusClass(status: string): string {
    return projectStatusStyles[status] ?? 'bg-gray-100 text-gray-600';
}

const conventionStatusStyles: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-700',
    suspendue: 'bg-orange-100 text-orange-700',
    terminee: 'bg-gray-100 text-gray-600',
    annulee: 'bg-red-100 text-red-700',
};

export function conventionStatusClass(status: string): string {
    return conventionStatusStyles[status] ?? 'bg-gray-100 text-gray-600';
}

const demandeStatusStyles: Record<string, string> = {
    soumise: 'bg-blue-100 text-blue-700',
    validee_daf: 'bg-amber-100 text-amber-700',
    rejetee_daf: 'bg-red-100 text-red-700',
    validee_ac: 'bg-emerald-100 text-emerald-700',
    rejetee_ac: 'bg-red-100 text-red-700',
    payee: 'bg-green-100 text-green-800',
    rapport_soumis: 'bg-purple-100 text-purple-700',
    terminee: 'bg-slate-100 text-slate-700',
};

export function demandeStatusClass(status: string): string {
    return demandeStatusStyles[status] ?? 'bg-gray-100 text-gray-600';
}
