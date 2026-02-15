import type { Auth, ManagedTeam } from '@/types/auth';
import type { CheckInResult } from '@/types/team';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            currentTeam: ManagedTeam | null;
            sidebarOpen: boolean;
            flash: {
                checkInResult?: CheckInResult;
            };
            [key: string]: unknown;
        };
    }
}
