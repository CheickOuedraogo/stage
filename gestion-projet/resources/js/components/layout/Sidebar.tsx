import { Badge } from '@/components/ui/Badge';
import { cn } from '@/lib/utils';
import { auditLog as adminAuditLog, dashboard as adminDashboard } from '@/routes/admin';
import { index as adminChatIndex } from '@/routes/admin/chat';
import { index as adminFaqIndex } from '@/routes/admin/faq';
import { index as usersIndex } from '@/routes/admin/users';
import { dashboard as acDashboard } from '@/routes/ac';
import { index as acChatIndex } from '@/routes/ac/chat';
import { index as acDemandesIndex } from '@/routes/ac/demandes';
import { index as acPaiementsIndex } from '@/routes/ac/paiements';
import { dashboard as dafDashboard } from '@/routes/daf';
import { index as dafChatIndex } from '@/routes/daf/chat';
import { index as dafDemandesIndex } from '@/routes/daf/demandes';
import { index as dafProjetsIndex } from '@/routes/daf/projets';
import { index as dafRapportsIndex } from '@/routes/daf/rapports';
import { index as dafRubriquesIndex } from '@/routes/daf/rubriques';
import { index as dafVersementsIndex } from '@/routes/daf/versements';
import { index as porteurChatIndex } from '@/routes/porteur/chat';
import { index as projetsIndex } from '@/routes/porteur/projets';
import { index as porteurDemandesIndex } from '@/routes/porteur/demandes';
import { index as notificationsIndex } from '@/routes/notifications';
import { edit as profileEdit } from '@/routes/profile';
import type { User } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    BellIcon,
    BookOpenIcon,
    ChartBarIcon,
    ChatBubbleLeftRightIcon,
    ClipboardDocumentListIcon,
    CreditCardIcon,
    FolderIcon,
    HomeIcon,
    ScaleIcon,
    UserCircleIcon,
    UsersIcon,
} from '@heroicons/react/24/outline';
import type { ComponentType, SVGProps } from 'react';

interface NavItem {
    label: string;
    href: string;
    icon: ComponentType<SVGProps<SVGSVGElement>>;
    badge?: number | string;
}

function getNavItems(role: User['role_key'], notifs: number): NavItem[] {
    const notifBadge = notifs > 0 ? notifs : undefined;

    const navByRole: Record<User['role_key'], NavItem[]> = {
        admin: [
            { label: 'Tableau de bord', href: adminDashboard.url(), icon: HomeIcon },
            { label: 'Utilisateurs', href: usersIndex.url(), icon: UsersIcon },
            { label: 'Journal d\'audit', href: adminAuditLog.url(), icon: ClipboardDocumentListIcon },
            { label: 'FAQ', href: adminFaqIndex.url(), icon: BookOpenIcon },
            { label: 'Messages', href: adminChatIndex.url(), icon: ChatBubbleLeftRightIcon },
        ],
        daf: [
            { label: 'Tableau de bord', href: dafDashboard.url(), icon: ChartBarIcon },
            { label: 'Projets', href: dafProjetsIndex.url(), icon: FolderIcon },
            { label: 'Demandes', href: dafDemandesIndex.url(), icon: ClipboardDocumentListIcon },
            { label: 'Versements', href: dafVersementsIndex.url(), icon: ScaleIcon },
            { label: 'Rubriques', href: dafRubriquesIndex.url(), icon: BookOpenIcon },
            { label: 'Rapports', href: dafRapportsIndex.url(), icon: ChartBarIcon },
            { label: 'Assistance', href: dafChatIndex.url(), icon: ChatBubbleLeftRightIcon },
            { label: 'Notifications', href: notificationsIndex.url(), icon: BellIcon, badge: notifBadge },
        ],
        ac: [
            { label: 'Tableau de bord', href: acDashboard.url(), icon: HomeIcon },
            { label: 'Demandes', href: acDemandesIndex.url(), icon: ClipboardDocumentListIcon },
            { label: 'Paiements', href: acPaiementsIndex.url(), icon: CreditCardIcon },
            { label: 'Assistance', href: acChatIndex.url(), icon: ChatBubbleLeftRightIcon },
            { label: 'Notifications', href: notificationsIndex.url(), icon: BellIcon, badge: notifBadge },
        ],
        porteur: [
            { label: 'Mes Projets', href: projetsIndex.url(), icon: FolderIcon },
            { label: 'Mes Demandes', href: porteurDemandesIndex.url(), icon: ClipboardDocumentListIcon },
            { label: 'Mon Profil', href: profileEdit.url(), icon: UserCircleIcon },
            { label: 'Assistance', href: porteurChatIndex.url(), icon: ChatBubbleLeftRightIcon },
            { label: 'Notifications', href: notificationsIndex.url(), icon: BellIcon, badge: notifBadge },
        ],
    };

    return navByRole[role] ?? [];
}

