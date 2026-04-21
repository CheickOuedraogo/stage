import { getInitials } from '@/lib/utils';
import { logout } from '@/routes';
import { edit as profileEdit } from '@/routes/profile';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/react';
import {
    ArrowRightStartOnRectangleIcon,
    BellIcon,
    Bars3Icon,
    UserCircleIcon,
} from '@heroicons/react/24/outline';
import { useState } from 'react';

interface HeaderProps {
    user: User;
    title: string;
    onToggleSidebar?: () => void;
}

export function Header({ user, title, onToggleSidebar }: HeaderProps) {
    const [dropdownOpen, setDropdownOpen] = useState(false);

    const handleLogout = () => {
        router.post(logout.url());
    };

    return (
        <header className="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center px-4 gap-4 sticky top-0 z-30">
            {/* Hamburger */}
            <button
                onClick={onToggleSidebar}
                className="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors"
                aria-label="Afficher/masquer la navigation"
            >
                <Bars3Icon className="w-5 h-5" />
            </button>

            {/* Page title */}
            <h1 className="flex-1 text-base font-semibold text-slate-900 dark:text-white truncate">
                {title}
            </h1>

            {/* Notifications */}
            <Link
                href="#"
                className="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors"
                aria-label={`${user.unread_notifications} notification${user.unread_notifications !== 1 ? 's' : ''} non lue${user.unread_notifications !== 1 ? 's' : ''}`}
            >
                <BellIcon className="w-5 h-5" />
                {user.unread_notifications > 0 && (
                    <span
                        className="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                        aria-hidden="true"
                    >
                        {user.unread_notifications > 9 ? '9+' : user.unread_notifications}
                    </span>
                )}
            </Link>

            {/* User avatar + dropdown */}
            <div className="relative">
                <button
                    onClick={() => setDropdownOpen((v) => !v)}
                    className="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    aria-label="Menu utilisateur"
                    aria-expanded={dropdownOpen}
                >
                    {user.avatar_url ? (
                        <img
                            src={user.avatar_url}
                            alt={`Avatar de ${user.name}`}
                            className="w-8 h-8 rounded-full object-cover ring-2 ring-blue-500/30"
                        />
                    ) : (
                        <div
                            className="w-8 h-8 rounded-full bg-blue-600 text-white text-xs font-bold flex items-center justify-center ring-2 ring-blue-500/30"
                            aria-hidden="true"
                        >
                            {getInitials(user.name)}
                        </div>
                    )}
                    <span className="hidden sm:block text-sm font-medium text-slate-700 dark:text-slate-300 max-w-[120px] truncate">
                        {user.name}
                    </span>
                </button>

                {/* Dropdown */}
                {dropdownOpen && (
                    <>
                        {/* Backdrop */}
                        <div
                            className="fixed inset-0 z-10"
                            onClick={() => setDropdownOpen(false)}
                            aria-hidden="true"
                        />
                        <div
                            className="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-20 overflow-hidden"
                            role="menu"
                        >
                            <div className="px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                                <p className="text-sm font-medium text-slate-900 dark:text-white truncate">
                                    {user.name}
                                </p>
                                <p className="text-xs text-slate-500 dark:text-slate-400 truncate">
                                    {user.email}
                                </p>
                            </div>
                            <div className="py-1">
                                <Link
                                    href={profileEdit.url()}
                                    className="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                    role="menuitem"
                                    onClick={() => setDropdownOpen(false)}
                                >
                                    <UserCircleIcon className="w-4 h-4" />
                                    Mon profil
                                </Link>
                                <button
                                    onClick={handleLogout}
                                    className="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    role="menuitem"
                                >
                                    <ArrowRightStartOnRectangleIcon className="w-4 h-4" />
                                    Se déconnecter
                                </button>
                            </div>
                        </div>
                    </>
                )}
            </div>
        </header>
    );
}
