import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import { show as teamShow } from '@/routes/team';
import type { BreadcrumbItem, PaginatedData, Team } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: admin.dashboard().url,
    },
    {
        title: 'Teams',
        href: admin.teams.index().url,
    },
];

export default function TeamsIndex({
    teams,
}: {
    teams: PaginatedData<Team>;
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Manage Teams" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Manage Teams"
                        description="View and manage all teams on the platform"
                    />
                    <Button asChild>
                        <Link href={admin.teams.create().url}>
                            Create Team
                        </Link>
                    </Button>
                </div>

                <div className="rounded-xl border">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b bg-muted/50">
                                <th className="px-4 py-3 text-left font-medium">
                                    Name
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Owner
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Gyms
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Members
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Status
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {teams.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No teams found.
                                    </td>
                                </tr>
                            )}
                            {teams.data.map((team) => (
                                <tr
                                    key={team.id}
                                    className="border-b last:border-0"
                                >
                                    <td className="px-4 py-3 font-medium">
                                        {team.name}
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">
                                        {team.owner?.name ?? 'N/A'}
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">
                                        {team.gyms_count ?? 0}
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">
                                        {team.memberships_count ?? 0}
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge
                                            variant={
                                                team.is_active
                                                    ? 'default'
                                                    : 'secondary'
                                            }
                                        >
                                            {team.is_active
                                                ? 'Active'
                                                : 'Inactive'}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-3 text-right">
                                        <div className="flex justify-end gap-1">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <Link
                                                    href={teamShow(team.slug)}
                                                >
                                                    View
                                                </Link>
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                asChild
                                            >
                                                <Link
                                                    href={admin.teams.edit.url(
                                                        team,
                                                    )}
                                                >
                                                    Edit
                                                </Link>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {teams.last_page > 1 && (
                    <div className="flex items-center justify-between">
                        <p className="text-sm text-muted-foreground">
                            Showing {teams.from} to {teams.to} of{' '}
                            {teams.total} teams
                        </p>
                        <div className="flex gap-1">
                            {teams.links.map((link, index) => (
                                <Button
                                    key={index}
                                    variant={
                                        link.active ? 'default' : 'outline'
                                    }
                                    size="sm"
                                    disabled={!link.url}
                                    asChild={!!link.url}
                                >
                                    {link.url ? (
                                        <Link
                                            href={link.url}
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    ) : (
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    )}
                                </Button>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
