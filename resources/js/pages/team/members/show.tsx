import { Form, Head, Link, router, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import type {
    BreadcrumbItem,
    CheckIn,
    Membership,
    MembershipNote,
    MembershipPlan,
    PaginatedData,
    Team,
} from '@/types';
import MemberController from '@/actions/App/Http/Controllers/Team/MemberController';
import MembershipNoteController from '@/actions/App/Http/Controllers/Team/MembershipNoteController';
import team from '@/routes/team';

export default function MemberShow({
    team: currentTeam,
    membership,
    plans,
    checkIns,
    notes,
}: {
    team: Team;
    membership: Membership;
    plans: MembershipPlan[];
    checkIns: PaginatedData<CheckIn>;
    notes: MembershipNote[];
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
            title: membership.customer_name,
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

    const noteForm = useForm({ content: '' });

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

    const methodBadge = (method: CheckIn['method']) => {
        switch (method) {
            case 'qr_scan':
                return 'QR Scan';
            case 'barcode_scanner':
                return 'Barcode';
            case 'manual_entry':
                return 'Manual';
            default:
                return method;
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${membership.customer_name} - Details`} />

            <div className="mx-auto w-full max-w-2xl space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title={membership.customer_name}
                        description={membership.email}
                    />
                    <Badge variant={statusVariant(membership.status)}>
                        {membership.status}
                    </Badge>
                </div>

                {/* Membership Details */}
                <Card>
                    <CardHeader>
                        <CardTitle>Membership Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Plan
                                </dt>
                                <dd className="mt-1">
                                    {membership.plan?.name ?? 'No plan'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Status
                                </dt>
                                <dd className="mt-1 capitalize">
                                    {membership.status}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Start Date
                                </dt>
                                <dd className="mt-1">
                                    {membership.starts_at
                                        ? new Date(
                                              membership.starts_at,
                                          ).toLocaleDateString()
                                        : 'Not activated'}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    End Date
                                </dt>
                                <dd className="mt-1">
                                    {membership.ends_at
                                        ? new Date(
                                              membership.ends_at,
                                          ).toLocaleDateString()
                                        : 'Ongoing'}
                                </dd>
                            </div>
                            {membership.cancelled_at && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">
                                        Cancelled At
                                    </dt>
                                    <dd className="mt-1">
                                        {new Date(
                                            membership.cancelled_at,
                                        ).toLocaleDateString()}
                                    </dd>
                                </div>
                            )}
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Member Since
                                </dt>
                                <dd className="mt-1">
                                    {new Date(
                                        membership.created_at,
                                    ).toLocaleDateString()}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                {/* Edit Details */}
                <Card>
                    <CardHeader>
                        <CardTitle>Edit Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Form
                            {...MemberController.updateDetails.form.patch({
                                team: currentTeam.slug,
                                membership: membership.id,
                            })}
                            options={{ preserveScroll: true }}
                            className="space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="customer_name">
                                                Name
                                            </Label>
                                            <Input
                                                id="customer_name"
                                                name="customer_name"
                                                defaultValue={
                                                    membership.customer_name
                                                }
                                            />
                                            <InputError
                                                message={errors.customer_name}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="email">Email</Label>
                                            <Input
                                                id="email"
                                                name="email"
                                                type="email"
                                                defaultValue={membership.email}
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>
                                    </div>
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="customer_phone">
                                                Phone
                                            </Label>
                                            <Input
                                                id="customer_phone"
                                                name="customer_phone"
                                                defaultValue={
                                                    membership.customer_phone ??
                                                    ''
                                                }
                                            />
                                            <InputError
                                                message={errors.customer_phone}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="membership_plan_id">
                                                Plan
                                            </Label>
                                            <Select
                                                name="membership_plan_id"
                                                defaultValue={String(
                                                    membership.membership_plan_id,
                                                )}
                                            >
                                                <SelectTrigger id="membership_plan_id">
                                                    <SelectValue placeholder="Select plan" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {plans.map((plan) => (
                                                        <SelectItem
                                                            key={plan.id}
                                                            value={String(
                                                                plan.id,
                                                            )}
                                                        >
                                                            {plan.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError
                                                message={
                                                    errors.membership_plan_id
                                                }
                                            />
                                        </div>
                                    </div>
                                    <Button disabled={processing}>
                                        Save Changes
                                    </Button>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>

                {/* Update Status */}
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
                                        <Select
                                            name="status"
                                            defaultValue={membership.status}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="active">
                                                    Active
                                                </SelectItem>
                                                <SelectItem value="paused">
                                                    Paused
                                                </SelectItem>
                                                <SelectItem value="cancelled">
                                                    Cancelled
                                                </SelectItem>
                                                <SelectItem value="expired">
                                                    Expired
                                                </SelectItem>
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

                {/* Extend Membership */}
                <Card>
                    <CardHeader>
                        <CardTitle>Extend Membership</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Form
                            {...MemberController.extend.form.patch({
                                team: currentTeam.slug,
                                membership: membership.id,
                            })}
                            options={{ preserveScroll: true }}
                            className="space-y-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="ends_at">
                                                New End Date
                                            </Label>
                                            <Input
                                                id="ends_at"
                                                name="ends_at"
                                                type="date"
                                                required
                                            />
                                            <InputError
                                                message={errors.ends_at}
                                            />
                                        </div>
                                        {membership.status === 'expired' && (
                                            <div className="flex items-center gap-2 self-center">
                                                <input
                                                    id="reactivate"
                                                    name="reactivate"
                                                    type="checkbox"
                                                    value="1"
                                                    className="size-4 rounded border-gray-300"
                                                />
                                                <Label htmlFor="reactivate">
                                                    Reactivate membership
                                                </Label>
                                            </div>
                                        )}
                                    </div>
                                    <Button disabled={processing}>
                                        Extend
                                    </Button>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>

                {/* Check-In History */}
                <Card>
                    <CardHeader>
                        <CardTitle>Check-In History</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {checkIns.data.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No check-ins recorded yet.
                            </p>
                        ) : (
                            <>
                                <div className="overflow-hidden rounded-lg border">
                                    <table className="w-full text-sm">
                                        <thead className="border-b bg-muted/50">
                                            <tr>
                                                <th className="px-4 py-2 text-left font-medium">
                                                    Date/Time
                                                </th>
                                                <th className="px-4 py-2 text-left font-medium">
                                                    Gym
                                                </th>
                                                <th className="px-4 py-2 text-left font-medium">
                                                    Method
                                                </th>
                                                <th className="px-4 py-2 text-left font-medium">
                                                    Staff
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y">
                                            {checkIns.data.map((checkIn) => (
                                                <tr key={checkIn.id}>
                                                    <td className="px-4 py-2">
                                                        {new Date(
                                                            checkIn.created_at,
                                                        ).toLocaleString()}
                                                    </td>
                                                    <td className="px-4 py-2">
                                                        {checkIn.gym?.name ??
                                                            '-'}
                                                    </td>
                                                    <td className="px-4 py-2">
                                                        <Badge variant="secondary">
                                                            {methodBadge(
                                                                checkIn.method,
                                                            )}
                                                        </Badge>
                                                    </td>
                                                    <td className="px-4 py-2">
                                                        {checkIn
                                                            .checked_in_by_user
                                                            ?.name ?? '-'}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                                {checkIns.last_page > 1 && (
                                    <div className="mt-4 flex items-center justify-center gap-2">
                                        {checkIns.links.map((link, index) => (
                                            <Button
                                                key={index}
                                                variant={
                                                    link.active
                                                        ? 'default'
                                                        : 'outline'
                                                }
                                                size="sm"
                                                disabled={!link.url}
                                                asChild={!!link.url}
                                            >
                                                {link.url ? (
                                                    <Link
                                                        href={link.url}
                                                        preserveScroll
                                                        dangerouslySetInnerHTML={{
                                                            __html: link.label,
                                                        }}
                                                    />
                                                ) : (
                                                    <span
                                                        dangerouslySetInnerHTML={{
                                                            __html: link.label,
                                                        }}
                                                    />
                                                )}
                                            </Button>
                                        ))}
                                    </div>
                                )}
                            </>
                        )}
                    </CardContent>
                </Card>

                {/* Notes */}
                <Card>
                    <CardHeader>
                        <CardTitle>Notes</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                noteForm.post(
                                    MembershipNoteController.store({
                                        team: currentTeam.slug,
                                        membership: membership.id,
                                    }).url,
                                    {
                                        preserveScroll: true,
                                        onSuccess: () =>
                                            noteForm.reset('content'),
                                    },
                                );
                            }}
                            className="space-y-3"
                        >
                            <Textarea
                                placeholder="Add a note..."
                                value={noteForm.data.content}
                                onChange={(e) =>
                                    noteForm.setData('content', e.target.value)
                                }
                                rows={3}
                            />
                            <InputError message={noteForm.errors.content} />
                            <Button
                                disabled={
                                    noteForm.processing ||
                                    !noteForm.data.content.trim()
                                }
                            >
                                Add Note
                            </Button>
                        </form>

                        {notes.length > 0 && (
                            <div className="space-y-3 border-t pt-4">
                                {notes.map((note) => (
                                    <div
                                        key={note.id}
                                        className="rounded-lg border p-3"
                                    >
                                        <div className="flex items-center justify-between text-xs text-muted-foreground">
                                            <span className="font-medium">
                                                {note.author?.name ?? 'Unknown'}
                                            </span>
                                            <span>
                                                {new Date(
                                                    note.created_at,
                                                ).toLocaleString()}
                                            </span>
                                        </div>
                                        <p className="mt-1 text-sm whitespace-pre-wrap">
                                            {note.content}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Remove Member */}
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
