import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import team from '@/routes/team';

export default function TeamSettingsLayout({
    teamSlug,
    children,
}: PropsWithChildren<{ teamSlug: string }>) {
    const { isCurrentUrl } = useCurrentUrl();

    const sidebarNavItems = [
        {
            title: 'Widget Defaults',
            href: team.settings.widgetDefaults.url(teamSlug),
        },
    ];

    if (typeof window === 'undefined') {
        return null;
    }

    return (
        <div>
            <Heading
                title="Team Settings"
                description="Manage your team's default settings"
            />

            <div className="flex flex-col lg:flex-row lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav
                        className="flex flex-col space-y-1 space-x-0"
                        aria-label="Settings"
                    >
                        {sidebarNavItems.map((item) => (
                            <Button
                                key={item.href}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('w-full justify-start', {
                                    'bg-muted': isCurrentUrl(item.href),
                                })}
                            >
                                <Link href={item.href}>{item.title}</Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 lg:hidden" />

                <div className="flex-1">
                    <section>{children}</section>
                </div>
            </div>
        </div>
    );
}
