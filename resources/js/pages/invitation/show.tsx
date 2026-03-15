import { Head, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AuthLayout from '@/layouts/auth-layout';

interface InvitationData {
    token: string;
    email: string;
    role: string;
    team_name: string;
    expires_at: string;
}

export default function ShowInvitation({
    invitation,
}: {
    invitation: InvitationData;
}) {
    const { auth } = usePage().props;

    const handleAccept = () => {
        router.post(`/invitation/${invitation.token}/accept`);
    };

    return (
        <AuthLayout
            title="Team Invitation"
            description={`You've been invited to join ${invitation.team_name}`}
        >
            <Head title="Team Invitation" />

            <Card>
                <CardHeader>
                    <CardTitle>Join {invitation.team_name}</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <p className="text-sm text-muted-foreground">
                        You have been invited to join{' '}
                        <strong>{invitation.team_name}</strong> as a{' '}
                        <strong>
                            {invitation.role.replace('team-', '')}
                        </strong>
                        .
                    </p>
                    <p className="text-sm text-muted-foreground">
                        This invitation was sent to{' '}
                        <strong>{invitation.email}</strong> and expires on{' '}
                        {new Date(invitation.expires_at).toLocaleDateString()}.
                    </p>

                    {auth.user ? (
                        <Button onClick={handleAccept} className="w-full">
                            Accept Invitation
                        </Button>
                    ) : (
                        <div className="space-y-2">
                            <p className="text-sm text-muted-foreground">
                                Please log in or create an account to accept
                                this invitation.
                            </p>
                            <Button asChild className="w-full">
                                <a
                                    href={`/login?redirect=/invitation/${invitation.token}`}
                                >
                                    Log In to Accept
                                </a>
                            </Button>
                        </div>
                    )}
                </CardContent>
            </Card>
        </AuthLayout>
    );
}
