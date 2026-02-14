import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
} from '@/components/ui/card';
import AccountLayout from '@/layouts/account-layout';
import type { Membership } from '@/types';

interface Props {
    memberships: Membership[];
}

export default function AccountDashboard({ memberships }: Props) {
    const activeMemberships = memberships.filter(
        (m) => m.status === 'active',
    );
    const otherMemberships = memberships.filter(
        (m) => m.status !== 'active',
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
                                    </div>
                                    <Badge variant="secondary">
                                        {membership.status}
                                    </Badge>
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
