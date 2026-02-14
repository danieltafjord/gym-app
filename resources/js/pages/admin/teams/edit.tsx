import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import AdminTeamController from '@/actions/App/Http/Controllers/Admin/TeamController';
import admin from '@/routes/admin';
import type { BreadcrumbItem, Team } from '@/types';

export default function TeamsEdit({ team }: { team: Team }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Admin',
            href: admin.dashboard().url,
        },
        {
            title: 'Teams',
            href: admin.teams.index().url,
        },
        {
            title: team.name,
            href: admin.teams.edit.url(team),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${team.name}`} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
                <Heading
                    title={`Edit ${team.name}`}
                    description="Update team details"
                />

                <div className="max-w-2xl">
                    <Form
                        {...AdminTeamController.update.form(team.slug)}
                        options={{ preserveScroll: true }}
                        className="space-y-6"
                    >
                        {({ processing, errors, recentlySuccessful }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Team Name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={team.name}
                                        required
                                        placeholder="Enter team name"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="description">
                                        Description
                                    </Label>
                                    <Input
                                        id="description"
                                        name="description"
                                        defaultValue={team.description ?? ''}
                                        placeholder="Enter team description (optional)"
                                    />
                                    <InputError message={errors.description} />
                                </div>

                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="is_active"
                                        name="is_active"
                                        defaultChecked={team.is_active}
                                        value="1"
                                    />
                                    <Label htmlFor="is_active">
                                        Team is active
                                    </Label>
                                    <InputError message={errors.is_active} />
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing}>
                                        Save Changes
                                    </Button>

                                    {recentlySuccessful && (
                                        <p className="text-sm text-neutral-600">
                                            Saved
                                        </p>
                                    )}
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </AppLayout>
    );
}
