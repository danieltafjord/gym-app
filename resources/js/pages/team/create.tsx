import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import TeamController from '@/actions/App/Http/Controllers/Team/TeamController';
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create Team',
        href: team.create().url,
    },
];

export default function CreateTeam() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Team" />

            <div className="mx-auto w-full max-w-2xl space-y-6 p-4">
                <Heading
                    title="Create Team"
                    description="Set up a new team to manage your gyms and memberships."
                />

                <Form
                    {...TeamController.store.form()}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Team Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    placeholder="Enter team name"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">
                                    Description{' '}
                                    <span className="text-muted-foreground">(optional)</span>
                                </Label>
                                <Textarea
                                    id="description"
                                    name="description"
                                    placeholder="Briefly describe your team"
                                    rows={4}
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="default_currency">Default Currency</Label>
                                    <Select name="default_currency" defaultValue="USD">
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
                                    <Select name="default_language" defaultValue="en">
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
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>
                                    Create Team
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
