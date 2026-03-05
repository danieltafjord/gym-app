import { Form, Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import GymSettingsLayout from '@/layouts/gym/gym-settings-layout';
import type { BreadcrumbItem, Gym, Team } from '@/types';
import GymController from '@/actions/App/Http/Controllers/Team/GymController';
import team from '@/routes/team';

export default function GeneralSettings({
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
                  title: 'General',
                  href: gymSettingsUrl,
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
                  title: 'Settings',
                  href: gymSettingsUrl,
              },
          ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isSingleGymMode ? 'Gym Settings' : `${gym.name} - Settings`} />

            <div className="mx-auto w-full max-w-6xl space-y-6 p-4">
                <GymSettingsLayout
                    teamSlug={currentTeam.slug}
                    gymSlug={gym.slug}
                    gymName={gym.name}
                    singleGymMode={isSingleGymMode}
                >
                    <Heading
                        title="General"
                        description={
                            isSingleGymMode
                                ? 'Update your gym details.'
                                : `Update details for ${gym.name}.`
                        }
                    />

                    <Form
                        {...GymController.update.form.patch({
                            team: currentTeam.slug,
                            gym: gym.slug,
                        })}
                        options={{ preserveScroll: true }}
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Gym Name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={gym.name}
                                        required
                                        placeholder="Enter gym name"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="address">Address</Label>
                                    <Input
                                        id="address"
                                        name="address"
                                        defaultValue={gym.address ?? ''}
                                        placeholder="Enter gym address"
                                    />
                                    <InputError message={errors.address} />
                                </div>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="phone">Phone</Label>
                                        <Input
                                            id="phone"
                                            name="phone"
                                            type="tel"
                                            defaultValue={gym.phone ?? ''}
                                            placeholder="Enter phone number"
                                        />
                                        <InputError message={errors.phone} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="email">Email</Label>
                                        <Input
                                            id="email"
                                            name="email"
                                            type="email"
                                            defaultValue={gym.email ?? ''}
                                            placeholder="Enter email address"
                                        />
                                        <InputError message={errors.email} />
                                    </div>
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing}>
                                        Save Changes
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </GymSettingsLayout>
            </div>
        </AppLayout>
    );
}
