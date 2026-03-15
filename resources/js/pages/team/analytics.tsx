import { Deferred, Head } from '@inertiajs/react';
import {
    Activity,
    DollarSign,
    TrendingDown,
    UserPlus,
    Users,
} from 'lucide-react';
import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type {
    AnalyticsStats,
    BreadcrumbItem,
    ChartDataPoint,
    Membership,
    Team,
} from '@/types';
import team from '@/routes/team';

function ChartSkeleton() {
    return (
        <div className="flex h-[200px] items-center justify-center">
            <div className="h-full w-full animate-pulse rounded bg-muted" />
        </div>
    );
}

function MemberGrowthChart({
    memberGrowth,
}: {
    memberGrowth: ChartDataPoint[];
}) {
    return (
        <ResponsiveContainer width="100%" height={200}>
            <AreaChart data={memberGrowth}>
                <CartesianGrid strokeDasharray="3 3" stroke="var(--border)" />
                <XAxis dataKey="label" tick={{ fontSize: 12, fill: 'var(--muted-foreground)' }} stroke="var(--border)" />
                <YAxis tick={{ fontSize: 12, fill: 'var(--muted-foreground)' }} stroke="var(--border)" />
                <Tooltip contentStyle={{ backgroundColor: 'var(--popover)', border: '1px solid var(--border)', color: 'var(--popover-foreground)' }} />
                <Area
                    type="monotone"
                    dataKey="value"
                    stroke="var(--primary)"
                    fill="var(--primary)"
                    fillOpacity={0.1}
                    name="Members"
                />
            </AreaChart>
        </ResponsiveContainer>
    );
}

function CheckInsDailyChart({
    checkInsDaily,
}: {
    checkInsDaily: ChartDataPoint[];
}) {
    return (
        <ResponsiveContainer width="100%" height={200}>
            <BarChart data={checkInsDaily}>
                <CartesianGrid strokeDasharray="3 3" stroke="var(--border)" />
                <XAxis dataKey="label" tick={{ fontSize: 12, fill: 'var(--muted-foreground)' }} stroke="var(--border)" />
                <YAxis tick={{ fontSize: 12, fill: 'var(--muted-foreground)' }} stroke="var(--border)" />
                <Tooltip contentStyle={{ backgroundColor: 'var(--popover)', border: '1px solid var(--border)', color: 'var(--popover-foreground)' }} />
                <Bar
                    dataKey="value"
                    fill="var(--primary)"
                    radius={[4, 4, 0, 0]}
                    name="Check-ins"
                />
            </BarChart>
        </ResponsiveContainer>
    );
}

export default function Analytics({
    team: currentTeam,
    stats,
    recentMemberships,
    memberGrowth,
    checkInsDaily,
}: {
    team: Team;
    stats: AnalyticsStats;
    recentMemberships: Membership[];
    memberGrowth?: ChartDataPoint[];
    checkInsDaily?: ChartDataPoint[];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Analytics',
            href: team.analytics(currentTeam.slug).url,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${currentTeam.name} - Analytics`} />

            <div className="space-y-6 p-4">
                <Heading
                    title="Analytics"
                    description="Trends, growth metrics, and membership activity."
                />

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Active Members
                            </CardTitle>
                            <Users className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.active_members}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                MRR
                            </CardTitle>
                            <DollarSign className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                ${stats.mrr.toLocaleString()}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Check-ins Today
                            </CardTitle>
                            <Activity className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.check_ins_today}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                New This Month
                            </CardTitle>
                            <UserPlus className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.new_members_this_month}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Churn Rate
                            </CardTitle>
                            <TrendingDown className="size-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {stats.churn_rate}%
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Member Growth (12 months)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Deferred
                                data="memberGrowth"
                                fallback={<ChartSkeleton />}
                            >
                                <MemberGrowthChart
                                    memberGrowth={memberGrowth ?? []}
                                />
                            </Deferred>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Daily Check-ins (30 days)</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Deferred
                                data="checkInsDaily"
                                fallback={<ChartSkeleton />}
                            >
                                <CheckInsDailyChart
                                    checkInsDaily={checkInsDaily ?? []}
                                />
                            </Deferred>
                        </CardContent>
                    </Card>
                </div>

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
                                                {membership.user?.name ??
                                                    'Unknown'}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {membership.plan?.name ??
                                                    'No plan'}{' '}
                                                &middot;{' '}
                                                {membership.starts_at
                                                    ? new Date(
                                                          membership.starts_at,
                                                      ).toLocaleDateString()
                                                    : 'Not activated'}
                                            </p>
                                        </div>
                                        <Badge
                                            variant={
                                                membership.status === 'active'
                                                    ? 'default'
                                                    : membership.status ===
                                                        'cancelled'
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
