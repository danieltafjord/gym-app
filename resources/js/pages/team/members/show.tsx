import { Form, Head, Link, router } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Membership, Team } from '@/types';
import MemberController from '@/actions/App/Http/Controllers/Team/MemberController';
import team from '@/routes/team';

export default function MemberShow({
    team: currentTeam,
    membership,
}: {
    team: Team;
    membership: Membership;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Members',
            href: team.members.index(currentTeam.slug).url,
        },
        {
            title: membership.user?.name ?? 'Member',
            href: team.members.show({
                team: currentTeam.slug,
                membership: membership.id,
            }).url,
        },
    ];

    const statusVariant = (status: Membership['status']) => {
        switch (status) {
            case 'active':
                return 'default' as const;
            case 'cancelled':
                return 'destructive' as const;
            default:
                return 'secondary' as const;
        }
    };

    const handleRemoveMember = () => {
        if (confirm('Are you sure you want to remove this member?')) {
            router.delete(
                MemberController.destroy({
                    team: currentTeam.slug,
                    membership: membership.id,
                }).url,
            );
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${membership.user?.name ?? 'Member'} - Details`} />

            <div className="mx-auto w-full max-w-2xl space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title={membership.user?.name ?? 'Member Details'}
                        description={membership.user?.email ?? undefined}
                    />
                    <Badge variant={statusVariant(membership.status)}>
                        {membership.status}
                    </Badge>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Membership Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Plan</dt>
                                <dd className="mt-1">{membership.plan?.name ?? 'No plan'}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd className="mt-1 capitalize">{membership.status}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Start Date</dt>
                                <dd className="mt-1">
                                    {new Date(membership.starts_at).toLocaleDateString()}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">End Date</dt>
                                <dd className="mt-1">
                                    {membership.ends_at
                                        ? new Date(membership.ends_at).toLocaleDateString()
                                        : 'Ongoing'}
                                </dd>
                            </div>
                            {membership.cancelled_at && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Cancelled At</dt>
                                    <dd className="mt-1">
                                        {new Date(membership.cancelled_at).toLocaleDateString()}
                                    </dd>
                                </div>
                            )}
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Member Since</dt>
                                <dd className="mt-1">
                                    {new Date(membership.created_at).toLocaleDateString()}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Update Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Form
                            {...MemberController.update.form.patch({
                                team: currentTeam.slug,
                                membership: membership.id,
                            })}
                            options={{ preserveScroll: true }}
                            className="flex items-end gap-4"
                        >
                            {({ processing }) => (
                                <>
                                    <div className="grid flex-1 gap-2">
                                        <Select name="status" defaultValue={membership.status}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="active">Active</SelectItem>
                                                <SelectItem value="paused">Paused</SelectItem>
                                                <SelectItem value="cancelled">Cancelled</SelectItem>
                                                <SelectItem value="expired">Expired</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <Button disabled={processing}>
                                        Update Status
                                    </Button>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>

                <div className="flex items-center justify-between rounded-lg border border-destructive/20 bg-destructive/5 p-4">
                    <div>
                        <p className="font-medium">Remove Member</p>
                        <p className="text-sm text-muted-foreground">
                            This will permanently remove this membership.
                        </p>
                    </div>
                    <Button variant="destructive" onClick={handleRemoveMember}>
                        Remove
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
