import type { Auth, ManagedTeam } from '@/types/auth';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            currentTeam: ManagedTeam | null;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
