import { Head, Link } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { resolvePlanAccessSummary } from '@/lib/membership-plans';
import AppLayout from '@/layouts/app-layout';
import type {
    BreadcrumbItem,
    MembershipPlan,
    PaginatedData,
    Team,
} from '@/types';
import team from '@/routes/team';

export default function PlanIndex({
    team: currentTeam,
    plans,
    publicPlansUrl,
}: {
    team: Team;
    plans: PaginatedData<MembershipPlan>;
    publicPlansUrl: string;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Plans',
            href: team.plans.index(currentTeam.slug).url,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${currentTeam.name} - Plans`} />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Membership Plans"
                        description="Manage your team's membership plans."
                    />
                    <div className="flex items-center gap-2">
                        <Button variant="outline" asChild>
                            <Link
                                href={publicPlansUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                View Public Plans
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link
                                href={team.plans.create(currentTeam.slug).url}
                            >
                                Add Plan
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="overflow-hidden rounded-lg border">
                    <table className="w-full text-sm">
                        <thead className="border-b bg-muted/50">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium">
                                    Name
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Type
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Price
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Access
                                </th>
                                <th className="px-4 py-3 text-left font-medium">
                                    Status
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {plans.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No plans found. Create your first
                                        membership plan.
                                    </td>
                                </tr>
                            ) : (
                                plans.data.map((plan) => (
                                    <tr key={plan.id}>
                                        <td className="px-4 py-3 font-medium">
                                            {plan.name}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge variant="outline">
                                                {plan.plan_type === 'recurring'
                                                    ? 'Recurring'
                                                    : 'One-time'}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="font-medium">
                                                ${plan.price_formatted}
                                            </div>
                                            {plan.yearly_price_formatted && (
                                                <div className="text-xs text-muted-foreground">
                                                    Yearly $
                                                    {
                                                        plan.yearly_price_formatted
                                                    }
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 text-muted-foreground">
                                            {resolvePlanAccessSummary(plan) ??
                                                `Billed ${plan.billing_period}`}
                                            {plan.requires_account && (
                                                <div className="text-xs text-primary">
                                                    Account required
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge
                                                variant={
                                                    plan.is_active
                                                        ? 'default'
                                                        : 'secondary'
                                                }
                                            >
                                                {plan.is_active
                                                    ? 'Active'
                                                    : 'Inactive'}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-end">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    asChild
                                                >
                                                    <Link
                                                        href={
                                                            team.plans.edit({
                                                                team: currentTeam.slug,
                                                                plan: plan.id,
                                                            }).url
                                                        }
                                                    >
                                                        Edit
                                                    </Link>
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {plans.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        {plans.links.map((link, index) => (
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
