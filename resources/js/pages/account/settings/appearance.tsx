import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import Heading from '@/components/heading';
import AccountLayout from '@/layouts/account-layout';
import AccountSettingsLayout from '@/layouts/account/account-settings-layout';

export default function Appearance() {
    return (
        <AccountLayout>
            <Head title="Appearance settings" />

            <h1 className="sr-only">Appearance Settings</h1>

            <AccountSettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title="Appearance settings"
                        description="Update your account's appearance settings"
                    />
                    <AppearanceTabs />
                </div>
            </AccountSettingsLayout>
        </AccountLayout>
    );
}
