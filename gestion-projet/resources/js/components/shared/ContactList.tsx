import { Link } from '@inertiajs/react';
import { ChatBubbleLeftRightIcon } from '@heroicons/react/24/outline';

export interface Contact {
    user_id: number;
    utilisateur_nom: string;
    utilisateur_email: string;
    role: string;
    role_key: string;
    unread: number;
    last_message: string | null;
    last_at: string | null;
    projets?: { id: number; titre: string }[];
}

interface Props {
    contacts: Contact[];
    getConversationUrl: (userId: number) => string;
    emptyMessage?: string;
}

const roleBadgeColors: Record<string, string> = {
    admin: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    daf: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    ac: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
    porteur: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
};

const roleLabels: Record<string, string> = {
    admin: 'Admin',
    daf: 'DAF',
    ac: 'AC',
    porteur: 'Porteur',
};

export function ContactList({ contacts, getConversationUrl, emptyMessage = 'Aucun contact' }: Props) {
    if (contacts.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-16 text-center bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl">
                <ChatBubbleLeftRightIcon className="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" />
                <p className="text-sm font-medium text-slate-600 dark:text-slate-300">{emptyMessage}</p>
            </div>
        );
    }

    return (
        <div className="space-y-2">
            {contacts.map((contact) => (
                <Link
                    key={contact.user_id}
                    href={getConversationUrl(contact.user_id)}
                    className="flex items-center gap-4 px-4 py-3 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors group"
                >
                    {/* Avatar */}
                    <div className="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center shrink-0">
                        <span className="text-sm font-bold text-blue-600 dark:text-blue-400">
                            {contact.utilisateur_nom.slice(0, 1)}
                        </span>
                    </div>

                    {/* Info */}
                    <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2 mb-0.5">
                            <p className="text-sm font-semibold text-slate-900 dark:text-white truncate">
                                {contact.utilisateur_nom}
                            </p>
                            <span className={`inline-block px-1.5 py-0.5 text-[10px] font-semibold rounded-full shrink-0 ${roleBadgeColors[contact.role_key] || 'bg-slate-100 text-slate-600'}`}>
                                {roleLabels[contact.role_key] || contact.role}
                            </span>
                        </div>
                        <p className="text-xs text-slate-400 truncate">
                            {contact.last_message || 'Aucun échange'}
                        </p>
                    </div>

                    {/* Meta */}
                    <div className="flex flex-col items-end gap-1 shrink-0">
                        {contact.last_at && (
                            <span className="text-[10px] text-slate-400">{contact.last_at}</span>
                        )}
                        {contact.unread > 0 && (
                            <span className="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-blue-500 rounded-full">
                                {contact.unread}
                            </span>
                        )}
                    </div>
                </Link>
            ))}
        </div>
    );
}
