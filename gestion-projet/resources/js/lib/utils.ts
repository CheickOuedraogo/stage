import { clsx, type ClassValue } from 'clsx';
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
