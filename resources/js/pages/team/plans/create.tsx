import { Form, Head } from '@inertiajs/react';
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

export default function CreatePlan({ team: currentTeam }: { team: Team }) {
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
            title: 'Create',
            href: team.plans.create(currentTeam.slug).url,
        },
    ];

    const priceCurrencyCode = currentTeam.default_currency ?? 'USD';
    const [planType, setPlanType] =
        useState<MembershipPlan['plan_type']>('recurring');
    const [billingPeriod, setBillingPeriod] =
        useState<MembershipPlan['billing_period']>('monthly');
    const [activationMode, setActivationMode] =
        useState<MembershipPlan['activation_mode']>('purchase');
    const [accessDurationUnit, setAccessDurationUnit] =
        useState<NonNullable<MembershipPlan['access_duration_unit']>>('hour');
    const [accessCodeStrategy, setAccessCodeStrategy] =
        useState<MembershipPlan['access_code_strategy']>('rotate_on_check_in');
    const [requiresAccount, setRequiresAccount] = useState(false);

    const showYearlyPricing =
        planType === 'recurring' && billingPeriod === 'monthly';
    const isOneTimePlan = planType === 'one_time';

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${currentTeam.name} - Create Plan`} />

            <div className="mx-auto w-full max-w-2xl space-y-6 p-4">
                <Heading
                    title="Create Plan"
                    description="Set up a recurring membership or a one-time access product."
                />

                <Form
                    {...MembershipPlanController.store.form(currentTeam.slug)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
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

                                        if (nextPlanType === 'one_time') {
                                            setAccessCodeStrategy('static');
                                        } else {
                                            setActivationMode('purchase');
                                            setAccessCodeStrategy(
                                                'rotate_on_check_in',
                                            );
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
                                            defaultValue="24"
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
                                    Create Plan
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
