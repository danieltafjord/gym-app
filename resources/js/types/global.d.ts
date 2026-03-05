import type { Auth, CurrentTeam } from '@/types/auth';
import type { CheckInResult } from '@/types/team';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            currentTeam: CurrentTeam | null;
            sidebarOpen: boolean;
            flash: {
                checkInResult?: CheckInResult;
            };
            [key: string]: unknown;
        };
    }
}
