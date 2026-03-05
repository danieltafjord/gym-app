export type Team = {
    id: number;
    owner_id: number | null;
    name: string;
    slug: string;
    description: string | null;
    default_currency: string;
    default_language: string;
    logo_path: string | null;
    is_active: boolean;
    stripe_account_id: string | null;
    stripe_onboarding_complete: boolean;
    widget_settings: WidgetSettings | null;
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

export type WidgetSettings = {
    primary_color: string;
    background_color: string;
    text_color: string;
    secondary_text_color: string;
    font_family: string;
    card_border_radius: number;
    button_border_radius: number;
    input_border_color: string;
    input_background_color: string;
    input_border_radius: number;
    card_border_color: string;
    button_text_color: string;
    padding: number;
    show_features: boolean;
    show_description: boolean;
    button_text: string;
    yearly_toggle_promo_text: string;
    columns: number;
    show_access_code: boolean;
    show_success_details: boolean;
    show_cta_card: boolean;
    success_heading: string;
    success_message: string;
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
    widget_settings: WidgetSettings | null;
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
    yearly_price_cents: number | null;
    yearly_price_formatted: string | null;
    billing_period: 'weekly' | 'monthly' | 'quarterly' | 'yearly';
    plan_type: 'recurring' | 'one_time';
    features: string[] | null;
    is_active: boolean;
    sort_order: number;
    stripe_product_id: string | null;
    stripe_price_id: string | null;
    stripe_yearly_price_id: string | null;
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

export type MembershipNote = {
    id: number;
    membership_id: number;
    team_id: number;
    user_id: number;
    content: string;
    author?: {
        id: number;
        name: string;
    };
    created_at: string;
    updated_at: string;
};

export type CheckInMethod = 'qr_scan' | 'barcode_scanner' | 'manual_entry';

export type CheckIn = {
    id: number;
    membership_id: number;
    team_id: number;
    gym_id: number | null;
    checked_in_by: number | null;
    method: CheckInMethod;
    membership?: Membership;
    gym?: Gym;
    checked_in_by_user?: {
        id: number;
        name: string;
    };
    created_at: string;
    updated_at: string;
};

export type CheckInSettings = {
    enabled: boolean;
    allowed_methods: CheckInMethod[];
    require_gym_selection: boolean;
    prevent_duplicate_minutes: number;
    kiosk_mode: 'camera' | 'barcode_scanner';
};

export type CheckInResult = {
    success: boolean;
    message: string;
    membership: {
        id: number;
        customer_name: string;
        plan_name: string | null;
        status: string;
    } | null;
    check_in: {
        id: number;
        created_at: string;
        gym_name: string | null;
    } | null;
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