function isLinkActive(href: string, currentUrl: string): boolean {
    if (href === '#') return false;
    // Exact match OR href is a path prefix followed by '/' or '?'
    if (currentUrl === href) return true;
    return currentUrl.startsWith(href + '/') || currentUrl.startsWith(href + '?');
}

interface SidebarProps {
    user: User;
    collapsed?: boolean;
}

export function Sidebar({ user, collapsed = false }: SidebarProps) {
    const { url } = usePage();
    const items = getNavItems(user.role_key, user.notifications_non_lues);

    return (
        <aside
            className={cn(
                'flex flex-col h-full bg-[#1e3a5f] text-white',
                'transition-all duration-300',
                collapsed ? 'w-16' : 'w-64',
            )}
            aria-label="Navigation principale"
        >
            {/* Logo */}
            <div
                className={cn(
                    'flex items-center gap-3 px-4 py-5 border-b border-white/10',
                    collapsed && 'justify-center',
                )}
            >
                <div className="w-9 h-9 rounded-xl bg-blue-500 flex items-center justify-center shrink-0 font-bold text-sm">
                    C
                </div>
                {!collapsed && (
                    <div>
                        <p className="font-bold text-sm leading-tight">CIFEU</p>
                        <p className="text-xs text-blue-200">Gestion Financière</p>
                    </div>
                )}
            </div>

            {/* Navigation */}
            <nav className="flex-1 px-2 py-4 space-y-1 overflow-y-auto" role="navigation">
                {items.map((item) => {
                    const Icon = item.icon;
                    const active = isLinkActive(item.href, url);

                    return (
                        <Link
                            key={item.label}
                            href={item.href}
                            aria-label={item.label}
                            aria-current={active ? 'page' : undefined}
                            className={cn(
                                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium',
                                'transition-all duration-150 relative',
                                active
                                    ? 'bg-white/15 text-white'
                                    : 'text-blue-100 hover:bg-white/10 hover:text-white',
                                collapsed && 'justify-center',
                            )}
                        >
                            {active && (
                                <span
                                    className="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-blue-400 rounded-r-full"
                                    aria-hidden="true"
                                />
                            )}
                            <Icon className="w-5 h-5 shrink-0" />
                            {!collapsed && (
                                <>
                                    <span className="flex-1">{item.label}</span>
                                    {item.badge !== undefined && (
                                        <Badge
                                            variant="danger"
                                            className="text-[10px] px-1.5 py-0.5 min-w-[18px] justify-center"
                                        >
                                            {item.badge}
                                        </Badge>
                                    )}
                                </>
                            )}
                            {collapsed && item.badge !== undefined && (
                                <span
                                    className="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"
                                    aria-label={`${item.badge} notifications`}
                                />
                            )}
                        </Link>
                    );
                })}
            </nav>

            {/* Role badge */}
            {!collapsed && (
                <div className="px-4 py-3 border-t border-white/10">
                    <p className="text-xs text-blue-300">Connecté en tant que</p>
                    <p className="text-sm font-medium text-white truncate">{user.label_role}</p>
                </div>
            )}
        </aside>
    );
}
