import { Link, usePage } from '@inertiajs/react';
import {
    Building2,
    ClipboardList,
    CreditCard,
    Dumbbell,
    History,
    LayoutGrid,
    ScanLine,
    Settings,
    Shield,
    User,
    Users,
} from 'lucide-react';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';
import AppLogo from './app-logo';
import { show as teamShow } from '@/routes/team';
import { index as teamGymsIndex } from '@/routes/team/gyms';
import { index as teamPlansIndex } from '@/routes/team/plans';
import { index as teamMembersIndex } from '@/routes/team/members';
import { scanner as teamCheckInScanner } from '@/routes/team/check-in';
import { index as teamCheckInsIndex } from '@/routes/team/check-ins';
import { widgetDefaults as teamSettingsWidgetDefaults } from '@/routes/team/settings';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as adminTeamsIndex } from '@/routes/admin/teams';
import { index as adminUsersIndex } from '@/routes/admin/users';

const adminNavItems: NavItem[] = [
    {
        title: 'Admin Dashboard',
        href: adminDashboard(),
        icon: Shield,
    },
    {
        title: 'Teams',
        href: adminTeamsIndex(),
        icon: Building2,
    },
    {
        title: 'Users',
        href: adminUsersIndex(),
        icon: Users,
    },
];

export function AppSidebar() {
    const { auth, currentTeam } = usePage().props;
    const { isCurrentUrl } = useCurrentUrl();

    const isSuperAdmin = auth.roles.includes('super-admin');
    const managedTeams = auth.managedTeams ?? [];

    const teamNavItems: NavItem[] = currentTeam
        ? [
              {
                  title: 'Dashboard',
                  href: teamShow(currentTeam.slug),
                  icon: LayoutGrid,
              },
              {
                  title: 'Gyms',
                  href: teamGymsIndex(currentTeam.slug),
                  icon: Dumbbell,
              },
              {
                  title: 'Plans',
                  href: teamPlansIndex(currentTeam.slug),
                  icon: ClipboardList,
              },
              {
                  title: 'Check-In',
                  href: teamCheckInScanner(currentTeam.slug).url,
                  icon: ScanLine,
              },
              {
                  title: 'Check-In Log',
                  href: teamCheckInsIndex(currentTeam.slug).url,
                  icon: History,
              },
              {
                  title: 'Members',
                  href: teamMembersIndex(currentTeam.slug),
                  icon: Users,
              },
              {
                  title: 'Settings',
                  href: teamSettingsWidgetDefaults(currentTeam.slug).url,
                  icon: Settings,
              },
          ]
        : [];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link
                                href={
                                    currentTeam
                                        ? teamShow(currentTeam.slug)
                                        : '/account'
                                }
                                prefetch
                            >
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {teamNavItems.length > 0 && (
                    <NavMain items={teamNavItems} />
                )}

                {managedTeams.length > 0 && (
                    <SidebarGroup className="px-2 py-0">
                        <SidebarGroupLabel>My Teams</SidebarGroupLabel>
                        <SidebarMenu>
                            {managedTeams.map((team) => (
                                <SidebarMenuItem key={team.id}>
                                    <SidebarMenuButton
                                        asChild
                                        isActive={
                                            currentTeam?.id === team.id
                                        }
                                        tooltip={{ children: team.name }}
                                    >
                                        <Link
                                            href={teamShow(team.slug)}
                                            prefetch
                                        >
                                            <Building2 />
                                            <span>{team.name}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            ))}
                        </SidebarMenu>
                    </SidebarGroup>
                )}

                {isSuperAdmin && (
                    <SidebarGroup className="px-2 py-0">
                        <SidebarGroupLabel>Administration</SidebarGroupLabel>
                        <SidebarMenu>
                            {adminNavItems.map((item) => (
                                <SidebarMenuItem key={item.title}>
                                    <SidebarMenuButton
                                        asChild
                                        isActive={isCurrentUrl(item.href)}
                                        tooltip={{ children: item.title }}
                                    >
                                        <Link href={item.href} prefetch>
                                            {item.icon && <item.icon />}
                                            <span>{item.title}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            ))}
                        </SidebarMenu>
                    </SidebarGroup>
                )}
            </SidebarContent>

            <SidebarFooter>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            asChild
                            tooltip={{ children: 'Account' }}
                        >
                            <Link href="/account" prefetch>
                                <User />
                                <span>Account</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
