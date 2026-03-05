import { Head, Link, usePage } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import PublicLayout from '@/layouts/public-layout';
import type { Gym, Membership, MembershipPlan, Team } from '@/types';
import type { Auth } from '@/types/auth';
import publicRoutes from '@/routes/public';
import { register } from '@/routes';

interface Props {
    team: Team;
    gym: Gym;
    plan: MembershipPlan;
    membership: Membership;
    email: string;
    selectedBillingPeriod: 'weekly' | 'monthly' | 'quarterly' | 'yearly';
    selectedPriceFormatted: string;
}

export default function CheckoutSuccess({
    team,
    gym,
    plan,
    membership,
    email,
    selectedBillingPeriod,
    selectedPriceFormatted,
}: Props) {
    const { auth } = usePage<{ auth: { user: Auth['user'] | null } }>().props;

    return (
        <PublicLayout>
            <Head title="Payment Successful" />

            <div className="mx-auto max-w-lg">
                <Card>
                    <CardHeader className="text-center">
                        <CheckCircle2 className="mx-auto mb-2 size-12 text-green-500" />
                        <CardTitle>Payment Successful</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="rounded-lg border p-4">
                            <dl className="space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <dt className="text-muted-foreground">
                                        Plan
                                    </dt>
                                    <dd className="font-medium">
                                        {plan.name}
                                    </dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-muted-foreground">
                                        Amount
                                    </dt>
                                    <dd className="font-medium">
                                        ${selectedPriceFormatted}
                                        {plan.plan_type === 'recurring'
                                            ? `/${selectedBillingPeriod}`
                                            : ''}
                                    </dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-muted-foreground">
                                        Gym
                                    </dt>
                                    <dd className="font-medium">{gym.name}</dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-muted-foreground">
                                        Status
                                    </dt>
                                    <dd className="font-medium capitalize">
                                        {membership.status}
                                    </dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-muted-foreground">
                                        Starts
                                    </dt>
                                    <dd className="font-medium">
                                        {new Date(
                                            membership.starts_at,
                                        ).toLocaleDateString()}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div className="rounded-lg border border-primary/20 bg-primary/5 p-4 text-center">
                            <p className="mb-1 text-sm text-muted-foreground">
                                Your Access Code
                            </p>
                            <p className="font-mono text-2xl font-bold tracking-widest">
                                {membership.access_code}
                            </p>
                        </div>

                        <p className="text-center text-sm text-muted-foreground">
                            A confirmation email has been sent to{' '}
                            <span className="font-medium text-foreground">
                                {email}
                            </span>
                        </p>

                        <div className="flex gap-2">
                            {auth.user ? (
                                <Button className="flex-1" asChild>
                                    <Link href="/account">My Account</Link>
                                </Button>
                            ) : (
                                <Button className="flex-1" asChild>
                                    <Link href={register()}>
                                        Create an Account
                                    </Link>
                                </Button>
                            )}
                            <Button
                                variant="outline"
                                className="flex-1"
                                asChild
                            >
                                <Link
                                    href={
                                        publicRoutes.gym({
                                            team: team.slug,
                                            gym: gym.slug,
                                        }).url
                                    }
                                >
                                    Back to Gym
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </PublicLayout>
    );
}
