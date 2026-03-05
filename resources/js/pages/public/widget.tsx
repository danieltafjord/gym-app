import { Head, Link } from '@inertiajs/react';
import { useEffect } from 'react';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/layouts/public-layout';
import type { Gym, Team } from '@/types';

interface Props {
    team: Team;
    gym: Gym;
    widgetScriptUrl: string;
    gymPageUrl: string;
}

export default function PublicWidgetDemo({
    team,
    gym,
    widgetScriptUrl,
    gymPageUrl,
}: Props) {
    useEffect(() => {
        const script = document.createElement('script');
        script.src = `${widgetScriptUrl}?t=${Date.now()}`;
        script.async = true;

        document.body.appendChild(script);

        return () => {
            script.remove();
        };
    }, [widgetScriptUrl, team.slug, gym.slug]);

    return (
        <PublicLayout>
            <Head title={`Widget Demo - ${gym.name}`} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Widget Demo
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Live embed preview for {team.name} / {gym.name}
                        </p>
                    </div>

                    <Button variant="outline" asChild>
                        <Link href={gymPageUrl}>Back to Gym Page</Link>
                    </Button>
                </div>

                <div className="rounded-lg border p-4 sm:p-6">
                    <div
                        key={`${team.slug}-${gym.slug}`}
                        data-gymapp-widget=""
                        data-team={team.slug}
                        data-gym={gym.slug}
                    />
                </div>
            </div>
        </PublicLayout>
    );
}
