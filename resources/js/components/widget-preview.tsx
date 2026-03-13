import { useState } from 'react';
import {
    resolvePlanAccessSummary,
    resolvePlanChargeLabel,
} from '@/lib/membership-plans';
import type { MembershipPlan, WidgetSettings } from '@/types';

export type PreviewView = 'plans' | 'checkout' | 'success';

function formatBillingPeriod(period: string): string {
    const labels: Record<string, string> = {
        weekly: 'per week',
        monthly: 'per month',
        quarterly: 'per quarter',
        yearly: 'per year',
    };
    return labels[period] || '';
}

function hasYearlyPricingOption(plan: MembershipPlan): boolean {
    return (
        plan.plan_type === 'recurring' &&
        plan.billing_period === 'monthly' &&
        plan.yearly_price_cents !== null &&
        plan.yearly_price_cents > 0
    );
}

function formatCents(cents: number): string {
    return (cents / 100).toFixed(2);
}

function resolveDisplayBillingPeriod(
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

function resolveDisplayPriceFormatted(
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

function resolveMonthlyDiscountLabel(
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

function resolveYearlySavingsMonths(plan: MembershipPlan): number {
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

function resolveYearlyTogglePromoText(
    plans: MembershipPlan[],
    configuredPromoText: string,
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

function hexToRgba(hex: string, alpha: number): string {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r},${g},${b},${alpha})`;
}

function PromoTagIcon() {
    return (
        <svg
            width="12"
            height="12"
            viewBox="0 0 12 12"
            fill="none"
            aria-hidden="true"
            style={{ flexShrink: 0 }}
        >
            <path
                d="M6.25 1.5H10.5V5.75L5.75 10.5L1.5 6.25L6.25 1.5Z"
                stroke="currentColor"
                strokeWidth="1.2"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
            <circle cx="8.5" cy="3.5" r="0.6" fill="currentColor" />
        </svg>
    );
}

function BillingToggle({
    settings,
    preferredBillingPeriod,
    onChange,
    yearlyPromoText,
    justifyContent = 'center',
    marginBottom = '0',
}: {
    settings: WidgetSettings;
    preferredBillingPeriod: 'monthly' | 'yearly';
    onChange: (period: 'monthly' | 'yearly') => void;
    yearlyPromoText?: string | null;
    justifyContent?: React.CSSProperties['justifyContent'];
    marginBottom?: string;
}) {
    return (
        <div
            style={{
                marginBottom,
                display: 'flex',
                justifyContent,
            }}
        >
            <div
                style={{
                    display: 'inline-flex',
                    alignItems: 'center',
                    gap: '4px',
                    padding: '4px',
                    borderRadius: '12px',
                    background: hexToRgba(settings.primary_color, 0.92),
                    boxShadow: '0 1px 2px rgba(15, 23, 42, 0.08)',
                }}
            >
                {(['monthly', 'yearly'] as const).map((period) => {
                    const isActive = preferredBillingPeriod === period;

                    return (
                        <button
                            key={period}
                            type="button"
                            aria-pressed={isActive}
                            onClick={() => onChange(period)}
                            style={{
                                display: 'inline-flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                gap: '8px',
                                flexWrap:
                                    period === 'yearly' ? 'wrap' : 'nowrap',
                                border: 'none',
                                cursor: 'pointer',
                                fontFamily: 'inherit',
                                fontSize: '0.75rem',
                                fontWeight: 600,
                                lineHeight: 1,
                                borderRadius: '8px',
                                padding: '10px 14px',
                                color: isActive
                                    ? settings.text_color
                                    : hexToRgba(
                                          settings.button_text_color,
                                          0.82,
                                      ),
                                background: isActive
                                    ? '#ffffff'
                                    : 'transparent',
                                boxShadow: isActive
                                    ? '0 1px 3px rgba(15, 23, 42, 0.1)'
                                    : 'none',
                                textAlign: 'center',
                            }}
                        >
                            <span>
                                {period === 'monthly' ? 'Monthly' : 'Yearly'}
                            </span>
                            {period === 'yearly' && yearlyPromoText && (
                                <span
                                    style={{
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        gap: '4px',
                                        fontSize: '0.6875rem',
                                        fontWeight: 700,
                                        lineHeight: 1,
                                        color: isActive
                                            ? '#db2777'
                                            : hexToRgba(
                                                  settings.button_text_color,
                                                  0.95,
                                              ),
                                    }}
                                >
                                    <PromoTagIcon />
                                    {yearlyPromoText}
                                </span>
                            )}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}

function CheckIcon({ color }: { color: string }) {
    return (
        <svg
            width="16"
            height="16"
            viewBox="0 0 16 16"
            fill="none"
            style={{ flexShrink: 0 }}
        >
            <path
                d="M12 5L6.5 10.5L4 8"
                stroke={color}
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function SuccessCheckIcon({ color }: { color: string }) {
    return (
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
            <path
                d="M20 10L12 18L8 14"
                stroke={color}
                strokeWidth="2.5"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

export default function WidgetPreview({
    settings,
    plans,
    view = 'plans',
}: {
    settings: WidgetSettings;
    plans: MembershipPlan[];
    view?: PreviewView;
}) {
    return (
        <div
            style={{
                fontFamily: settings.font_family,
                background: settings.background_color,
                color: settings.text_color,
                padding: `${settings.padding}px`,
                WebkitFontSmoothing: 'antialiased',
                lineHeight: 1.5,
            }}
        >
            {view === 'plans' && (
                <PlansView settings={settings} plans={plans} />
            )}
            {view === 'checkout' && (
                <CheckoutView settings={settings} plans={plans} />
            )}
            {view === 'success' && (
                <SuccessView settings={settings} plans={plans} />
            )}
        </div>
    );
}

function PlansView({
    settings,
    plans,
}: {
    settings: WidgetSettings;
    plans: MembershipPlan[];
}) {
    const gridCols = Math.min(Math.max(settings.columns, 1), 4);
    const [preferredBillingPeriod, setPreferredBillingPeriod] = useState<
        'monthly' | 'yearly'
    >('monthly');
    const showBillingToggle = plans.some(hasYearlyPricingOption);
    const yearlyTogglePromoText = resolveYearlyTogglePromoText(
        plans,
        settings.yearly_toggle_promo_text,
    );

    if (plans.length === 0) {
        return (
            <p
                style={{
                    textAlign: 'center',
                    padding: '32px',
                    color: settings.secondary_text_color,
                    fontSize: '0.875rem',
                }}
            >
                No active plans to preview. Create membership plans to see the
                widget preview.
            </p>
        );
    }

    return (
        <>
            {showBillingToggle && (
                <BillingToggle
                    settings={settings}
                    preferredBillingPeriod={preferredBillingPeriod}
                    onChange={setPreferredBillingPeriod}
                    yearlyPromoText={yearlyTogglePromoText}
                    marginBottom="16px"
                />
            )}
            <div
                style={{
                    display: 'grid',
                    gridTemplateColumns: `repeat(${gridCols}, 1fr)`,
                    gap: '20px',
                }}
            >
                {plans.map((plan) => {
                    const displayBillingPeriod = resolveDisplayBillingPeriod(
                        plan,
                        preferredBillingPeriod,
                    );
                    const displayPriceFormatted = resolveDisplayPriceFormatted(
                        plan,
                        displayBillingPeriod,
                    );
                    const discountLabel = resolveMonthlyDiscountLabel(
                        plan,
                        displayBillingPeriod,
                    );
                    const accessSummary = resolvePlanAccessSummary(plan);

                    return (
                        <div
                            key={plan.id}
                            style={{
                                border: `1px solid ${settings.card_border_color}`,
                                borderRadius: `${settings.card_border_radius}px`,
                                padding: '28px',
                                display: 'flex',
                                flexDirection: 'column',
                                background: settings.background_color,
                                boxShadow:
                                    '0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.03)',
                            }}
                        >
                            <p
                                style={{
                                    fontSize: '1.125rem',
                                    fontWeight: 600,
                                    margin: '0 0 6px',
                                    color: settings.text_color,
                                    letterSpacing: '-0.01em',
                                }}
                            >
                                {plan.name}
                            </p>

                            {settings.show_description && plan.description && (
                                <p
                                    style={{
                                        fontSize: '0.8125rem',
                                        color: settings.secondary_text_color,
                                        margin: '0 0 20px',
                                        lineHeight: 1.6,
                                    }}
                                >
                                    {plan.description}
                                </p>
                            )}

                            <div style={{ marginBottom: '20px' }}>
                                <p
                                    style={{
                                        display: 'flex',
                                        alignItems: 'flex-start',
                                        fontSize: '2.5rem',
                                        fontWeight: 700,
                                        color: settings.text_color,
                                        margin: 0,
                                        letterSpacing: '-0.03em',
                                        lineHeight: 1,
                                    }}
                                >
                                    <span
                                        style={{
                                            fontSize: '1.125rem',
                                            fontWeight: 600,
                                            marginTop: '0.35em',
                                            marginRight: '2px',
                                            letterSpacing: 0,
                                        }}
                                    >
                                        $
                                    </span>
                                    {displayPriceFormatted}
                                </p>
                                <span
                                    style={{
                                        display: 'inline-block',
                                        fontSize: '0.6875rem',
                                        color: settings.secondary_text_color,
                                        margin: '8px 0 0',
                                        padding: '3px 10px',
                                        background: hexToRgba(
                                            settings.secondary_text_color,
                                            0.07,
                                        ),
                                        borderRadius: '100px',
                                        fontWeight: 500,
                                    }}
                                >
                                    {resolvePlanChargeLabel(
                                        plan,
                                        displayBillingPeriod,
                                    )}
                                </span>
                                {discountLabel && (
                                    <p
                                        style={{
                                            fontSize: '0.75rem',
                                            color: settings.primary_color,
                                            margin: '8px 0 0',
                                            fontWeight: 600,
                                        }}
                                    >
                                        {discountLabel}
                                    </p>
                                )}
                                {accessSummary && (
                                    <p
                                        style={{
                                            fontSize: '0.75rem',
                                            color: settings.secondary_text_color,
                                            margin: '8px 0 0',
                                            lineHeight: 1.5,
                                        }}
                                    >
                                        {accessSummary}
                                    </p>
                                )}
                                {plan.requires_account && (
                                    <p
                                        style={{
                                            fontSize: '0.75rem',
                                            color: settings.primary_color,
                                            margin: '8px 0 0',
                                            fontWeight: 600,
                                        }}
                                    >
                                        Requires account sign-in
                                    </p>
                                )}
                            </div>

                            {settings.show_features &&
                                plan.features &&
                                plan.features.length > 0 && (
                                    <>
                                        <div
                                            style={{
                                                height: '1px',
                                                background:
                                                    settings.card_border_color,
                                                margin: '0 0 20px',
                                            }}
                                        />
                                        <ul
                                            style={{
                                                listStyle: 'none',
                                                padding: 0,
                                                margin: '0 0 24px',
                                                flexGrow: 1,
                                            }}
                                        >
                                            {plan.features.map(
                                                (feature, index) => (
                                                    <li
                                                        key={index}
                                                        style={{
                                                            padding: '5px 0',
                                                            fontSize:
                                                                '0.8125rem',
                                                            color: settings.secondary_text_color,
                                                            display: 'flex',
                                                            alignItems:
                                                                'center',
                                                            gap: '10px',
                                                        }}
                                                    >
                                                        <CheckIcon
                                                            color={
                                                                settings.primary_color
                                                            }
                                                        />
                                                        {feature}
                                                    </li>
                                                ),
                                            )}
                                        </ul>
                                    </>
                                )}

                            <span
                                style={{
                                    display: 'inline-block',
                                    width: '100%',
                                    padding: '13px 24px',
                                    background: settings.primary_color,
                                    color: settings.button_text_color,
                                    textAlign: 'center',
                                    textDecoration: 'none',
                                    fontWeight: 600,
                                    fontSize: '0.875rem',
                                    borderRadius: `${settings.button_border_radius}px`,
                                    border: 'none',
                                    cursor: 'pointer',
                                    boxSizing: 'border-box',
                                    marginTop: 'auto',
                                    boxShadow: `0 1px 3px ${hexToRgba(settings.primary_color, 0.2)}`,
                                    letterSpacing: '0.01em',
                                    lineHeight: 1.5,
                                }}
                            >
                                {settings.button_text || 'Sign Up'}
                            </span>
                        </div>
                    );
                })}
            </div>
            <p
                style={{
                    textAlign: 'center',
                    marginTop: '24px',
                    paddingTop: '16px',
                    fontSize: '0.6875rem',
                    color: settings.secondary_text_color,
                    opacity: 0.6,
                    letterSpacing: '0.02em',
                }}
            >
                Powered by <span style={{ fontWeight: 500 }}>GymApp</span>
            </p>
        </>
    );
}

function CheckoutView({
    settings,
    plans,
}: {
    settings: WidgetSettings;
    plans: MembershipPlan[];
}) {
    const [preferredBillingPeriod, setPreferredBillingPeriod] = useState<
        'monthly' | 'yearly'
    >('monthly');
    const plan = plans[0];

    if (!plan) {
        return (
            <p
                style={{
                    textAlign: 'center',
                    padding: '32px',
                    color: settings.secondary_text_color,
                    fontSize: '0.875rem',
                }}
            >
                No plans available to preview checkout.
            </p>
        );
    }

    const supportsYearlyPricing = hasYearlyPricingOption(plan);
    const yearlyTogglePromoText = supportsYearlyPricing
        ? resolveYearlyTogglePromoText(
              [plan],
              settings.yearly_toggle_promo_text,
          )
        : null;
    const displayBillingPeriod = resolveDisplayBillingPeriod(
        plan,
        preferredBillingPeriod,
    );
    const displayPriceFormatted = resolveDisplayPriceFormatted(
        plan,
        displayBillingPeriod,
    );
    const discountLabel = resolveMonthlyDiscountLabel(
        plan,
        displayBillingPeriod,
    );
    const billingLabel = resolvePlanChargeLabel(plan, displayBillingPeriod);
    const accessSummary = resolvePlanAccessSummary(plan);

    const inputStyle: React.CSSProperties = {
        width: '100%',
        padding: '11px 14px',
        border: `1px solid ${settings.input_border_color}`,
        borderRadius: `${settings.input_border_radius}px`,
        fontSize: '0.875rem',
        fontFamily: 'inherit',
        color: settings.text_color,
        background: settings.input_background_color,
        outline: 'none',
        lineHeight: 1.5,
        boxSizing: 'border-box' as const,
    };

    const labelStyle: React.CSSProperties = {
        display: 'block',
        fontSize: '0.8125rem',
        fontWeight: 500,
        marginBottom: '6px',
        color: settings.text_color,
    };

    return (
        <div style={{ maxWidth: '440px', margin: '0 auto' }}>
            <div style={{ marginBottom: '24px' }}>
                <span
                    style={{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '6px',
                        fontSize: '0.8125rem',
                        color: settings.primary_color,
                        fontWeight: 500,
                        marginBottom: '4px',
                    }}
                >
                    &larr; Back to plans
                </span>
                <h2
                    style={{
                        fontSize: '1.25rem',
                        fontWeight: 600,
                        margin: '8px 0 0',
                        color: settings.text_color,
                        letterSpacing: '-0.01em',
                    }}
                >
                    Complete your signup
                </h2>
            </div>

            <div
                style={{
                    border: `1px solid ${settings.card_border_color}`,
                    borderRadius: `${settings.card_border_radius}px`,
                    padding: '16px 20px',
                    marginBottom: '28px',
                    background: hexToRgba(settings.primary_color, 0.03),
                }}
            >
                {supportsYearlyPricing && (
                    <BillingToggle
                        settings={settings}
                        preferredBillingPeriod={preferredBillingPeriod}
                        onChange={setPreferredBillingPeriod}
                        yearlyPromoText={yearlyTogglePromoText}
                        justifyContent="flex-start"
                        marginBottom="10px"
                    />
                )}
                <p
                    style={{
                        fontWeight: 600,
                        margin: '0 0 2px',
                        fontSize: '0.9375rem',
                    }}
                >
                    {plan.name}
                </p>
                <p
                    style={{
                        fontSize: '0.8125rem',
                        color: settings.secondary_text_color,
                        margin: 0,
                    }}
                >
                    ${displayPriceFormatted} {billingLabel}
                </p>
                {discountLabel && (
                    <p
                        style={{
                            fontSize: '0.75rem',
                            color: settings.primary_color,
                            margin: '6px 0 0',
                            fontWeight: 600,
                        }}
                    >
                        {discountLabel}
                    </p>
                )}
                {accessSummary && (
                    <p
                        style={{
                            fontSize: '0.75rem',
                            color: settings.secondary_text_color,
                            margin: '6px 0 0',
                            lineHeight: 1.5,
                        }}
                    >
                        {accessSummary}
                    </p>
                )}
            </div>

            <div style={{ marginBottom: '18px' }}>
                <label style={labelStyle}>Full Name *</label>
                <input
                    style={inputStyle}
                    type="text"
                    placeholder="John Doe"
                    readOnly
                />
            </div>
            <div style={{ marginBottom: '18px' }}>
                <label style={labelStyle}>Email *</label>
                <input
                    style={inputStyle}
                    type="email"
                    placeholder="john@example.com"
                    readOnly
                />
            </div>
            <div style={{ marginBottom: '18px' }}>
                <label style={labelStyle}>Phone (optional)</label>
                <input
                    style={inputStyle}
                    type="tel"
                    placeholder="+1 (555) 000-0000"
                    readOnly
                />
            </div>

            <span
                style={{
                    display: 'inline-block',
                    width: '100%',
                    padding: '13px 24px',
                    background: settings.primary_color,
                    color: settings.button_text_color,
                    textAlign: 'center',
                    fontWeight: 600,
                    fontSize: '0.875rem',
                    borderRadius: `${settings.button_border_radius}px`,
                    border: 'none',
                    boxSizing: 'border-box',
                    boxShadow: `0 1px 3px ${hexToRgba(settings.primary_color, 0.2)}`,
                    letterSpacing: '0.01em',
                    lineHeight: 1.5,
                }}
            >
                Continue to Payment &rarr;
            </span>
        </div>
    );
}

function SuccessView({
    settings,
    plans,
}: {
    settings: WidgetSettings;
    plans: MembershipPlan[];
}) {
    const plan = plans[0];
    const planName = plan?.name ?? 'Premium Plan';
    const priceFormatted = plan?.price_formatted ?? '29.99';
    const billingLabel = plan
        ? resolvePlanChargeLabel(plan, plan.billing_period)
        : 'per month';

    return (
        <div
            style={{
                textAlign: 'center',
                maxWidth: '440px',
                margin: '0 auto',
                padding: '16px 0',
            }}
        >
            <div
                style={{
                    width: '56px',
                    height: '56px',
                    borderRadius: '50%',
                    background: settings.primary_color,
                    color: settings.button_text_color,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    margin: '0 auto 20px',
                }}
            >
                <SuccessCheckIcon color={settings.button_text_color} />
            </div>

            <h2
                style={{
                    fontSize: '1.375rem',
                    fontWeight: 700,
                    margin: '0 0 6px',
                    letterSpacing: '-0.01em',
                }}
            >
                {settings.success_heading || 'You\u2019re all set!'}
            </h2>
            <p
                style={{
                    fontSize: '0.8125rem',
                    color: settings.secondary_text_color,
                    margin: '0 0 8px',
                    lineHeight: 1.6,
                }}
            >
                {settings.success_message || 'Your membership is now active.'}
            </p>

            {settings.show_access_code && (
                <div style={{ margin: '24px 0' }}>
                    <p
                        style={{
                            fontSize: '0.6875rem',
                            textTransform: 'uppercase',
                            letterSpacing: '0.08em',
                            color: settings.secondary_text_color,
                            margin: '0 0 8px',
                            fontWeight: 600,
                        }}
                    >
                        Your access code
                    </p>
                    <div
                        style={{
                            display: 'inline-block',
                            background: hexToRgba(settings.primary_color, 0.06),
                            border: `1px solid ${hexToRgba(settings.primary_color, 0.15)}`,
                            padding: '12px 28px',
                            borderRadius: `${settings.card_border_radius}px`,
                            fontSize: '1.75rem',
                            fontWeight: 700,
                            letterSpacing: '0.15em',
                            color: settings.text_color,
                        }}
                    >
                        ABC-1234
                    </div>
                </div>
            )}

            {settings.show_success_details && (
                <div
                    style={{
                        border: `1px solid ${settings.card_border_color}`,
                        borderRadius: `${settings.card_border_radius}px`,
                        padding: '16px 20px',
                        margin: '20px 0',
                        textAlign: 'left',
                        fontSize: '0.8125rem',
                    }}
                >
                    <div style={{ padding: '5px 0' }}>
                        <dt style={{ fontWeight: 600, display: 'inline' }}>
                            Plan:
                        </dt>
                        <dd
                            style={{
                                display: 'inline',
                                margin: '0 0 0 4px',
                                color: settings.secondary_text_color,
                            }}
                        >
                            {planName}
                        </dd>
                    </div>
                    <div style={{ padding: '5px 0' }}>
                        <dt style={{ fontWeight: 600, display: 'inline' }}>
                            Price:
                        </dt>
                        <dd
                            style={{
                                display: 'inline',
                                margin: '0 0 0 4px',
                                color: settings.secondary_text_color,
                            }}
                        >
                            ${priceFormatted} {billingLabel}
                        </dd>
                    </div>
                    <div style={{ padding: '5px 0' }}>
                        <dt style={{ fontWeight: 600, display: 'inline' }}>
                            Starts:
                        </dt>
                        <dd
                            style={{
                                display: 'inline',
                                margin: '0 0 0 4px',
                                color: settings.secondary_text_color,
                            }}
                        >
                            {new Date().toLocaleDateString()}
                        </dd>
                    </div>
                </div>
            )}

            <p
                style={{
                    fontSize: '0.8125rem',
                    color: settings.secondary_text_color,
                    margin: '0 0 8px',
                    lineHeight: 1.6,
                }}
            >
                A confirmation has been sent to john@example.com
            </p>

            {settings.show_cta_card && (
                <div
                    style={{
                        border: `1px solid ${hexToRgba(settings.primary_color, 0.2)}`,
                        borderRadius: `${settings.card_border_radius}px`,
                        padding: '24px',
                        margin: '24px 0 0',
                        textAlign: 'center',
                        background: hexToRgba(settings.primary_color, 0.03),
                    }}
                >
                    <h3
                        style={{
                            fontSize: '1.0625rem',
                            fontWeight: 600,
                            margin: '0 0 6px',
                            color: settings.text_color,
                        }}
                    >
                        Create an Account
                    </h3>
                    <p
                        style={{
                            fontSize: '0.8125rem',
                            color: settings.secondary_text_color,
                            margin: '0 0 16px',
                            lineHeight: 1.6,
                        }}
                    >
                        Manage your membership, view billing history, and update
                        your details all in one place.
                    </p>
                    <span
                        style={{
                            display: 'inline-block',
                            width: '100%',
                            padding: '13px 24px',
                            background: settings.primary_color,
                            color: settings.button_text_color,
                            textAlign: 'center',
                            fontWeight: 600,
                            fontSize: '0.875rem',
                            borderRadius: `${settings.button_border_radius}px`,
                            border: 'none',
                            boxSizing: 'border-box',
                            boxShadow: `0 1px 3px ${hexToRgba(settings.primary_color, 0.2)}`,
                            letterSpacing: '0.01em',
                            lineHeight: 1.5,
                        }}
                    >
                        Create Free Account
                    </span>
                </div>
            )}

            <div style={{ marginTop: '20px' }}>
                <span
                    style={{
                        display: 'inline-block',
                        width: '100%',
                        padding: '13px 24px',
                        background: 'transparent',
                        color: settings.primary_color,
                        textAlign: 'center',
                        fontWeight: 600,
                        fontSize: '0.875rem',
                        borderRadius: `${settings.button_border_radius}px`,
                        border: `1px solid ${settings.card_border_color}`,
                        boxSizing: 'border-box',
                        letterSpacing: '0.01em',
                        lineHeight: 1.5,
                    }}
                >
                    Browse Plans
                </span>
            </div>
        </div>
    );
}
