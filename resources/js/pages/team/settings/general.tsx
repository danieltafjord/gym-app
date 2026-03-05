import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import TeamSettingsLayout from '@/layouts/team/team-settings-layout';
import type { BreadcrumbItem, Team } from '@/types';
import TeamGeneralSettingsController from '@/actions/App/Http/Controllers/Team/TeamGeneralSettingsController';
import team from '@/routes/team';

const currencyOptions = [
    { value: 'USD', label: 'USD - US Dollar' },
    { value: 'EUR', label: 'EUR - Euro' },
    { value: 'GBP', label: 'GBP - British Pound' },
    { value: 'NOK', label: 'NOK - Norwegian Krone' },
];

const languageOptions = [
    { value: 'en', label: 'English' },
    { value: 'nb', label: 'Norwegian (Bokmal)' },
    { value: 'sv', label: 'Swedish' },
    { value: 'da', label: 'Danish' },
];

export default function TeamGeneralSettingsPage({ team: currentTeam }: { team: Team }) {
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
            title: 'General',
            href: team.settings.general.url(currentTeam.slug),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="General Settings - Team Settings" />

            <div className="space-y-6 p-4">
                <TeamSettingsLayout teamSlug={currentTeam.slug}>
                    <Form
                        {...TeamGeneralSettingsController.update.form.patch(currentTeam.slug)}
                        options={{ preserveScroll: true }}
                        className="max-w-lg space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="default_currency">Default Currency</Label>
                                    <Select
                                        name="default_currency"
                                        defaultValue={currentTeam.default_currency ?? 'USD'}
                                    >
                                        <SelectTrigger id="default_currency">
                                            <SelectValue placeholder="Select currency" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {currencyOptions.map((currency) => (
                                                <SelectItem key={currency.value} value={currency.value}>
                                                    {currency.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.default_currency} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="default_language">Default Language</Label>
                                    <Select
                                        name="default_language"
                                        defaultValue={currentTeam.default_language ?? 'en'}
                                    >
                                        <SelectTrigger id="default_language">
                                            <SelectValue placeholder="Select language" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {languageOptions.map((language) => (
                                                <SelectItem key={language.value} value={language.value}>
                                                    {language.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.default_language} />
                                </div>

                                <Button disabled={processing}>Save Settings</Button>
                            </>
                        )}
                    </Form>
                </TeamSettingsLayout>
            </div>
        </AppLayout>
    );
}
