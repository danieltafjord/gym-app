import { Head, usePage } from '@inertiajs/react';
import { loadStripe, type Stripe } from '@stripe/stripe-js';
import {
    Elements,
    PaymentElement,
    useStripe,
    useElements,
} from '@stripe/react-stripe-js';
import { useCallback, useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/layouts/public-layout';
import type { Gym, MembershipPlan, Team } from '@/types';
import type { Auth } from '@/types/auth';
import publicRoutes from '@/routes/public';
import { success as checkoutSuccess } from '@/routes/public/checkout';

interface Props {
    team: Team;
    gym: Gym;
    plan: MembershipPlan;
    stripeKey: string;
    stripeDevMode: boolean;
}

function PlanSummary({ plan }: { plan: MembershipPlan }) {
    return (
        <div className="rounded-lg border p-4">
            <div className="flex items-baseline justify-between">
                <div>
                    <p className="font-semibold">{plan.name}</p>
                    {plan.description && (
                        <p className="text-sm text-muted-foreground">
                            {plan.description}
                        </p>
                    )}
                </div>
                <div className="text-right">
                    <span className="text-2xl font-bold">
                        ${plan.price_formatted}
                    </span>
                    <span className="text-sm text-muted-foreground">
                        {plan.plan_type === 'recurring'
                            ? `/${plan.billing_period}`
                            : ' one-time'}
                    </span>
                </div>
            </div>
        </div>
    );
}

function PaymentForm({
    team,
    gym,
    plan,
    subscriptionId,
    paymentIntentId,
}: {
    team: Team;
    gym: Gym;
    plan: MembershipPlan;
    subscriptionId: string | null;
    paymentIntentId: string | null;
}) {
    const stripe = useStripe();
    const elements = useElements();
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();

        if (!stripe || !elements) {
            return;
        }

        setProcessing(true);
        setError(null);

        const successUrl = new URL(
            checkoutSuccess({
                team: team.slug,
                gym: gym.slug,
            }).url,
            window.location.origin,
        );

        if (subscriptionId) {
            successUrl.searchParams.set('subscription_id', subscriptionId);
        }
        if (paymentIntentId) {
            successUrl.searchParams.set('payment_intent', paymentIntentId);
        }

        const { error: stripeError } = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: successUrl.toString(),
            },
        });

        if (stripeError) {
            setError(stripeError.message ?? 'Payment failed');
            setProcessing(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <PlanSummary plan={plan} />
            <PaymentElement />

            {error && (
                <div className="rounded-lg border border-destructive bg-destructive/10 p-3 text-sm text-destructive">
                    {error}
                </div>
            )}

            <Button
                type="submit"
                className="w-full"
                disabled={processing || !stripe || !elements}
            >
                {processing ? 'Processing...' : 'Pay Now'}
            </Button>
        </form>
    );
}

function DevModePayment({
    team,
    gym,
    plan,
    subscriptionId,
    paymentIntentId,
    membershipPlanId,
}: {
    team: Team;
    gym: Gym;
    plan: MembershipPlan;
    subscriptionId: string | null;
    paymentIntentId: string | null;
    membershipPlanId: number | null;
}) {
    const [redirecting, setRedirecting] = useState(false);

    const handleSimulatePayment = () => {
        setRedirecting(true);

        const successUrl = new URL(
            checkoutSuccess({
                team: team.slug,
                gym: gym.slug,
            }).url,
            window.location.origin,
        );

        if (subscriptionId) {
            successUrl.searchParams.set('subscription_id', subscriptionId);
        }
        if (paymentIntentId) {
            successUrl.searchParams.set('payment_intent', paymentIntentId);
        }
        if (membershipPlanId) {
            successUrl.searchParams.set(
                'membership_plan',
                String(membershipPlanId),
            );
        }

        window.location.href = successUrl.toString();
    };

    return (
        <div className="space-y-4">
            <PlanSummary plan={plan} />

            <div className="rounded-lg border border-dashed border-amber-500 bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-950/20 dark:text-amber-200">
                <p className="font-medium">Stripe Dev Mode</p>
                <p className="mt-1">
                    Payment processing is simulated. No real charge will be
                    made.
                </p>
            </div>

            <Button
                className="w-full"
                onClick={handleSimulatePayment}
                disabled={redirecting}
            >
                {redirecting ? 'Redirecting...' : 'Simulate Payment'}
            </Button>
        </div>
    );
}

