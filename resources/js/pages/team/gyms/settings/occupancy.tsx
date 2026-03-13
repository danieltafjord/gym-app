import { Head, useForm, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import GymSettingsLayout from '@/layouts/gym/gym-settings-layout';
import type { BreadcrumbItem, Gym, Team } from '@/types';
import GymController from '@/actions/App/Http/Controllers/Team/GymController';
import team from '@/routes/team';

export default function OccupancySettings({
    team: currentTeam,
    gym,
}: {
    team: Team;
    gym: Gym;
}) {
    const isSingleGymMode =
        usePage().props.currentTeam?.singleGym?.slug === gym.slug;
    const gymSettingsUrl = team.gyms.settings.general.url({
        team: currentTeam.slug,
        gym: gym.slug,
    });
    const breadcrumbs: BreadcrumbItem[] = isSingleGymMode
        ? [
              {
                  title: currentTeam.name,
                  href: team.show(currentTeam.slug).url,
              },
              {
                  title: 'Gym Settings',
                  href: gymSettingsUrl,
              },
              {
                  title: 'Occupancy',
                  href: team.gyms.settings.occupancy.url({
                      team: currentTeam.slug,
                      gym: gym.slug,
                  }),
              },
          ]
        : [
              {
                  title: currentTeam.name,
                  href: team.show(currentTeam.slug).url,
              },
              {
                  title: 'Gyms',
                  href: team.gyms.index(currentTeam.slug).url,
              },
              {
                  title: gym.name,
                  href: gymSettingsUrl,
              },
              {
                  title: 'Occupancy',
                  href: team.gyms.settings.occupancy.url({
                      team: currentTeam.slug,
                      gym: gym.slug,
                  }),
              },
          ];

    const { data, setData, patch, processing, errors, recentlySuccessful } = useForm({
        occupancy_tracking_enabled: gym.occupancy_tracking_enabled,
        show_occupancy_to_members: gym.show_occupancy_to_members,
        show_occupancy_predictions: gym.show_occupancy_predictions,
        max_capacity: gym.max_capacity ?? ('' as number | ''),
    });

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        patch(
            GymController.update({
                team: currentTeam.slug,
                gym: gym.slug,
            }).url,
            { preserveScroll: true },
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head
                title={
                    isSingleGymMode
                        ? 'Occupancy Tracking'
                        : `${gym.name} - Occupancy`
                }
            />

            <div className="mx-auto w-full max-w-6xl space-y-6 p-4">
                <GymSettingsLayout
                    teamSlug={currentTeam.slug}
                    gymSlug={gym.slug}
                    gymName={gym.name}
                    singleGymMode={isSingleGymMode}
                >
                    <Heading
                        title="Occupancy Tracking"
                        description="Show members a live activity graph so they can see how busy the gym is."
                    />

                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="occupancy_tracking_enabled"
                                checked={data.occupancy_tracking_enabled}
                                onCheckedChange={(checked) =>
                                    setData(
                                        'occupancy_tracking_enabled',
                                        checked === true,
                                    )
                                }
                            />
                            <Label htmlFor="occupancy_tracking_enabled">
                                Enable occupancy tracking
                            </Label>
                        </div>

                        {data.occupancy_tracking_enabled && (
                            <>
                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="show_occupancy_to_members"
                                    checked={data.show_occupancy_to_members}
                                    onCheckedChange={(checked) =>
                                        setData(
                                            'show_occupancy_to_members',
                                            checked === true,
                                        )
                                    }
                                />
                                <div>
                                    <Label htmlFor="show_occupancy_to_members">
                                        Show to members
                                    </Label>
                                    <p className="text-xs text-muted-foreground">
                                        Display the activity graph on members' account page.
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="show_occupancy_predictions"
                                    checked={data.show_occupancy_predictions}
                                    onCheckedChange={(checked) =>
                                        setData(
                                            'show_occupancy_predictions',
                                            checked === true,
                                        )
                                    }
                                />
                                <div>
                                    <Label htmlFor="show_occupancy_predictions">
                                        Show predicted activity
                                    </Label>
                                    <p className="text-xs text-muted-foreground">
                                        Display a prediction line based on historical patterns for the same day of week. Requires at least 4 weeks of data.
                                    </p>
                                </div>
                            </div>

                            <div className="grid max-w-xs gap-2">
                                <Label htmlFor="max_capacity">
                                    Max Capacity
                                </Label>
                                <Input
                                    id="max_capacity"
                                    type="number"
                                    min={1}
                                    max={10000}
                                    value={data.max_capacity}
                                    onChange={(e) =>
                                        setData(
                                            'max_capacity',
                                            e.target.value === ''
                                                ? ''
                                                : Number(e.target.value),
                                        )
                                    }
                                    placeholder="e.g. 100"
                                />
                                <p className="text-xs text-muted-foreground">
                                    The maximum number of people the gym can
                                    hold. Used to calculate how busy the gym
                                    appears.
                                </p>
                                <InputError
                                    message={errors.max_capacity}
                                />
                            </div>
                            </>
                        )}

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>
                                Save Changes
                            </Button>
                            {recentlySuccessful && (
                                <p className="text-sm text-muted-foreground">
                                    Saved.
                                </p>
                            )}
                        </div>
                    </form>
                </GymSettingsLayout>
            </div>
        </AppLayout>
    );
}
