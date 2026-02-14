import type { PropsWithChildren } from 'react';
import { AccountHeader } from '@/layouts/account/account-header';

export default function AccountLayout({ children }: PropsWithChildren) {
    return (
        <div className="min-h-screen bg-background">
            <AccountHeader />
            <main className="mx-auto max-w-5xl px-4 py-6 sm:px-6">
                {children}
            </main>
        </div>
    );
}
