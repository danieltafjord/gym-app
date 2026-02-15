import { Head, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
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
import type { BreadcrumbItem, CheckInSettings, Gym, Team } from '@/types';
import CheckInSettingsController from '@/actions/App/Http/Controllers/Team/CheckInSettingsController';
import team from '@/routes/team';
import { kiosk } from '@/routes/public';

const METHOD_OPTIONS = [
    { value: 'qr_scan' as const, label: 'QR Code Scan' },
    { value: 'barcode_scanner' as const, label: 'Barcode Scanner' },
    { value: 'manual_entry' as const, label: 'Manual Entry' },
];

export default function CheckInSettingsPage({
    team: currentTeam,
    settings,
    gyms,
}: {
    team: Team;
    settings: CheckInSettings;
    gyms: Gym[];
}) {
    const { data, setData, patch, processing, errors } =
        useForm<CheckInSettings>(settings);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Settings',
            href: team.settings.widgetDefaults.url(currentTeam.slug),
        },
        {
            title: 'Check-In',
            href: team.settings.checkIn.url(currentTeam.slug),
        },
    ];

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        patch(CheckInSettingsController.update.url(currentTeam.slug), {
            preserveScroll: true,
        });
    }

    function toggleMethod(method: CheckInSettings['allowed_methods'][number]) {
        const methods = data.allowed_methods.includes(method)
            ? data.allowed_methods.filter((m) => m !== method)
            : [...data.allowed_methods, method];

        if (methods.length > 0) {
            setData('allowed_methods', methods);
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Check-In Settings - Team Settings" />

            <div className="space-y-6 p-4">
                <TeamSettingsLayout teamSlug={currentTeam.slug}>
                    <form onSubmit={handleSubmit} className="max-w-lg space-y-6">
                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="enabled"
                                checked={data.enabled}
                                onCheckedChange={(checked) =>
                                    setData('enabled', checked === true)
                                }
                            />
                            <Label htmlFor="enabled">
                                Enable check-in system
                            </Label>
                        </div>

                        <fieldset className="space-y-3">
                            <legend className="text-sm font-medium">
                                Allowed Check-In Methods
                            </legend>
                            {METHOD_OPTIONS.map((option) => (
                                <div
                                    key={option.value}
                                    className="flex items-center gap-2"
                                >
                                    <Checkbox
                                        id={`method-${option.value}`}
                                        checked={data.allowed_methods.includes(
                                            option.value,
                                        )}
                                        onCheckedChange={() =>
                                            toggleMethod(option.value)
                                        }
                                    />
                                    <Label
                                        htmlFor={`method-${option.value}`}
                                    >
                                        {option.label}
                                    </Label>
                                </div>
                            ))}
                            {errors.allowed_methods && (
                                <p className="text-sm text-destructive">
                                    {errors.allowed_methods}
                                </p>
                            )}
                        </fieldset>

                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="require_gym_selection"
                                checked={data.require_gym_selection}
                                onCheckedChange={(checked) =>
                                    setData(
                                        'require_gym_selection',
                                        checked === true,
                                    )
                                }
                            />
                            <Label htmlFor="require_gym_selection">
                                Require gym selection when checking in
                            </Label>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="prevent_duplicate_minutes">
                                Duplicate prevention window (minutes)
                            </Label>
                            <Input
                                id="prevent_duplicate_minutes"
                                type="number"
                                min={0}
                                max={1440}
                                value={data.prevent_duplicate_minutes}
                                onChange={(e) =>
                                    setData(
                                        'prevent_duplicate_minutes',
                                        parseInt(e.target.value) || 0,
                                    )
                                }
                                className="max-w-32"
                            />
                            <p className="text-xs text-muted-foreground">
                                Set to 0 to allow unlimited check-ins.
                            </p>
                            {errors.prevent_duplicate_minutes && (
                                <p className="text-sm text-destructive">
                                    {errors.prevent_duplicate_minutes}
                                </p>
                            )}
                        </div>

                        <fieldset className="space-y-3">
                            <legend className="text-sm font-medium">
                                Kiosk Mode
                            </legend>
                            <p className="text-xs text-muted-foreground">
                                Choose how the public kiosk page accepts
                                check-ins.
                            </p>
                            <Select
                                value={data.kiosk_mode}
                                onValueChange={(v) =>
                                    setData(
                                        'kiosk_mode',
                                        v as CheckInSettings['kiosk_mode'],
                                    )
                                }
                            >
                                <SelectTrigger className="max-w-64">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="camera">
                                        Camera (QR code scanning)
                                    </SelectItem>
                                    <SelectItem value="barcode_scanner">
                                        Hardware barcode scanner
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.kiosk_mode && (
                                <p className="text-sm text-destructive">
                                    {errors.kiosk_mode}
                                </p>
                            )}
                        </fieldset>

                        <Button disabled={processing}>Save Settings</Button>
                    </form>

                    {gyms.length > 0 && (
                        <div className="mt-8 border-t pt-6">
                            <h3 className="text-sm font-medium">
                                Kiosk URLs
                            </h3>
                            <p className="mb-3 text-xs text-muted-foreground">
                                Open these links on a tablet or computer at
                                each gym for self-service check-in.
                            </p>
                            <div className="space-y-2">
                                {gyms.map((gym) => (
                                    <div
                                        key={gym.id}
                                        className="flex items-center gap-3 rounded-md border px-3 py-2"
                                    >
                                        <span className="text-sm font-medium">
                                            {gym.name}
                                        </span>
                                        <code className="ml-auto text-xs text-muted-foreground">
                                            {window.location.origin}
                                            {kiosk.url({
                                                team: currentTeam.slug,
                                                gym: gym.slug,
                                            })}
                                        </code>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </TeamSettingsLayout>
            </div>
        </AppLayout>
    );
}
