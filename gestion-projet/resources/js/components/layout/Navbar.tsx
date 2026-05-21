import { cn, getInitials } from '@/lib/utils';
import { useDarkMode } from '@/hooks/useDarkMode';
import { logout } from '@/routes';
import { auditLog as adminAuditLog, dashboard as adminDashboard } from '@/routes/admin';
import { index as usersIndex } from '@/routes/admin/users';
import { index as adminFaqIndex } from '@/routes/admin/faq';
import { index as adminChatIndex } from '@/routes/admin/chat';
import { dashboard as acDashboard } from '@/routes/ac';
import { index as acDemandesIndex } from '@/routes/ac/demandes';
import { index as acChatIndex } from '@/routes/ac/chat';
import { dashboard as dafDashboard } from '@/routes/daf';
import { index as dafDemandesIndex } from '@/routes/daf/demandes';
import { index as dafProjetsIndex } from '@/routes/daf/projets';
import { index as dafRapportsIndex } from '@/routes/daf/rapports';
import { index as dafChatIndex } from '@/routes/daf/chat';
import { index as acPaiementsIndex } from '@/routes/ac/paiements';
import { dashboard as porteurDashboard } from '@/routes/porteur';
import { index as porteurDemandesIndex } from '@/routes/porteur/demandes';
import { index as porteurProjetsIndex } from '@/routes/porteur/projets';
import { index as porteurFaqIndex } from '@/routes/porteur/faq';
import { index as notificationsIndex } from '@/routes/notifications';
import { edit as profileEdit } from '@/routes/profile';
import type { User } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import {
    ArrowRightStartOnRectangleIcon,
    Bars3Icon,
    BellIcon,
    ChartBarIcon,
    ChatBubbleLeftRightIcon,
    ClipboardDocumentCheckIcon,
    ClipboardDocumentListIcon,
    CreditCardIcon,
    DocumentChartBarIcon,
    FolderIcon,
    HomeIcon,
    MoonIcon,
    QuestionMarkCircleIcon,
    SunIcon,
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

function getNavItems(role: User['utilisateur_role'], notifs: number): NavItem[] {
    const notifBadge = notifs > 0 ? notifs : undefined;

    const navByRole: Record<User['utilisateur_role'], NavItem[]> = {
        admin: [
            { label: 'Tableau de bord', href: adminDashboard.url(), icon: HomeIcon },
            { label: 'Utilisateurs', href: usersIndex.url(), icon: UsersIcon },
            { label: 'Journal d\'audit', href: adminAuditLog.url(), icon: ClipboardDocumentCheckIcon },
            { label: 'FAQ', href: adminFaqIndex.url(), icon: QuestionMarkCircleIcon },
            { label: 'Messages', href: adminChatIndex.url(), icon: ChatBubbleLeftRightIcon },
        ],
        daf: [
            { label: 'Tableau de bord', href: dafDashboard.url(), icon: ChartBarIcon },
            { label: 'Projets', href: dafProjetsIndex.url(), icon: FolderIcon },
            { label: 'Demandes', href: dafDemandesIndex.url(), icon: ClipboardDocumentListIcon },
            { label: 'Rapports', href: dafRapportsIndex.url(), icon: DocumentChartBarIcon },
            { label: 'Assistance', href: dafChatIndex.url(), icon: ChatBubbleLeftRightIcon },
        ],
        ac: [
            { label: 'Tableau de bord', href: acDashboard.url(), icon: HomeIcon },
            { label: 'Demandes', href: acDemandesIndex.url(), icon: ClipboardDocumentListIcon },
            { label: 'Paiements', href: acPaiementsIndex.url(), icon: CreditCardIcon },
            { label: 'Assistance', href: acChatIndex.url(), icon: ChatBubbleLeftRightIcon },
        ],
        porteur: [
            { label: 'Mes projets', href: porteurProjetsIndex.url(), icon: FolderIcon },
            { label: 'Mes demandes', href: porteurDemandesIndex.url(), icon: ClipboardDocumentListIcon },
            { label: 'Assistance', href: porteurFaqIndex.url(), icon: QuestionMarkCircleIcon },
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
    const { isDark, toggle: toggleDark } = useDarkMode();

    const items = getNavItems(user.utilisateur_role, user.notifications_non_lues);

    const handleLogout = () => {
        router.post(logout.url());
    };

    return (
        <>
            <header className="h-14 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-600 flex items-center px-4 lg:px-6 gap-4 sticky top-0 z-30">
                {/* Mobile hamburger */}
                <button
                    className="lg:hidden p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-600 dark:text-slate-400 transition-colors"
                    onClick={() => setMobileOpen((v) => !v)}
                    aria-label="Menu de navigation"
                    aria-expanded={mobileOpen}
                >
                    {mobileOpen ? <XMarkIcon className="w-5 h-5" /> : <Bars3Icon className="w-5 h-5" />}
                </button>

                {/* Brand */}
                <span className="text-sm font-semibold text-gray-900 dark:text-white tracking-tight shrink-0">CIFEU</span>

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
                                        ? 'text-gray-900 dark:text-white bg-gray-100 dark:bg-slate-800'
                                        : 'text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-slate-800',
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
                                    <span className="absolute bottom-0 left-3 right-3 h-0.5 bg-blue-600 dark:bg-blue-400 rounded-full" aria-hidden="true" />
                                )}
                            </Link>
                        );
                    })}
                </nav>

                {/* Spacer for desktop (nav already fills it) */}
                <div className="flex-1 lg:hidden" />

                {/* Right: dark mode + notifications + user */}
                <div className="flex items-center gap-2">
                    {/* Dark mode toggle */}
                    <button
                        onClick={toggleDark}
                        className="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                        aria-label={isDark ? 'Passer en mode clair' : 'Passer en mode sombre'}
                        title={isDark ? 'Mode clair' : 'Mode sombre'}
                    >
                        {isDark
                            ? <SunIcon className="w-4.5 h-4.5" />
                            : <MoonIcon className="w-4.5 h-4.5" />
                        }
                    </button>

                    {/* Notifications */}
                    <Link
                        href={notificationsIndex.url()}
                        className="relative p-2 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                        aria-label={`${user.notifications_non_lues} notification${user.notifications_non_lues !== 1 ? 's' : ''}`}
                    >
                        <BellIcon className="w-4.5 h-4.5" style={{ width: '1.125rem', height: '1.125rem' }} />
                        {user.notifications_non_lues > 0 && (
                            <span
                                className="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                                aria-hidden="true"
                            >
                                {user.notifications_non_lues > 9 ? '9+' : user.notifications_non_lues}
                            </span>
                        )}
                    </Link>

                    {/* User avatar + dropdown */}
                    <div className="relative">
                        <button
                            onClick={() => setUserMenuOpen((v) => !v)}
                            className="flex items-center gap-2 p-1 rounded-md hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors"
                            aria-label="Menu utilisateur"
                            aria-expanded={userMenuOpen}
                        >
                            {user.url_avatar ? (
                                <img
                                    src={user.url_avatar}
                                    alt={`Avatar de ${user.utilisateur_nom}`}
                                    className="w-7 h-7 rounded-full object-cover ring-2 ring-gray-200 dark:ring-slate-600"
                                />
                            ) : (
                                <div className="w-7 h-7 rounded-full bg-slate-700 dark:bg-slate-600 text-white text-xs font-bold flex items-center justify-center">
                                    {getInitials(user.utilisateur_nom)}
                                </div>
                            )}
                            <span className="hidden sm:block text-sm font-medium text-gray-700 dark:text-slate-300 max-w-[120px] truncate">
                                {user.utilisateur_nom.split(' ')[0]}
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
                                    className="absolute right-0 top-full mt-1.5 w-56 bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-gray-200 dark:border-slate-600 z-20 overflow-hidden"
                                    role="menu"
                                >
                                    <div className="px-4 py-3 border-b border-gray-100 dark:border-slate-600">
                                        <p className="text-sm font-medium text-gray-900 dark:text-white truncate">{user.utilisateur_nom}</p>
                                        <p className="text-xs text-gray-500 dark:text-slate-400 truncate">{user.label_role}</p>
                                    </div>
                                    <div className="py-1">
                                        <Link
                                            href={profileEdit.url()}
                                            className="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors"
                                            role="menuitem"
                                            onClick={() => setUserMenuOpen(false)}
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
                </div>
            </header>

            {/* Mobile menu */}
            {mobileOpen && (
                <>
                    <div
                        className="fixed inset-0 z-20 bg-black/20 dark:bg-black/40"
                        onClick={() => setMobileOpen(false)}
                        aria-hidden="true"
                    />
                    <nav
                        className="fixed top-14 left-0 right-0 z-30 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-600 py-2 px-4 space-y-1 shadow-sm"
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
                                            ? 'text-gray-900 dark:text-white bg-gray-100 dark:bg-slate-800'
                                            : 'text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-slate-800',
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
