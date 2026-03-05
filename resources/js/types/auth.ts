export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type SingleGym = {
    id: number;
    name: string;
    slug: string;
};

export type ManagedTeam = {
    id: number;
    name: string;
    slug: string;
};

export type CurrentTeam = ManagedTeam & {
    singleGym: SingleGym | null;
};

export type Auth = {
    user: User;
    roles: string[];
    managedTeams: ManagedTeam[];
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
