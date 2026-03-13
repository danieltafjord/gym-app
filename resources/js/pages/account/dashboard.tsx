import { Head } from '@inertiajs/react';
import { QRCodeSVG } from 'qrcode.react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { resolvePlanAccessSummary } from '@/lib/membership-plans';
import AccountLayout from '@/layouts/account-layout';
import type { Membership } from '@/types';

interface Props {
    memberships: Membership[];
}

function formatDateTime(value: string | null): string | null {
    if (!value) {
        return null;
    }

    return new Date(value).toLocaleString();
}

export default function AccountDashboard({ memberships }: Props) {
    const activeMemberships = memberships.filter(
        (membership) => membership.is_currently_valid,
    );
    const otherMemberships = memberships.filter(
        (membership) => !membership.is_currently_valid,
    );

    return (
        <AccountLayout>
            <Head title="My Account" />

            <Heading
                title="My Account"
                description="Manage your memberships and account settings."
            />

            {activeMemberships.length > 0 && (
                <section className="mb-8">
                    <h3 className="mb-4 text-lg font-semibold tracking-tight">
                        Active Memberships
                    </h3>
                    <div className="space-y-3">
                        {activeMemberships.map((membership) => (
                            <Card key={membership.id} className="py-4">
                                <CardContent className="flex items-center justify-between">
                                    <div>
                                        <p className="font-medium">
                                            {membership.team?.name}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {membership.plan?.name}
                                        </p>
                                        {membership.plan && (
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {resolvePlanAccessSummary(
                                                    membership.plan,
                                                )}
                                            </p>
                                        )}
                                        {membership.ends_at && (
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                Expires{' '}
                                                {formatDateTime(
                                                    membership.ends_at,
                                                )}
                                            </p>
                                        )}
                                    </div>
                                    <Badge variant="secondary">
                                        {membership.status}
                                    </Badge>
                                </CardContent>
                                <CardContent className="flex flex-col items-center border-t pt-4">
                                    <QRCodeSVG
                                        value={membership.access_code}
                                        size={160}
                                        level="M"
                                    />
                                    <p className="mt-2 font-mono text-sm tracking-widest text-muted-foreground">
                                        {membership.access_code}
                                    </p>
                                    <p className="mt-1 text-xs text-muted-foreground">
                                        Show this QR code at the gym to check in
                                    </p>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </section>
            )}

            {otherMemberships.length > 0 && (
                <section className="mb-8">
                    <h3 className="mb-4 text-lg font-semibold tracking-tight">
                        Past Memberships
                    </h3>
                    <div className="space-y-3">
                        {otherMemberships.map((membership) => (
                            <Card key={membership.id} className="py-4">
                                <CardContent className="flex items-center justify-between">
                                    <div>
                                        <p className="font-medium">
                                            {membership.team?.name}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {membership.plan?.name}
                                        </p>
                                        {membership.starts_at === null &&
                                            membership.plan?.activation_mode ===
                                                'first_check_in' && (
                                                <p className="mt-1 text-xs text-muted-foreground">
                                                    Activates on first check-in
                                                </p>
                                            )}
                                        {membership.ends_at && (
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                Ended{' '}
                                                {formatDateTime(
                                                    membership.ends_at,
                                                )}
                                            </p>
                                        )}
                                    </div>
                                    <Badge variant="outline">
                                        {membership.status}
                                    </Badge>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </section>
            )}

            {memberships.length === 0 && (
                <Card className="py-12">
                    <CardContent className="text-center">
                        <p className="text-muted-foreground">
                            You don't have any memberships yet.
                        </p>
                    </CardContent>
                </Card>
            )}
        </AccountLayout>
    );
}