export default function Checkout({
    team,
    gym,
    plan,
    stripeKey,
    stripeDevMode,
}: Props) {
    const { auth } = usePage<{ auth: { user: Auth['user'] | null } }>().props;
    const [clientSecret, setClientSecret] = useState<string | null>(null);
    const [subscriptionId, setSubscriptionId] = useState<string | null>(null);
    const [paymentIntentId, setPaymentIntentId] = useState<string | null>(null);
    const [isDevMode, setIsDevMode] = useState(false);
    const [membershipPlanId, setMembershipPlanId] = useState<number | null>(
        null,
    );
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const [name, setName] = useState(auth.user?.name ?? '');
    const [email, setEmail] = useState(auth.user?.email ?? '');
    const [phone, setPhone] = useState('');

    const stripePromise = useMemo(
        () => (stripeKey ? loadStripe(stripeKey) : null),
        [stripeKey],
    );

    const createIntent = useCallback(async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetch(
                publicRoutes.checkout.intent({
                    team: team.slug,
                    gym: gym.slug,
                    membershipPlan: plan.id,
                }).url,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': decodeURIComponent(
                            document.cookie
                                .split('; ')
                                .find((row) =>
                                    row.startsWith('XSRF-TOKEN='),
                                )
                                ?.split('=')[1] ?? '',
                        ),
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ name, email, phone: phone || null }),
                },
            );

            if (!response.ok) {
                const data = await response.json().catch(() => null);
                if (data?.errors) {
                    const messages = Object.values(data.errors).flat();
                    throw new Error(messages.join(', '));
                }
                throw new Error('Failed to create payment intent');
            }

            const data = await response.json();
            setClientSecret(data.clientSecret);
            setSubscriptionId(data.subscriptionId ?? null);
            setPaymentIntentId(data.paymentIntentId ?? null);
            setIsDevMode(data.devMode ?? false);
            setMembershipPlanId(data.membershipPlanId ?? null);
        } catch (e) {
            setError(
                e instanceof Error ? e.message : 'Something went wrong',
            );
        } finally {
            setLoading(false);
        }
    }, [team, gym, plan, name, email, phone]);

    const isContactValid = name.trim() !== '' && email.trim() !== '';

    const showPaymentStep = clientSecret || isDevMode;

    return (
        <PublicLayout>
            <Head title={`Checkout - ${plan.name}`} />

            <div className="mx-auto max-w-lg">
                <Card>
                    <CardHeader>
                        <CardTitle>Checkout</CardTitle>
                        <CardDescription>
                            {gym.name} &middot; {team.name}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {showPaymentStep ? (
                            isDevMode ? (
                                <DevModePayment
                                    team={team}
                                    gym={gym}
                                    plan={plan}
                                    subscriptionId={subscriptionId}
                                    paymentIntentId={paymentIntentId}
                                    membershipPlanId={membershipPlanId}
                                />
                            ) : (
                                <Elements
                                    stripe={stripePromise}
                                    options={{ clientSecret: clientSecret! }}
                                >
                                    <PaymentForm
                                        team={team}
                                        gym={gym}
                                        plan={plan}
                                        subscriptionId={subscriptionId}
                                        paymentIntentId={paymentIntentId}
                                    />
                                </Elements>
                            )
                        ) : (
                            <div className="space-y-4">
                                <PlanSummary plan={plan} />

                                {stripeDevMode && (
                                    <div className="rounded-lg border border-dashed border-amber-500 bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-950/20 dark:text-amber-200">
                                        Stripe Dev Mode active
                                    </div>
                                )}

                                <div className="space-y-3">
                                    <div className="space-y-1.5">
                                        <Label htmlFor="name">Name</Label>
                                        <Input
                                            id="name"
                                            value={name}
                                            onChange={(e) =>
                                                setName(e.target.value)
                                            }
                                            placeholder="Your full name"
                                            required
                                        />
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="email">Email</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={email}
                                            onChange={(e) =>
                                                setEmail(e.target.value)
                                            }
                                            placeholder="your@email.com"
                                            required
                                            disabled={!!auth.user}
                                        />
                                    </div>
                                    <div className="space-y-1.5">
                                        <Label htmlFor="phone">
                                            Phone{' '}
                                            <span className="text-muted-foreground">
                                                (optional)
                                            </span>
                                        </Label>
                                        <Input
                                            id="phone"
                                            type="tel"
                                            value={phone}
                                            onChange={(e) =>
                                                setPhone(e.target.value)
                                            }
                                            placeholder="Your phone number"
                                        />
                                    </div>
                                </div>

                                {error && (
                                    <div className="rounded-lg border border-destructive bg-destructive/10 p-3 text-sm text-destructive">
                                        {error}
                                    </div>
                                )}

                                <Button
                                    className="w-full"
                                    onClick={createIntent}
                                    disabled={loading || !isContactValid}
                                >
                                    {loading
                                        ? 'Preparing...'
                                        : 'Continue to Payment'}
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </PublicLayout>
    );
}
