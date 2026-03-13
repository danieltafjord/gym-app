import { Link, usePage } from '@inertiajs/react';
import {
    Building2,
    Check,
    ChevronsUpDown,
    ClipboardList,
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
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
import AppLogoIcon from './app-logo-icon';
import { show as teamShow } from '@/routes/team';
import { index as teamGymsIndex } from '@/routes/team/gyms';
import { general as teamGymsSettingsGeneral } from '@/routes/team/gyms/settings';
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
    const hasMultipleTeams = managedTeams.length > 1;
    const activeTeamName = currentTeam?.name ?? managedTeams[0]?.name ?? 'GymApp';
    const gymNavigationItem = currentTeam
        ? currentTeam.singleGym
            ? {
                  title: 'Gym Settings',
                  href: teamGymsSettingsGeneral({
                      team: currentTeam.slug,
                      gym: currentTeam.singleGym.slug,
                  }),
                  icon: Dumbbell,
              }
            : {
                  title: 'Gyms',
                  href: teamGymsIndex(currentTeam.slug),
                  icon: Dumbbell,
              }
        : null;

    const teamNavItems: NavItem[] = currentTeam
        ? [
              {
                  title: 'Dashboard',
                  href: teamShow(currentTeam.slug),
                  icon: LayoutGrid,
              },
              ...(gymNavigationItem ? [gymNavigationItem] : []),
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
                        {hasMultipleTeams ? (
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <SidebarMenuButton
                                        size="lg"
                                        className="text-sidebar-accent-foreground data-[state=open]:bg-sidebar-accent"
                                    >
                                        <TeamIdentity
                                            teamName={activeTeamName}
                                            showSelectorIcon
                                        />
                                    </SidebarMenuButton>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                                    align="start"
                                    side="bottom"
                                >
                                    {managedTeams.map((team) => (
                                        <DropdownMenuItem key={team.id} asChild>
                                            <Link
                                                href={teamShow(team.slug)}
                                                prefetch
                                                className="w-full"
                                            >
                                                <span className="truncate">
                                                    {team.name}
                                                </span>
                                                {currentTeam?.id ===
                                                    team.id && (
                                                    <Check className="ml-auto size-4" />
                                                )}
                                            </Link>
                                        </DropdownMenuItem>
                                    ))}
                                </DropdownMenuContent>
                            </DropdownMenu>
                        ) : (
                            <SidebarMenuButton size="lg" asChild>
                                <Link
                                    href={
                                        currentTeam
                                            ? teamShow(currentTeam.slug)
                                            : '/account'
                                    }
                                    prefetch
                                >
                                    <TeamIdentity teamName={activeTeamName} />
                                </Link>
                            </SidebarMenuButton>
                        )}
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {teamNavItems.length > 0 && (
                    <NavMain items={teamNavItems} />
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

function TeamIdentity({
    teamName,
    showSelectorIcon = false,
}: {
    teamName: string;
    showSelectorIcon?: boolean;
}) {
    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 flex items-center gap-1 truncate leading-tight font-semibold">
                    {showSelectorIcon && (
                        <ChevronsUpDown className="size-3.5 shrink-0" />
                    )}
                    <span className="truncate">{teamName}</span>
                </span>
            </div>
        </>
    );
}
