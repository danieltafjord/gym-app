import { Head, Link, usePage } from '@inertiajs/react';
import { Building2, CreditCard, ExternalLink, Users } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Membership, Team } from '@/types';
import team from '@/routes/team';

export default function ShowTeam({
    team: currentTeam,
    recentMemberships,
}: {
    team: Team;
    recentMemberships: Membership[];
}) {
    const pageCurrentTeam = usePage().props.currentTeam;
    const singleGym = pageCurrentTeam?.singleGym ?? null;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
    ];

    const plansCount = currentTeam.membership_plans?.length ?? 0;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={currentTeam.name} />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title={currentTeam.name}
                        description={currentTeam.description ?? undefined}
                    />
                    <Button variant="outline" asChild>
                        <Link href={team.edit(currentTeam.slug).url}>
                            Edit Team
                        </Link>
                    </Button>
                </div>

                <div className={`grid gap-4 ${singleGym ? 'md:grid-cols-2' : 'md:grid-cols-3'}`}>
                    {!singleGym && (
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Gyms
                                </CardTitle>
                                <Building2 className="size-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {currentTeam.gyms_count ?? 0}
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Plans
                            </CardTitle>
                            <CreditCard className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {plansCount}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Members
                            </CardTitle>
                            <Users className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {currentTeam.memberships_count ?? 0}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Button variant="outline" asChild>
                        <Link
                            href={
                                singleGym
                                    ? team.gyms.settings.general.url({
                                          team: currentTeam.slug,
                                          gym: singleGym.slug,
                                      })
                                    : team.gyms.index(currentTeam.slug).url
                            }
                        >
                            <Building2 className="mr-2 size-4" />
                            {singleGym ? 'Gym Settings' : 'Manage Gyms'}
                        </Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href={team.plans.index(currentTeam.slug).url}>
                            <CreditCard className="mr-2 size-4" />
                            Manage Plans
                        </Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href={team.members.index(currentTeam.slug).url}>
                            <Users className="mr-2 size-4" />
                            Manage Members
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0">
                        <CardTitle>Stripe Payments</CardTitle>
                        <CreditCard className="size-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        {currentTeam.stripe_onboarding_complete ? (
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <span className="size-2 rounded-full bg-green-500" />
                                    <span className="text-sm font-medium">
                                        Connected
                                    </span>
                                </div>
                                <Button variant="outline" size="sm" asChild>
                                    <Link
                                        href={
                                            team.stripe.dashboard(
                                                currentTeam.slug,
                                            ).url
                                        }
                                    >
                                        <ExternalLink className="mr-1 size-3" />
                                        Stripe Dashboard
                                    </Link>
                                </Button>
                            </div>
                        ) : currentTeam.stripe_account_id ? (
                            <div className="space-y-3">
                                <div className="flex items-center gap-2">
                                    <span className="size-2 rounded-full bg-yellow-500" />
                                    <span className="text-sm font-medium">
                                        Setup Incomplete
                                    </span>
                                </div>
                                <Button size="sm" asChild>
                                    <Link
                                        href={
                                            team.stripe.onboard(
                                                currentTeam.slug,
                                            ).url
                                        }
                                    >
                                        Complete Setup
                                    </Link>
                                </Button>
                            </div>
                        ) : (
                            <div className="space-y-3">
                                <p className="text-sm text-muted-foreground">
                                    Connect a Stripe account to start accepting
                                    payments for memberships.
                                </p>
                                <Button size="sm" asChild>
                                    <Link
                                        href={
                                            team.stripe.onboard(
                                                currentTeam.slug,
                                            ).url
                                        }
                                    >
                                        Connect Stripe Account
                                    </Link>
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Recent Memberships</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recentMemberships.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No memberships yet.
                            </p>
                        ) : (
                            <div className="space-y-4">
                                {recentMemberships.map((membership) => (
                                    <div
                                        key={membership.id}
                                        className="flex items-center justify-between rounded-lg border p-3"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {membership.user?.name ?? 'Unknown'}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {membership.plan?.name ?? 'No plan'} &middot;{' '}
                                                {new Date(membership.starts_at).toLocaleDateString()}
                                            </p>
                                        </div>
                                        <Badge
                                            variant={
                                                membership.status === 'active'
                                                    ? 'default'
                                                    : membership.status === 'cancelled'
                                                      ? 'destructive'
                                                      : 'secondary'
                                            }
                                        >
                                            {membership.status}
                                        </Badge>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
