import { Head, Link, router } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import type {
    BreadcrumbItem,
    Membership,
    MembershipPlan,
    PaginatedData,
    Team,
} from '@/types';
import team from '@/routes/team';
import { useRef } from 'react';

type Filters = {
    search: string;
    status: string;
    plan: string;
};

export default function MemberIndex({
    team: currentTeam,
    members,
    plans,
    filters,
}: {
    team: Team;
    members: PaginatedData<Membership>;
    plans: MembershipPlan[];
    filters: Filters;
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

    const searchTimeout = useRef<ReturnType<typeof setTimeout>>(null);

    const applyFilters = (newFilters: Partial<Filters>) => {
        const merged = { ...filters, ...newFilters };

        // Remove empty values
        const params: Record<string, string> = {};
        for (const [key, value] of Object.entries(merged)) {
            if (value) {
                params[key] = value;
            }
        }

        router.get(team.members.index(currentTeam.slug).url, params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSearch = (value: string) => {
        if (searchTimeout.current) {
            clearTimeout(searchTimeout.current);
        }
        searchTimeout.current = setTimeout(() => {
            applyFilters({ search: value });
        }, 300);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${currentTeam.name} - Members`} />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Members"
                        description="View and manage your team's members."
                    />
                    <div className="flex items-center gap-2">
                        <Button variant="outline" asChild>
                            <a
                                href={`${team.members.export(currentTeam.slug).url}?${new URLSearchParams(
                                    Object.fromEntries(
                                        Object.entries(filters).filter(
                                            ([, v]) => v,
                                        ),
                                    ),
                                ).toString()}`}
                            >
                                Export CSV
                            </a>
                        </Button>
                        <Button asChild>
                            <Link
                                href={team.members.create(currentTeam.slug).url}
                            >
                                Add Member
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="Search by name or email..."
                        defaultValue={filters.search}
                        onChange={(e) => handleSearch(e.target.value)}
                        className="w-64"
                    />
                    <Select
                        value={filters.status || 'all'}
                        onValueChange={(value) =>
                            applyFilters({
                                status: value === 'all' ? '' : value,
                            })
                        }
                    >
                        <SelectTrigger className="w-40">
                            <SelectValue placeholder="All statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All statuses</SelectItem>
                            <SelectItem value="active">Active</SelectItem>
                            <SelectItem value="paused">Paused</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                            <SelectItem value="expired">Expired</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select
                        value={filters.plan || 'all'}
                        onValueChange={(value) =>
                            applyFilters({ plan: value === 'all' ? '' : value })
                        }
                    >
                        <SelectTrigger className="w-48">
                            <SelectValue placeholder="All plans" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All plans</SelectItem>
                            {plans.map((plan) => (
                                <SelectItem
                                    key={plan.id}
                                    value={String(plan.id)}
                                >
                                    {plan.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">
                                    Name
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Email
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Plan
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Status
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Joined
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Actions
                                </th>
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
                                            {membership.customer_name}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {membership.email}
                                        </td>
                                        <td className="px-4 py-3">
                                            {membership.plan?.name ?? '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge
                                                variant={statusVariant(
                                                    membership.status,
                                                )}
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
                                        <td className="px-4 py-3 text-right">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                asChild
                                            >
                                                <Link
                                                    href={
                                                        team.members.show({
                                                            team: currentTeam.slug,
                                                            membership:
                                                                membership.id,
                                                        }).url
                                                    }
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
                )}
            </div>
        </AppLayout>
    );
}
