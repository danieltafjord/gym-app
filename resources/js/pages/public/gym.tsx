import { Head, Link } from '@inertiajs/react';
import { MapPin, Mail, Phone } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import PublicLayout from '@/layouts/public-layout';
import type { Gym, MembershipPlan, Team } from '@/types';
import publicRoutes from '@/routes/public';

interface Props {
    team: Team;
    gym: Gym;
    plans: MembershipPlan[];
    stripeReady: boolean;
}

export default function PublicGym({ team, gym, plans, stripeReady }: Props) {
    return (
        <PublicLayout>
            <Head title={`${gym.name} - ${team.name}`} />

            <div className="mb-2">
                <Link
                    href={`/${team.slug}`}
                    className="text-sm text-muted-foreground hover:text-foreground"
                >
                    {team.name}
                </Link>
            </div>

            <Heading title={gym.name} />

            <div className="mb-8 flex flex-wrap gap-4 text-sm text-muted-foreground">
                {gym.address && (
                    <span className="flex items-center gap-1">
                        <MapPin className="size-4" />
                        {gym.address}
                    </span>
                )}
                {gym.phone && (
                    <span className="flex items-center gap-1">
                        <Phone className="size-4" />
                        {gym.phone}
                    </span>
                )}
                {gym.email && (
                    <span className="flex items-center gap-1">
                        <Mail className="size-4" />
                        {gym.email}
                    </span>
                )}
            </div>

            {plans.length > 0 ? (
                <section>
                    <h3 className="mb-4 text-lg font-semibold tracking-tight">
                        Membership Plans
                    </h3>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {plans.map((plan) => (
                            <Card key={plan.id}>
                                <CardHeader>
                                    <CardTitle>{plan.name}</CardTitle>
                                    {plan.description && (
                                        <CardDescription>
                                            {plan.description}
                                        </CardDescription>
                                    )}
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-baseline gap-1">
                                        <span className="text-2xl font-bold">
                                            ${plan.price_formatted}
                                        </span>
                                        <span className="text-sm text-muted-foreground">
                                            /{plan.billing_period}
                                        </span>
                                    </div>

                                    {plan.features && plan.features.length > 0 && (
                                        <ul className="space-y-1 text-sm text-muted-foreground">
                                            {plan.features.map(
                                                (feature, index) => (
                                                    <li key={index}>
                                                        {feature}
                                                    </li>
                                                ),
                                            )}
                                        </ul>
                                    )}

                                    {stripeReady && (
                                        <Button className="w-full" asChild>
                                            <Link
                                                href={publicRoutes.checkout(
                                                    {
                                                        team: team.slug,
                                                        gym: gym.slug,
                                                        membershipPlan: plan.id,
                                                    },
                                                ).url}
                                            >
                                                Sign Up
                                            </Link>
                                        </Button>
                                    )}
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </section>
            ) : (
                <Card className="py-12">
                    <CardContent className="text-center">
                        <p className="text-muted-foreground">
                            No membership plans available at the moment.
                        </p>
                    </CardContent>
                </Card>
            )}
        </PublicLayout>
    );
}
