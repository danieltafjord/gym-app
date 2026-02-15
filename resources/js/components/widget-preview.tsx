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

function hexToRgba(hex: string, alpha: number): string {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r},${g},${b},${alpha})`;
}

function CheckIcon({ color }: { color: string }) {
    return (
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style={{ flexShrink: 0 }}>
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
            {view === 'plans' && <PlansView settings={settings} plans={plans} />}
            {view === 'checkout' && <CheckoutView settings={settings} plans={plans} />}
            {view === 'success' && <SuccessView settings={settings} plans={plans} />}
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
                No active plans to preview. Create membership plans to see the widget preview.
            </p>
        );
    }

    return (
        <>
            <div
                style={{
                    display: 'grid',
                    gridTemplateColumns: `repeat(${gridCols}, 1fr)`,
                    gap: '20px',
                }}
            >
                {plans.map((plan) => (
                    <div
                        key={plan.id}
                        style={{
                            border: `1px solid ${settings.card_border_color}`,
                            borderRadius: `${settings.card_border_radius}px`,
                            padding: '28px',
                            display: 'flex',
                            flexDirection: 'column',
                            background: settings.background_color,
                            boxShadow: '0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.03)',
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
                                {plan.price_formatted}
                            </p>
                            <span
                                style={{
                                    display: 'inline-block',
                                    fontSize: '0.6875rem',
                                    color: settings.secondary_text_color,
                                    margin: '8px 0 0',
                                    padding: '3px 10px',
                                    background: hexToRgba(settings.secondary_text_color, 0.07),
                                    borderRadius: '100px',
                                    fontWeight: 500,
                                }}
                            >
                                {plan.plan_type === 'one_time'
                                    ? 'One-time payment'
                                    : formatBillingPeriod(plan.billing_period)}
                            </span>
                        </div>

                        {settings.show_features &&
                            plan.features &&
                            plan.features.length > 0 && (
                                <>
                                    <div
                                        style={{
                                            height: '1px',
                                            background: settings.card_border_color,
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
                                        {plan.features.map((feature, index) => (
                                            <li
                                                key={index}
                                                style={{
                                                    padding: '5px 0',
                                                    fontSize: '0.8125rem',
                                                    color: settings.secondary_text_color,
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                    gap: '10px',
                                                }}
                                            >
                                                <CheckIcon color={settings.primary_color} />
                                                {feature}
                                            </li>
                                        ))}
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
                ))}
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

    const billingLabel =
        plan.plan_type === 'one_time' ? 'One-time payment' : formatBillingPeriod(plan.billing_period);

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
                <p style={{ fontWeight: 600, margin: '0 0 2px', fontSize: '0.9375rem' }}>
                    {plan.name}
                </p>
                <p
                    style={{
                        fontSize: '0.8125rem',
                        color: settings.secondary_text_color,
                        margin: 0,
                    }}
                >
                    ${plan.price_formatted} {billingLabel}
                </p>
            </div>

            <div style={{ marginBottom: '18px' }}>
                <label style={labelStyle}>Full Name *</label>
                <input style={inputStyle} type="text" placeholder="John Doe" readOnly />
            </div>
            <div style={{ marginBottom: '18px' }}>
                <label style={labelStyle}>Email *</label>
                <input style={inputStyle} type="email" placeholder="john@example.com" readOnly />
            </div>
            <div style={{ marginBottom: '18px' }}>
                <label style={labelStyle}>Phone (optional)</label>
                <input style={inputStyle} type="tel" placeholder="+1 (555) 000-0000" readOnly />
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
        ? plan.plan_type === 'one_time'
            ? 'One-time payment'
            : formatBillingPeriod(plan.billing_period)
        : 'per month';

    return (
        <div style={{ textAlign: 'center', maxWidth: '440px', margin: '0 auto', padding: '16px 0' }}>
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
                {settings.success_heading || "You\u2019re all set!"}
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
                        <dt style={{ fontWeight: 600, display: 'inline' }}>Plan:</dt>
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
                        <dt style={{ fontWeight: 600, display: 'inline' }}>Price:</dt>
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
                        <dt style={{ fontWeight: 600, display: 'inline' }}>Starts:</dt>
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
                        Manage your membership, view billing history, and update your details all in
                        one place.
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
