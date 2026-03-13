import { Form, Head, router, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, MembershipPlan, Team } from '@/types';
import MembershipPlanController from '@/actions/App/Http/Controllers/Team/MembershipPlanController';
import team from '@/routes/team';
import { useState } from 'react';

export default function EditPlan({
    team: currentTeam,
    plan,
}: {
    team: Team;
    plan: MembershipPlan;
}) {
    const { errors: pageErrors } = usePage<{ errors: Record<string, string> }>()
        .props;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Plans',
            href: team.plans.index(currentTeam.slug).url,
        },
        {
            title: plan.name,
            href: team.plans.edit({
                team: currentTeam.slug,
                plan: plan.id,
            }).url,
        },
    ];

    const monthlyPriceDollars = (plan.price_cents / 100).toFixed(2);
    const yearlyPriceDollars =
        plan.yearly_price_cents === null
            ? ''
            : (plan.yearly_price_cents / 100).toFixed(2);
    const priceCurrencyCode = currentTeam.default_currency ?? 'USD';
    const [planType, setPlanType] = useState<MembershipPlan['plan_type']>(
        plan.plan_type,
    );
    const [billingPeriod, setBillingPeriod] = useState<
        MembershipPlan['billing_period']
    >(plan.billing_period);
    const [activationMode, setActivationMode] = useState<
        MembershipPlan['activation_mode']
    >(plan.activation_mode);
    const [accessDurationUnit, setAccessDurationUnit] = useState<
        NonNullable<MembershipPlan['access_duration_unit']>
    >(plan.access_duration_unit ?? 'hour');
    const [accessCodeStrategy, setAccessCodeStrategy] = useState<
        MembershipPlan['access_code_strategy']
    >(plan.access_code_strategy);
    const [requiresAccount, setRequiresAccount] = useState(
        plan.requires_account,
    );

    const showYearlyPricing =
        planType === 'recurring' && billingPeriod === 'monthly';
    const isOneTimePlan = planType === 'one_time';

    const handleDeletePlan = () => {
        if (!confirm(`Delete "${plan.name}"? This action cannot be undone.`)) {
            return;
        }

        router.delete(
            MembershipPlanController.destroy.url({
                team: currentTeam.slug,
                plan: plan.id,
            }),
            { preserveScroll: true },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${plan.name}`} />

            <div className="mx-auto w-full max-w-2xl space-y-6 p-4">
                <Heading
                    title="Edit Plan"
                    description={`Update details for ${plan.name}.`}
                />

                <Form
                    {...MembershipPlanController.update.form.patch({
                        team: currentTeam.slug,
                        plan: plan.id,
                    })}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
                >
                    {({ processing, errors, recentlySuccessful }) => (
                        <>
                            <input type="hidden" name="plan_type" value={planType} />
                            <input
                                type="hidden"
                                name="billing_period"
                                value={
                                    planType === 'recurring'
                                        ? billingPeriod
                                        : 'monthly'
                                }
                            />
                            <input
                                type="hidden"
                                name="access_duration_unit"
                                value={isOneTimePlan ? accessDurationUnit : ''}
                            />
                            <input
                                type="hidden"
                                name="activation_mode"
                                value={
                                    isOneTimePlan
                                        ? activationMode
                                        : 'purchase'
                                }
                            />
                            <input
                                type="hidden"
                                name="access_code_strategy"
                                value={accessCodeStrategy}
                            />

                            <div className="grid gap-2">
                                <Label htmlFor="plan_type">Product Type</Label>
                                <Select
                                    value={planType}
                                    onValueChange={(value) => {
                                        const nextPlanType =
                                            value as MembershipPlan['plan_type'];
                                        setPlanType(nextPlanType);

                                        if (
                                            nextPlanType === 'one_time' &&
                                            accessCodeStrategy ===
                                                'rotate_on_check_in'
                                        ) {
                                            setAccessCodeStrategy('static');
                                        }

                                        if (nextPlanType === 'recurring') {
                                            setActivationMode('purchase');
                                        }
                                    }}
                                >
                                    <SelectTrigger id="plan_type">
                                        <SelectValue placeholder="Select product type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="recurring">
                                            Recurring membership
                                        </SelectItem>
                                        <SelectItem value="one_time">
                                            One-time pass
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.plan_type} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="name">Plan Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={plan.name}
                                    required
                                    placeholder="e.g. Unlimited, 24-Hour Pass, Weekend Pass"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">
                                    Description{' '}
                                    <span className="text-muted-foreground">
                                        (optional)
                                    </span>
                                </Label>
                                <Textarea
                                    id="description"
                                    name="description"
                                    defaultValue={plan.description ?? ''}
                                    placeholder="Describe what's included in this product"
                                    rows={3}
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="monthly_price_dollars">
                                        Price ({priceCurrencyCode})
                                    </Label>
                                    <Input
                                        id="monthly_price_dollars"
                                        name="price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        defaultValue={monthlyPriceDollars}
                                        placeholder="0.00"
                                    />
                                    <InputError message={errors.price_cents} />
                                </div>

                                {planType === 'recurring' && (
                                    <div className="grid gap-2">
                                        <Label htmlFor="billing_period">
                                            Billing Period
                                        </Label>
                                        <Select
                                            value={billingPeriod}
                                            onValueChange={(value) =>
                                                setBillingPeriod(
                                                    value as MembershipPlan['billing_period'],
                                                )
                                            }
                                        >
                                            <SelectTrigger id="billing_period">
                                                <SelectValue placeholder="Select billing period" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="weekly">
                                                    Weekly
                                                </SelectItem>
                                                <SelectItem value="monthly">
                                                    Monthly
                                                </SelectItem>
                                                <SelectItem value="quarterly">
                                                    Quarterly
                                                </SelectItem>
                                                <SelectItem value="yearly">
                                                    Yearly
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            message={errors.billing_period}
                                        />
                                    </div>
                                )}
                            </div>

                            {showYearlyPricing ? (
                                <div className="grid gap-2">
                                    <Label htmlFor="yearly_price_dollars">
                                        Yearly Price ({priceCurrencyCode}){' '}
                                        <span className="text-muted-foreground">
                                            (optional)
                                        </span>
                                    </Label>
                                    <Input
                                        id="yearly_price_dollars"
                                        name="yearly_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        defaultValue={yearlyPriceDollars}
                                        placeholder="0.00"
                                    />
                                    <InputError
                                        message={errors.yearly_price_cents}
                                    />
                                </div>
                            ) : null}

                            {isOneTimePlan && (
                                <div className="grid gap-4 sm:grid-cols-[minmax(0,1fr)_220px]">
                                    <div className="grid gap-2">
                                        <Label htmlFor="access_duration_value">
                                            Access Duration
                                        </Label>
                                        <Input
                                            id="access_duration_value"
                                            name="access_duration_value"
                                            type="number"
                                            min="1"
                                            step="1"
                                            defaultValue={
                                                plan.access_duration_value ?? 24
                                            }
                                        />
                                        <InputError
                                            message={
                                                errors.access_duration_value
                                            }
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="access_duration_unit">
                                            Duration Unit
                                        </Label>
                                        <Select
                                            value={accessDurationUnit}
                                            onValueChange={(value) =>
                                                setAccessDurationUnit(
                                                    value as NonNullable<
                                                        MembershipPlan['access_duration_unit']
                                                    >,
                                                )
                                            }
                                        >
                                            <SelectTrigger id="access_duration_unit">
                                                <SelectValue placeholder="Select duration unit" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="hour">
                                                    Hours
                                                </SelectItem>
                                                <SelectItem value="day">
                                                    Days
                                                </SelectItem>
                                                <SelectItem value="week">
                                                    Weeks
                                                </SelectItem>
                                                <SelectItem value="month">
                                                    Months
                                                </SelectItem>
                                                <SelectItem value="year">
                                                    Years
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            message={
                                                errors.access_duration_unit
                                            }
                                        />
                                    </div>
                                </div>
                            )}

                            {!isOneTimePlan && (
                                <>
                                    <input
                                        type="hidden"
                                        name="access_duration_value"
                                        value=""
                                    />
                                    <input
                                        type="hidden"
                                        name="max_entries"
                                        value=""
                                    />
                                </>
                            )}

                            {isOneTimePlan && (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="activation_mode">
                                            Activation
                                        </Label>
                                        <Select
                                            value={activationMode}
                                            onValueChange={(value) =>
                                                setActivationMode(
                                                    value as MembershipPlan['activation_mode'],
                                                )
                                            }
                                        >
                                            <SelectTrigger id="activation_mode">
                                                <SelectValue placeholder="Select activation timing" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="purchase">
                                                    Start at purchase
                                                </SelectItem>
                                                <SelectItem value="first_check_in">
                                                    Start on first check-in
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError
                                            message={errors.activation_mode}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="max_entries">
                                            Entry Limit{' '}
                                            <span className="text-muted-foreground">
                                                (optional)
                                            </span>
                                        </Label>
                                        <Input
                                            id="max_entries"
                                            name="max_entries"
                                            type="number"
                                            min="1"
                                            step="1"
                                            defaultValue={
                                                plan.max_entries ?? ''
                                            }
                                            placeholder="Leave empty for unlimited check-ins during the access window"
                                        />
                                        <InputError
                                            message={errors.max_entries}
                                        />
                                    </div>
                                </>
                            )}

                            <div className="grid gap-2">
                                <Label htmlFor="access_code_strategy">
                                    Access Code Behavior
                                </Label>
                                <Select
                                    value={accessCodeStrategy}
                                    onValueChange={(value) =>
                                        setAccessCodeStrategy(
                                            value as MembershipPlan['access_code_strategy'],
                                        )
                                    }
                                >
                                    <SelectTrigger id="access_code_strategy">
                                        <SelectValue placeholder="Select access code behavior" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="static">
                                            Keep the same code
                                        </SelectItem>
                                        <SelectItem value="rotate_on_check_in">
                                            Rotate after each check-in
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    message={errors.access_code_strategy}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="features">
                                    Features{' '}
                                    <span className="text-muted-foreground">
                                        (comma-separated)
                                    </span>
                                </Label>
                                <Input
                                    id="features"
                                    name="features"
                                    defaultValue={
                                        plan.features?.join(', ') ?? ''
                                    }
                                    placeholder="e.g. Unlimited access, Sauna, Valid at all locations"
                                />
                                <InputError message={errors.features} />
                            </div>

                            <div className="flex items-start gap-3 rounded-lg border p-4">
                                <input
                                    type="hidden"
                                    name="requires_account"
                                    value={requiresAccount ? '1' : '0'}
                                />
                                <Checkbox
                                    id="requires_account"
                                    checked={requiresAccount}
                                    onCheckedChange={(checked) =>
                                        setRequiresAccount(checked === true)
                                    }
                                />
                                <div className="space-y-1">
                                    <Label htmlFor="requires_account">
                                        Require account before checkout
                                    </Label>
                                    <p className="text-sm text-muted-foreground">
                                        Recommended for day passes and other
                                        one-time products that should always be
                                        tied to a customer account.
                                    </p>
                                    <InputError
                                        message={errors.requires_account}
                                    />
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>
                                    Save Changes
                                </Button>
                                {recentlySuccessful && (
                                    <p className="text-sm text-muted-foreground">
                                        Saved.
                                    </p>
                                )}
                            </div>
                        </>
                    )}
                </Form>

                <div className="flex items-center justify-between rounded-lg border border-destructive/20 bg-destructive/5 p-4">
                    <div>
                        <p className="font-medium">Delete Plan</p>
                        <p className="text-sm text-muted-foreground">
                            This will permanently remove this plan.
                        </p>
                        <InputError message={pageErrors.delete_plan} />
                    </div>
                    <Button variant="destructive" onClick={handleDeletePlan}>
                        Delete
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
