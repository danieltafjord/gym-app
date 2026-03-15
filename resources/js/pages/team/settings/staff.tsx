import { Form, Head, router } from '@inertiajs/react';
import { Mail, Trash2, UserMinus } from 'lucide-react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import TeamSettingsLayout from '@/layouts/team/team-settings-layout';
import type { BreadcrumbItem, StaffMember, Team, TeamInvitation } from '@/types';
import StaffController from '@/actions/App/Http/Controllers/Team/StaffController';
import team from '@/routes/team';

export default function StaffSettingsPage({
    team: currentTeam,
    staffMembers,
    pendingInvitations,
}: {
    team: Team;
    staffMembers: StaffMember[];
    pendingInvitations: TeamInvitation[];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Settings',
            href: team.settings.general.url(currentTeam.slug),
        },
        {
            title: 'Staff',
            href: team.settings.staff.url(currentTeam.slug),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Staff - Team Settings" />

            <div className="space-y-6 p-4">
                <TeamSettingsLayout teamSlug={currentTeam.slug}>
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Invite Staff Member</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Form
                                    {...StaffController.store.form(
                                        currentTeam.slug,
                                    )}
                                    options={{ preserveScroll: true }}
                                    className="flex items-end gap-4"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid flex-1 gap-2">
                                                <Label htmlFor="email">
                                                    Email Address
                                                </Label>
                                                <Input
                                                    id="email"
                                                    name="email"
                                                    type="email"
                                                    placeholder="staff@example.com"
                                                    required
                                                />
                                                <InputError
                                                    message={errors.email}
                                                />
                                            </div>
                                            <input
                                                type="hidden"
                                                name="role"
                                                value="team-admin"
                                            />
                                            <Button disabled={processing}>
                                                <Mail className="mr-2 size-4" />
                                                Send Invite
                                            </Button>
                                        </>
                                    )}
                                </Form>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Staff Members</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    {staffMembers.map((member) => (
                                        <div
                                            key={member.id}
                                            className="flex items-center justify-between rounded-lg border p-3"
                                        >
                                            <div>
                                                <p className="font-medium">
                                                    {member.name}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {member.email}
                                                </p>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Badge variant="secondary">
                                                    {member.role.replace(
                                                        'team-',
                                                        '',
                                                    )}
                                                </Badge>
                                                {member.role === 'team-admin' && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() =>
                                                            router.delete(
                                                                team.settings.staff.remove.url(
                                                                    {
                                                                        team: currentTeam.slug,
                                                                        user: member.id,
                                                                    },
                                                                ),
                                                                {
                                                                    preserveScroll:
                                                                        true,
                                                                },
                                                            )
                                                        }
                                                    >
                                                        <UserMinus className="size-4" />
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>

                        {pendingInvitations.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Pending Invitations</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {pendingInvitations.map(
                                            (invitation) => (
                                                <div
                                                    key={invitation.id}
                                                    className="flex items-center justify-between rounded-lg border p-3"
                                                >
                                                    <div>
                                                        <p className="font-medium">
                                                            {invitation.email}
                                                        </p>
                                                        <p className="text-sm text-muted-foreground">
                                                            Expires{' '}
                                                            {new Date(
                                                                invitation.expires_at,
                                                            ).toLocaleDateString()}
                                                        </p>
                                                    </div>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() =>
                                                            router.delete(
                                                                team.settings.staff.invitations.destroy.url(
                                                                    {
                                                                        team: currentTeam.slug,
                                                                        invitation:
                                                                            invitation.id,
                                                                    },
                                                                ),
                                                                {
                                                                    preserveScroll:
                                                                        true,
                                                                },
                                                            )
                                                        }
                                                    >
                                                        <Trash2 className="size-4" />
                                                    </Button>
                                                </div>
                                            ),
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </TeamSettingsLayout>
            </div>
        </AppLayout>
    );
}
