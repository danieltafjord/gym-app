import { Head, Link } from '@inertiajs/react';
import { CheckCircle2, AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Team } from '@/types';
import team from '@/routes/team';

export default function StripeReturn({
    team: currentTeam,
    onboardingComplete,
}: {
    team: Team;
    onboardingComplete: boolean;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Stripe Setup',
            href: team.stripe.return(currentTeam.slug).url,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Stripe Setup" />

            <div className="flex items-center justify-center p-8">
                <Card className="w-full max-w-md">
                    <CardHeader className="text-center">
                        {onboardingComplete ? (
                            <CheckCircle2 className="mx-auto mb-2 size-12 text-green-500" />
                        ) : (
                            <AlertCircle className="mx-auto mb-2 size-12 text-yellow-500" />
                        )}
                        <CardTitle>
                            {onboardingComplete
                                ? 'Stripe Connected'
                                : 'Setup Incomplete'}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4 text-center">
                        <p className="text-sm text-muted-foreground">
                            {onboardingComplete
                                ? 'Your Stripe account is connected and ready to accept payments.'
                                : 'Your Stripe account setup is not yet complete. Please finish the onboarding process.'}
                        </p>
                        <div className="flex justify-center gap-2">
                            <Button asChild>
                                <Link href={team.show(currentTeam.slug).url}>
                                    Back to Dashboard
                                </Link>
                            </Button>
                            {!onboardingComplete && (
                                <Button variant="outline" asChild>
                                    <Link
                                        href={
                                            team.stripe.onboard(
                                                currentTeam.slug,
                                            ).url
                                        }
                                    >
                                        Retry Setup
                                    </Link>
                                </Button>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
