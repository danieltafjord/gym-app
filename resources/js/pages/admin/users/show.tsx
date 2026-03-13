import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import admin from '@/routes/admin';
import type { BreadcrumbItem, Membership, Team, User } from '@/types';

type UserDetail = User & {
    roles: { name: string }[];
    owned_teams: Team[];
    memberships: Membership[];
};

export default function UsersShow({ user }: { user: UserDetail }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Admin',
            href: admin.dashboard().url,
        },
        {
            title: 'Users',
            href: admin.users.index().url,
        },
        {
            title: user.name,
            href: admin.users.show.url(user),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={user.name} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <Heading
                    title={user.name}
                    description="User details and associations"
                />

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>User Information</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Name
                                </p>
                                <p className="font-medium">{user.name}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Email
                                </p>
                                <p className="font-medium">{user.email}</p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Email Verified
                                </p>
                                <Badge
                                    variant={
                                        user.email_verified_at
                                            ? 'default'
                                            : 'secondary'
                                    }
                                >
                                    {user.email_verified_at
                                        ? 'Verified'
                                        : 'Unverified'}
                                </Badge>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">
                                    Created
                                </p>
                                <p className="font-medium">
                                    {new Date(
                                        user.created_at,
                                    ).toLocaleDateString()}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Roles</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {user.roles.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    No roles assigned.
                                </p>
                            ) : (
                                <div className="flex flex-wrap gap-2">
                                    {user.roles.map((role) => (
                                        <Badge
                                            key={role.name}
                                            variant="outline"
                                        >
                                            {role.name}
                                        </Badge>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Owned Teams</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {user.owned_teams.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                This user does not own any teams.
                            </p>
                        ) : (
                            <div className="rounded-xl border">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b bg-muted/50">
                                            <th className="px-4 py-3 text-left font-medium">
                                                Name
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
                                        {user.owned_teams.map((team) => (
                                            <tr
                                                key={team.id}
                                                className="border-b last:border-0"
                                            >
                                                <td className="px-4 py-3 font-medium">
                                                    {team.name}
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
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Memberships</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {user.memberships.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                This user has no memberships.
                            </p>
                        ) : (
                            <div className="rounded-xl border">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b bg-muted/50">
                                            <th className="px-4 py-3 text-left font-medium">
                                                Team
                                            </th>
                                            <th className="px-4 py-3 text-left font-medium">
                                                Plan
                                            </th>
                                            <th className="px-4 py-3 text-left font-medium">
                                                Status
                                            </th>
                                            <th className="px-4 py-3 text-left font-medium">
                                                Started
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {user.memberships.map((membership) => (
                                            <tr
                                                key={membership.id}
                                                className="border-b last:border-0"
                                            >
                                                <td className="px-4 py-3 font-medium">
                                                    {membership.team?.name ??
                                                        'N/A'}
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    {membership.plan?.name ??
                                                        'N/A'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    <Badge
                                                        variant={
                                                            membership.status ===
                                                            'active'
                                                                ? 'default'
                                                                : 'secondary'
                                                        }
                                                    >
                                                        {membership.status}
                                                    </Badge>
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    {membership.starts_at
                                                        ? new Date(
                                                              membership.starts_at,
                                                          ).toLocaleDateString()
                                                        : 'Not activated'}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
