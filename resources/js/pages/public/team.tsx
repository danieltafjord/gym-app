import { Head, Link } from '@inertiajs/react';
import { Dumbbell } from 'lucide-react';
import Heading from '@/components/heading';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import PublicLayout from '@/layouts/public-layout';
import type { Gym, Team } from '@/types';

interface Props {
    team: Team & { gyms: Gym[] };
}

export default function PublicTeam({ team }: Props) {
    return (
        <PublicLayout>
            <Head title={team.name} />

            <Heading
                title={team.name}
                description={team.description ?? undefined}
            />

            {team.gyms.length > 0 ? (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {team.gyms.map((gym) => (
                        <Link
                            key={gym.id}
                            href={`/${team.slug}/${gym.slug}`}
                            className="block"
                        >
                            <Card className="transition-shadow hover:shadow-md">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Dumbbell className="size-5 text-muted-foreground" />
                                        {gym.name}
                                    </CardTitle>
                                </CardHeader>
                                {gym.address && (
                                    <CardContent>
                                        <p className="text-sm text-muted-foreground">
                                            {gym.address}
                                        </p>
                                    </CardContent>
                                )}
                            </Card>
                        </Link>
                    ))}
                </div>
            ) : (
                <Card className="py-12">
                    <CardContent className="text-center">
                        <p className="text-muted-foreground">
                            No gyms available at the moment.
                        </p>
                    </CardContent>
                </Card>
            )}
        </PublicLayout>
    );
}
