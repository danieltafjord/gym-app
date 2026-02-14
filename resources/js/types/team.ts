export type Team = {
    id: number;
    owner_id: number | null;
    name: string;
    slug: string;
    description: string | null;
    logo_path: string | null;
    is_active: boolean;
    stripe_account_id: string | null;
    stripe_onboarding_complete: boolean;
    gyms_count?: number;
    memberships_count?: number;
    owner?: {
        id: number;
        name: string;
        email: string;
    };
    membership_plans?: MembershipPlan[];
    created_at: string;
    updated_at: string;
};

export type Gym = {
    id: number;
    team_id: number;
    name: string;
    slug: string;
    address: string | null;
    phone: string | null;
    email: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
};

export type MembershipPlan = {
    id: number;
    team_id: number;
    name: string;
    description: string | null;
    price_cents: number;
    price_formatted: string;
    billing_period: 'weekly' | 'monthly' | 'quarterly' | 'yearly';
    plan_type: 'recurring' | 'one_time';
    features: string[] | null;
    is_active: boolean;
    sort_order: number;
    stripe_product_id: string | null;
    stripe_price_id: string | null;
    created_at: string;
    updated_at: string;
};

export type Membership = {
    id: number;
    user_id: number | null;
    team_id: number;
    membership_plan_id: number;
    email: string;
    customer_name: string;
    customer_phone: string | null;
    access_code: string;
    status: 'active' | 'cancelled' | 'expired' | 'paused';
    starts_at: string;
    ends_at: string | null;
    cancelled_at: string | null;
    stripe_subscription_id: string | null;
    stripe_payment_intent_id: string | null;
    stripe_status: string | null;
    user?: {
        id: number;
        name: string;
        email: string;
    };
    team?: Team;
    plan?: MembershipPlan;
    created_at: string;
    updated_at: string;
};

export type PaginatedData<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
};
