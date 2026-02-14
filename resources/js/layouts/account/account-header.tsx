import { Link, router, usePage } from '@inertiajs/react';
import { Building2, LogOut, Settings, Shield, User } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import { logout } from '@/routes';
import { show as teamShow } from '@/routes/team';

const navItems = [
    { title: 'Dashboard', href: '/account' },
    { title: 'Settings', href: '/account/settings/profile' },
];

export function AccountHeader() {
    const { auth } = usePage().props;
    const { currentUrl } = useCurrentUrl();

    const isSuperAdmin = auth.roles.includes('super-admin');
    const managedTeams = auth.managedTeams ?? [];

    const handleLogout = () => {
        router.flushAll();
    };

    return (
        <header className="border-b bg-background">
            <div className="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6">
                <div className="flex items-center gap-6">
                    <Link href="/account" className="flex items-center gap-2">
                        <div className="flex size-8 items-center justify-center rounded-md bg-primary text-primary-foreground">
                            <AppLogoIcon className="size-5 fill-current" />
                        </div>
                    </Link>

                    <nav className="flex items-center gap-1">
                        {navItems.map((item) => (
                            <Button
                                key={item.href}
                                variant="ghost"
                                size="sm"
                                asChild
                                className={cn(
                                    currentUrl.startsWith(item.href) &&
                                        (item.href === '/account'
                                            ? currentUrl === '/account'
                                            : true) &&
                                        'bg-muted',
                                )}
                            >
                                <Link href={item.href} prefetch>
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            variant="ghost"
                            size="sm"
                            className="gap-2"
                        >
                            <User className="size-4" />
                            <span className="hidden sm:inline">
                                {auth.user.name}
                            </span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-56">
                        <DropdownMenuItem asChild>
                            <Link
                                href="/account/settings/profile"
                                className="cursor-pointer"
                            >
                                <Settings className="mr-2 size-4" />
                                Settings
                            </Link>
                        </DropdownMenuItem>

                        {managedTeams.length > 0 && (
                            <>
                                <DropdownMenuSeparator />
                                <DropdownMenuLabel className="text-xs text-muted-foreground">
                                    My Teams
                                </DropdownMenuLabel>
                                {managedTeams.map((team) => (
                                    <DropdownMenuItem key={team.id} asChild>
                                        <Link
                                            href={teamShow(team.slug)}
                                            className="cursor-pointer"
                                        >
                                            <Building2 className="mr-2 size-4" />
                                            {team.name}
                                        </Link>
                                    </DropdownMenuItem>
                                ))}
                            </>
                        )}

                        {isSuperAdmin && (
                            <>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem asChild>
                                    <Link
                                        href="/admin"
                                        className="cursor-pointer"
                                    >
                                        <Shield className="mr-2 size-4" />
                                        Admin Panel
                                    </Link>
                                </DropdownMenuItem>
                            </>
                        )}

                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                            <Link
                                href={logout()}
                                as="button"
                                className="w-full cursor-pointer"
                                onClick={handleLogout}
                            >
                                <LogOut className="mr-2 size-4" />
                                Log out
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}
