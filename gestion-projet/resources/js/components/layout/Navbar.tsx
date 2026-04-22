import { cn, getInitials } from '@/lib/utils';
import { logout } from '@/routes';
import { auditLog as adminAuditLog, dashboard as adminDashboard } from '@/routes/admin';
import { index as usersIndex } from '@/routes/admin/users';
import { dashboard as acDashboard } from '@/routes/ac';
import { dashboard as dafDashboard } from '@/routes/daf';
import { index as dafProjetsIndex } from '@/routes/daf/projets';
import { dashboard as porteurDashboard } from '@/routes/porteur';
import { index as porteurProjetsIndex } from '@/routes/porteur/projets';
import { edit as profileEdit } from '@/routes/profile';
import type { User } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import {
    ArrowRightStartOnRectangleIcon,
    Bars3Icon,
    BellIcon,
    ChartBarIcon,
    ClipboardDocumentCheckIcon,
    ClipboardDocumentListIcon,
    CreditCardIcon,
    FolderIcon,
    HomeIcon,
    UserCircleIcon,
    UsersIcon,
    XMarkIcon,
} from '@heroicons/react/24/outline';
import { type ComponentType, type SVGProps, useState } from 'react';

interface NavItem {
    label: string;
    href: string;
    icon: ComponentType<SVGProps<SVGSVGElement>>;
    badge?: number;
}

function getNavItems(role: User['role'], notifs: number): NavItem[] {
    const notifBadge = notifs > 0 ? notifs : undefined;

    const navByRole: Record<User['role'], NavItem[]> = {
        admin: [
            { label: 'Tableau de bord', href: adminDashboard.url(), icon: HomeIcon },
            { label: 'Utilisateurs', href: usersIndex.url(), icon: UsersIcon },
            { label: 'Journal d\'audit', href: adminAuditLog.url(), icon: ClipboardDocumentCheckIcon },
        ],
        daf: [
            { label: 'Tableau de bord', href: dafDashboard.url(), icon: ChartBarIcon },
            { label: 'Projets', href: dafProjetsIndex.url(), icon: FolderIcon },
            { label: 'Demandes', href: '#', icon: ClipboardDocumentListIcon },
            { label: 'Versements', href: '#', icon: CreditCardIcon },
            { label: 'Notifications', href: '#', icon: BellIcon, badge: notifBadge },
        ],
        ac: [
            { label: 'Tableau de bord', href: acDashboard.url(), icon: HomeIcon },
            { label: 'Demandes', href: '#', icon: ClipboardDocumentListIcon },
            { label: 'Paiements', href: '#', icon: CreditCardIcon },
            { label: 'Notifications', href: '#', icon: BellIcon, badge: notifBadge },
        ],
        porteur: [
            { label: 'Mes projets', href: porteurProjetsIndex.url(), icon: FolderIcon },
            { label: 'Mes demandes', href: '#', icon: ClipboardDocumentListIcon },
            { label: 'Notifications', href: '#', icon: BellIcon, badge: notifBadge },
        ],
    };

    return navByRole[role] ?? [];
}

function isActive(href: string, currentUrl: string): boolean {
    if (href === '#') return false;
    return currentUrl === href || currentUrl.startsWith(href + '/') || currentUrl.startsWith(href + '?');
}

interface NavbarProps {
    user: User;
    title?: string;
}

