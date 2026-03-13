import type { MembershipPlan } from '@/types';

export function hasYearlyPricingOption(plan: MembershipPlan): boolean {
    return (
        plan.plan_type === 'recurring' &&
        plan.billing_period === 'monthly' &&
        plan.yearly_price_cents !== null &&
        plan.yearly_price_cents > 0
    );
}

export function formatCents(cents: number): string {
    return (cents / 100).toFixed(2);
}

export function resolveDisplayBillingPeriod(
    plan: MembershipPlan,
    preferredBillingPeriod: 'monthly' | 'yearly',
): MembershipPlan['billing_period'] {
    if (plan.plan_type !== 'recurring') {
        return plan.billing_period;
    }

    if (preferredBillingPeriod === 'yearly' && hasYearlyPricingOption(plan)) {
        return 'yearly';
    }

    return plan.billing_period;
}

export function resolveDisplayPriceFormatted(
    plan: MembershipPlan,
    displayBillingPeriod: MembershipPlan['billing_period'],
): string {
    if (displayBillingPeriod === 'yearly' && hasYearlyPricingOption(plan)) {
        return (
            plan.yearly_price_formatted ??
            formatCents(plan.yearly_price_cents ?? 0)
        );
    }

    return plan.price_formatted;
}

export function resolveMonthlyDiscountLabel(
    plan: MembershipPlan,
    displayBillingPeriod: MembershipPlan['billing_period'],
): string | null {
    if (displayBillingPeriod !== 'yearly' || !hasYearlyPricingOption(plan)) {
        return null;
    }

    const monthlyCents = plan.price_cents;
    const yearlyMonthlyEquivalentCents = Math.round(
        (plan.yearly_price_cents ?? 0) / 12,
    );
    const savingsCents = monthlyCents - yearlyMonthlyEquivalentCents;

    if (savingsCents <= 0) {
        return null;
    }

    return `Save $${formatCents(savingsCents)}/month with yearly billing`;
}

export function resolveYearlySavingsMonths(plan: MembershipPlan): number {
    if (!hasYearlyPricingOption(plan)) {
        return 0;
    }

    const monthlyCents = plan.price_cents;

    if (monthlyCents <= 0) {
        return 0;
    }

    const yearlyCents = plan.yearly_price_cents ?? 0;
    const totalSavingsCents = monthlyCents * 12 - yearlyCents;

    if (totalSavingsCents <= 0) {
        return 0;
    }

    return totalSavingsCents / monthlyCents;
}

export function resolveYearlyTogglePromoText(
    plans: MembershipPlan[],
    configuredPromoText?: string | null,
): string | null {
    const trimmedPromoText = (configuredPromoText ?? '').trim();

    if (trimmedPromoText !== '') {
        return trimmedPromoText;
    }

    const bestMonthsFree = plans.reduce((maxMonths, plan) => {
        return Math.max(maxMonths, resolveYearlySavingsMonths(plan));
    }, 0);

    if (bestMonthsFree >= 0.95) {
        const roundedMonths = Math.max(1, Math.round(bestMonthsFree));

        return `Get ${roundedMonths} month${roundedMonths > 1 ? 's' : ''} free`;
    }

    return null;
}

export function resolvePlanChargeLabel(
    plan: MembershipPlan,
    displayBillingPeriod: MembershipPlan['billing_period'],
): string {
    if (plan.plan_type === 'one_time') {
        return plan.access_duration_label ?? 'One-time payment';
    }

    const labels: Record<MembershipPlan['billing_period'], string> = {
        weekly: '/week',
        monthly: '/month',
        quarterly: '/quarter',
        yearly: '/year',
    };

    return labels[displayBillingPeriod];
}

export function resolvePlanAccessSummary(plan: MembershipPlan): string | null {
    if (plan.plan_type !== 'one_time') {
        return null;
    }

    if (
        plan.activation_mode === 'first_check_in' &&
        plan.access_duration_label
    ) {
        return `Activates on first check-in and lasts ${plan.access_duration_label}.`;
    }

    if (plan.access_duration_label) {
        return `Starts at purchase and lasts ${plan.access_duration_label}.`;
    }

    if (plan.max_entries !== null) {
        return `${plan.max_entries} ${plan.max_entries === 1 ? 'entry' : 'entries'}.`;
    }

    return 'One-time access.';
}
