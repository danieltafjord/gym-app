import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Gym, PaginatedData, Team } from '@/types';
import team from '@/routes/team';

export default function GymIndex({
    team: currentTeam,
    gyms,
}: {
    team: Team;
    gyms: PaginatedData<Gym>;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Gyms',
            href: team.gyms.index(currentTeam.slug).url,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${currentTeam.name} - Gyms`} />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading title="Gyms" description="Manage your team's gym locations." />
                    <Button asChild>
                        <Link href={team.gyms.create(currentTeam.slug).url}>
                            Add Gym
                        </Link>
                    </Button>
                </div>

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">Name</th>
                                <th className="px-4 py-3 text-left font-medium">Address</th>
                                <th className="px-4 py-3 text-left font-medium">Status</th>
                                <th className="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {gyms.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No gyms found. Add your first gym to get started.
                                    </td>
                                </tr>
                            ) : (
                                gyms.data.map((gym) => (
                                    <tr key={gym.id}>
                                        <td className="px-4 py-3 font-medium">
                                            {gym.name}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {gym.address ?? '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge variant={gym.is_active ? 'default' : 'secondary'}>
                                                {gym.is_active ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link
                                                    href={team.gyms.edit({
                                                        team: currentTeam.slug,
                                                        gym: gym.slug,
                                                    }).url}
                                                >
                                                    Edit
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {gyms.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        {gyms.links.map((link, index) => (
                            <Button
                                key={index}
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                disabled={!link.url}
                                asChild={!!link.url}
                            >
                                {link.url ? (
                                    <Link
                                        href={link.url}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ) : (
                                    <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                )}
                            </Button>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
