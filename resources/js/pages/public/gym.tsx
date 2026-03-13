import { Head, Link } from '@inertiajs/react';
import { MapPin, Mail, Phone, Tag } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    hasYearlyPricingOption,
    resolveDisplayBillingPeriod,
    resolveDisplayPriceFormatted,
    resolveMonthlyDiscountLabel,
    resolvePlanAccessSummary,
    resolvePlanChargeLabel,
    resolveYearlySavingsMonths,
    resolveYearlyTogglePromoText,
} from '@/lib/membership-plans';
import PublicLayout from '@/layouts/public-layout';
import type { Gym, MembershipPlan, Team } from '@/types';
import publicRoutes from '@/routes/public';

interface Props {
    team: Team;
    gym: Gym;
    plans: MembershipPlan[];
    stripeReady: boolean;
    widgetDemoUrl: string;
}

export default function PublicGym({
    team,
    gym,
    plans,
    stripeReady,
    widgetDemoUrl,
}: Props) {
    const [preferredBillingPeriod, setPreferredBillingPeriod] = useState<
        'monthly' | 'yearly'
    >('monthly');
    const showBillingToggle = plans.some(hasYearlyPricingOption);
    const yearlyTogglePromoText = resolveYearlyTogglePromoText(plans);

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
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <h3 className="text-lg font-semibold tracking-tight">
                            Membership Plans
                        </h3>

                        <div className="flex items-center gap-2">
                            {showBillingToggle && (
                                <div className="inline-flex rounded-xl bg-primary/90 p-1 shadow-sm">
                                    <button
                                        type="button"
                                        className={`rounded-lg px-3 py-2 text-xs font-semibold transition ${
                                            preferredBillingPeriod === 'monthly'
                                                ? 'bg-white text-foreground shadow-sm'
                                                : 'text-primary-foreground/80 hover:text-primary-foreground'
                                        }`}
                                        onClick={() =>
                                            setPreferredBillingPeriod('monthly')
                                        }
                                    >
                                        Monthly
                                    </button>
                                    <button
                                        type="button"
                                        className={`inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold transition ${
                                            preferredBillingPeriod === 'yearly'
                                                ? 'bg-white text-foreground shadow-sm'
                                                : 'text-primary-foreground/80 hover:text-primary-foreground'
                                        }`}
                                        onClick={() =>
                                            setPreferredBillingPeriod('yearly')
                                        }
                                    >
                                        <span>Yearly</span>
                                        {yearlyTogglePromoText && (
                                            <span
                                                className={`inline-flex items-center gap-1 text-[11px] font-bold ${
                                                    preferredBillingPeriod ===
                                                    'yearly'
                                                        ? 'text-pink-600'
                                                        : 'text-primary-foreground'
                                                }`}
                                            >
                                                <Tag className="size-3" />
                                                {yearlyTogglePromoText}
                                            </span>
                                        )}
                                    </button>
                                </div>
                            )}

                            <Button variant="outline" asChild>
                                <Link href={widgetDemoUrl}>Widget Demo</Link>
                            </Button>
                        </div>
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {plans.map((plan) => {
                            const displayBillingPeriod =
                                resolveDisplayBillingPeriod(
                                    plan,
                                    preferredBillingPeriod,
                                );
                            const displayPriceFormatted =
                                resolveDisplayPriceFormatted(
                                    plan,
                                    displayBillingPeriod,
                                );
                            const discountLabel = resolveMonthlyDiscountLabel(
                                plan,
                                displayBillingPeriod,
                            );
                            const accessSummary =
                                resolvePlanAccessSummary(plan);

                            return (
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
                                        <div className="space-y-1">
                                            <div className="flex items-baseline gap-1">
                                                <span className="text-2xl font-bold">
                                                    ${displayPriceFormatted}
                                                </span>
                                                <span className="text-sm text-muted-foreground">
                                                    {resolvePlanChargeLabel(
                                                        plan,
                                                        displayBillingPeriod,
                                                    )}
                                                </span>
                                            </div>
                                            {discountLabel && (
                                                <p className="text-sm font-medium text-primary">
                                                    {discountLabel}
                                                </p>
                                            )}
                                            {accessSummary && (
                                                <p className="text-sm text-muted-foreground">
                                                    {accessSummary}
                                                </p>
                                            )}
                                            {plan.requires_account && (
                                                <p className="text-sm font-medium text-primary">
                                                    Requires account sign-in
                                                </p>
                                            )}
                                        </div>

                                        {plan.features &&
                                            plan.features.length > 0 && (
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
                                                    href={
                                                        publicRoutes.checkout(
                                                            {
                                                                team: team.slug,
                                                                gym: gym.slug,
                                                                membershipPlan:
                                                                    plan.id,
                                                            },
                                                            plan.plan_type ===
                                                                'recurring' &&
                                                                (displayBillingPeriod ===
                                                                    'monthly' ||
                                                                    displayBillingPeriod ===
                                                                        'yearly')
                                                                ? {
                                                                      query: {
                                                                          billing_period:
                                                                              displayBillingPeriod,
                                                                      },
                                                                  }
                                                                : undefined,
                                                        ).url
                                                    }
                                                >
                                                    {plan.requires_account
                                                        ? 'Sign In to Buy'
                                                        : 'Sign Up'}
                                                </Link>
                                            </Button>
                                        )}
                                    </CardContent>
                                </Card>
                            );
                        })}
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
