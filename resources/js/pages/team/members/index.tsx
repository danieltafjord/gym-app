import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Membership, PaginatedData, Team } from '@/types';
import team from '@/routes/team';

export default function MemberIndex({
    team: currentTeam,
    members,
}: {
    team: Team;
    members: PaginatedData<Membership>;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Members',
            href: team.members.index(currentTeam.slug).url,
        },
    ];

    const statusVariant = (status: Membership['status']) => {
        switch (status) {
            case 'active':
                return 'default' as const;
            case 'cancelled':
                return 'destructive' as const;
            default:
                return 'secondary' as const;
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${currentTeam.name} - Members`} />

            <div className="space-y-6 p-4">
                <Heading title="Members" description="View and manage your team's members." />

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">Name</th>
                                <th className="px-4 py-3 text-left font-medium">Email</th>
                                <th className="px-4 py-3 text-left font-medium">Plan</th>
                                <th className="px-4 py-3 text-left font-medium">Status</th>
                                <th className="px-4 py-3 text-left font-medium">Joined</th>
                                <th className="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {members.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No members found.
                                    </td>
                                </tr>
                            ) : (
                                members.data.map((membership) => (
                                    <tr key={membership.id}>
                                        <td className="px-4 py-3 font-medium">
                                            {membership.user?.name ?? 'Unknown'}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {membership.user?.email ?? '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            {membership.plan?.name ?? '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge variant={statusVariant(membership.status)}>
                                                {membership.status}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {new Date(membership.starts_at).toLocaleDateString()}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link
                                                    href={team.members.show({
                                                        team: currentTeam.slug,
                                                        membership: membership.id,
                                                    }).url}
                                                >
                                                    View
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {members.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        {members.links.map((link, index) => (
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