export function Navbar({ user }: NavbarProps) {
    const { url } = usePage();
    const [mobileOpen, setMobileOpen] = useState(false);
    const [userMenuOpen, setUserMenuOpen] = useState(false);

    const items = getNavItems(user.role, user.unread_notifications);

    const handleLogout = () => {
        router.post(logout.url());
    };

    return (
        <>
            <header className="h-14 bg-white border-b border-gray-200 flex items-center px-4 lg:px-6 gap-4 sticky top-0 z-30">
                {/* Mobile hamburger */}
                <button
                    className="lg:hidden p-1.5 rounded-md hover:bg-gray-100 text-gray-600 transition-colors"
                    onClick={() => setMobileOpen((v) => !v)}
                    aria-label="Menu de navigation"
                    aria-expanded={mobileOpen}
                >
                    {mobileOpen ? <XMarkIcon className="w-5 h-5" /> : <Bars3Icon className="w-5 h-5" />}
                </button>

                {/* Brand */}
                <span className="text-sm font-semibold text-gray-900 tracking-tight shrink-0">CIFEU</span>

                {/* Desktop nav */}
                <nav className="hidden lg:flex items-center gap-1 flex-1 ml-4" aria-label="Navigation principale">
                    {items.map((item) => {
                        const active = isActive(item.href, url);
                        const Icon = item.icon;
                        return (
                            <Link
                                key={item.label}
                                href={item.href}
                                aria-current={active ? 'page' : undefined}
                                className={cn(
                                    'relative flex items-center gap-1.5 px-3 py-1.5 rounded-md text-sm font-medium transition-colors',
                                    active
                                        ? 'text-gray-900 bg-gray-100'
                                        : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50',
                                )}
                            >
                                <Icon className="w-4 h-4 shrink-0" />
                                {item.label}
                                {item.badge !== undefined && (
                                    <span className="ml-0.5 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold bg-red-500 text-white rounded-full">
                                        {item.badge > 9 ? '9+' : item.badge}
                                    </span>
                                )}
                                {active && (
                                    <span className="absolute bottom-0 left-3 right-3 h-0.5 bg-gray-900 rounded-full" aria-hidden="true" />
                                )}
                            </Link>
                        );
                    })}
                </nav>

                {/* Spacer for desktop (nav already fills it) */}
                <div className="flex-1 lg:hidden" />

                {/* Right: notifications + user */}
                <div className="flex items-center gap-2">
                    {/* Notifications */}
                    <Link
                        href="#"
                        className="relative p-2 rounded-md hover:bg-gray-100 text-gray-500 hover:text-gray-900 transition-colors"
                        aria-label={`${user.unread_notifications} notification${user.unread_notifications !== 1 ? 's' : ''}`}
                    >
                        <BellIcon className="w-4.5 h-4.5" style={{ width: '1.125rem', height: '1.125rem' }} />
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
                            onClick={() => setUserMenuOpen((v) => !v)}
                            className="flex items-center gap-2 p-1 rounded-md hover:bg-gray-100 transition-colors"
                            aria-label="Menu utilisateur"
                            aria-expanded={userMenuOpen}
                        >
                            {user.avatar_url ? (
                                <img
                                    src={user.avatar_url}
                                    alt={`Avatar de ${user.name}`}
                                    className="w-7 h-7 rounded-full object-cover ring-2 ring-gray-200"
                                />
                            ) : (
                                <div className="w-7 h-7 rounded-full bg-gray-900 text-white text-xs font-bold flex items-center justify-center">
                                    {getInitials(user.name)}
                                </div>
                            )}
                            <span className="hidden sm:block text-sm font-medium text-gray-700 max-w-[120px] truncate">
                                {user.name.split(' ')[0]}
                            </span>
                        </button>

                        {userMenuOpen && (
                            <>
                                <div
                                    className="fixed inset-0 z-10"
                                    onClick={() => setUserMenuOpen(false)}
                                    aria-hidden="true"
                                />
                                <div
                                    className="absolute right-0 top-full mt-1.5 w-56 bg-white rounded-xl shadow-lg border border-gray-200 z-20 overflow-hidden"
                                    role="menu"
                                >
                                    <div className="px-4 py-3 border-b border-gray-100">
                                        <p className="text-sm font-medium text-gray-900 truncate">{user.name}</p>
                                        <p className="text-xs text-gray-500 truncate">{user.role_label}</p>
                                    </div>
                                    <div className="py-1">
                                        <Link
                                            href={profileEdit.url()}
                                            className="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                            role="menuitem"
                                            onClick={() => setUserMenuOpen(false)}
                                        >
                                            <UserCircleIcon className="w-4 h-4" />
                                            Mon profil
                                        </Link>
                                        <button
                                            onClick={handleLogout}
                                            className="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
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
                </div>
            </header>

            {/* Mobile menu */}
            {mobileOpen && (
                <>
                    <div
                        className="fixed inset-0 z-20 bg-black/20"
                        onClick={() => setMobileOpen(false)}
                        aria-hidden="true"
                    />
                    <nav
                        className="fixed top-14 left-0 right-0 z-30 bg-white border-b border-gray-200 py-2 px-4 space-y-1 shadow-sm"
                        aria-label="Navigation mobile"
                    >
                        {items.map((item) => {
                            const active = isActive(item.href, url);
                            const Icon = item.icon;
                            return (
                                <Link
                                    key={item.label}
                                    href={item.href}
                                    aria-current={active ? 'page' : undefined}
                                    onClick={() => setMobileOpen(false)}
                                    className={cn(
                                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                                        active
                                            ? 'text-gray-900 bg-gray-100'
                                            : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50',
                                    )}
                                >
                                    <Icon className="w-5 h-5 shrink-0" />
                                    <span className="flex-1">{item.label}</span>
                                    {item.badge !== undefined && (
                                        <span className="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold bg-red-500 text-white rounded-full">
                                            {item.badge > 9 ? '9+' : item.badge}
                                        </span>
                                    )}
                                </Link>
                            );
                        })}
                    </nav>
                </>
            )}
        </>
    );
}
