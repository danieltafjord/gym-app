import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import WidgetPreview, { type PreviewView } from '@/components/widget-preview';
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
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import AppLayout from '@/layouts/app-layout';
import GymSettingsLayout from '@/layouts/gym/gym-settings-layout';
import type { BreadcrumbItem, Gym, MembershipPlan, Team, WidgetSettings } from '@/types';
import WidgetSettingsController from '@/actions/App/Http/Controllers/Team/WidgetSettingsController';
import team from '@/routes/team';

const FONT_OPTIONS = [
    { value: 'system-ui, -apple-system, sans-serif', label: 'System Default' },
    { value: 'Inter, sans-serif', label: 'Inter' },
    { value: 'Georgia, serif', label: 'Georgia' },
    { value: '"Helvetica Neue", Helvetica, sans-serif', label: 'Helvetica' },
    { value: '"Segoe UI", sans-serif', label: 'Segoe UI' },
];

export default function WidgetSettingsPage({
    team: currentTeam,
    gym,
    settings,
    plans,
    embedUrl,
    hasOverrides,
}: {
    team: Team;
    gym: Gym;
    settings: WidgetSettings;
    plans: MembershipPlan[];
    embedUrl: string;
    hasOverrides: boolean;
}) {
    const [copied, setCopied] = useState(false);
    const [previewView, setPreviewView] = useState<PreviewView>('plans');

    const { data, setData, patch, processing, errors } = useForm<WidgetSettings>(settings);

    const breadcrumbs: BreadcrumbItem[] = [
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
            href: team.gyms.settings.general.url({
                team: currentTeam.slug,
                gym: gym.slug,
            }),
        },
        {
            title: 'Settings',
            href: team.gyms.settings.general.url({
                team: currentTeam.slug,
                gym: gym.slug,
            }),
        },
    ];

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        patch(
            WidgetSettingsController.update.url({
                team: currentTeam.slug,
                gym: gym.slug,
            }),
            { preserveScroll: true },
        );
    }

    function handleReset() {
        if (!confirm('Reset this gym\'s widget settings to team defaults? This cannot be undone.')) {
            return;
        }

        router.delete(
            WidgetSettingsController.destroy.url({
                team: currentTeam.slug,
                gym: gym.slug,
            }),
            { preserveScroll: true },
        );
    }

    const embedCode = `<div data-gymapp-widget data-team="${currentTeam.slug}" data-gym="${gym.slug}"></div>\n<script src="${embedUrl}" async></script>`;

    function copyEmbedCode() {
        navigator.clipboard.writeText(embedCode).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${gym.name} - Widget Settings`} />

            <div className="mx-auto w-full max-w-6xl space-y-6 p-4">
                <GymSettingsLayout
                    teamSlug={currentTeam.slug}
                    gymSlug={gym.slug}
                    gymName={gym.name}
                >
                    <Heading
                        title="Widget"
                        description={`Configure the embeddable membership widget for ${gym.name}.`}
                    />

                    <div className="grid gap-8 lg:grid-cols-2">
                        {/* Settings Form */}
                        <form onSubmit={handleSubmit} className="space-y-8">
                            {/* Colors */}
                            <fieldset className="space-y-4">
                                <legend className="text-sm font-medium">Colors</legend>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <ColorField
                                        label="Primary Color"
                                        value={data.primary_color}
                                        onChange={(v) => setData('primary_color', v)}
                                        error={errors.primary_color}
                                    />
                                    <ColorField
                                        label="Background Color"
                                        value={data.background_color}
                                        onChange={(v) => setData('background_color', v)}
                                        error={errors.background_color}
                                    />
                                    <ColorField
                                        label="Text Color"
                                        value={data.text_color}
                                        onChange={(v) => setData('text_color', v)}
                                        error={errors.text_color}
                                    />
                                    <ColorField
                                        label="Secondary Text"
                                        value={data.secondary_text_color}
                                        onChange={(v) => setData('secondary_text_color', v)}
                                        error={errors.secondary_text_color}
                                    />
                                    <ColorField
                                        label="Card Border"
                                        value={data.card_border_color}
                                        onChange={(v) => setData('card_border_color', v)}
                                        error={errors.card_border_color}
                                    />
                                    <ColorField
                                        label="Button Text"
                                        value={data.button_text_color}
                                        onChange={(v) => setData('button_text_color', v)}
                                        error={errors.button_text_color}
                                    />
                                    <ColorField
                                        label="Input Border"
                                        value={data.input_border_color}
                                        onChange={(v) => setData('input_border_color', v)}
                                        error={errors.input_border_color}
                                    />
                                    <ColorField
                                        label="Input Background"
                                        value={data.input_background_color}
                                        onChange={(v) => setData('input_background_color', v)}
                                        error={errors.input_background_color}
                                    />
                                </div>
                            </fieldset>

                            {/* Typography */}
                            <fieldset className="space-y-4">
                                <legend className="text-sm font-medium">Typography</legend>
                                <div className="grid gap-2">
                                    <Label htmlFor="font_family">Font Family</Label>
                                    <Select
                                        value={data.font_family}
                                        onValueChange={(v) => setData('font_family', v)}
                                    >
                                        <SelectTrigger id="font_family">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {FONT_OPTIONS.map((font) => (
                                                <SelectItem key={font.value} value={font.value}>
                                                    {font.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.font_family} />
                                </div>
                            </fieldset>

                            {/* Layout */}
                            <fieldset className="space-y-4">
                                <legend className="text-sm font-medium">Layout</legend>
                                <div className="grid gap-4 sm:grid-cols-3">
                                    <div className="grid gap-2">
                                        <Label htmlFor="card_border_radius">Card Radius</Label>
                                        <Input
                                            id="card_border_radius"
                                            type="number"
                                            min={0}
                                            max={32}
                                            value={data.card_border_radius}
                                            onChange={(e) =>
                                                setData('card_border_radius', parseInt(e.target.value) || 0)
                                            }
                                        />
                                        <InputError message={errors.card_border_radius} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="button_border_radius">Button Radius</Label>
                                        <Input
                                            id="button_border_radius"
                                            type="number"
                                            min={0}
                                            max={32}
                                            value={data.button_border_radius}
                                            onChange={(e) =>
                                                setData('button_border_radius', parseInt(e.target.value) || 0)
                                            }
                                        />
                                        <InputError message={errors.button_border_radius} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="input_border_radius">Input Radius</Label>
                                        <Input
                                            id="input_border_radius"
                                            type="number"
                                            min={0}
                                            max={32}
                                            value={data.input_border_radius}
                                            onChange={(e) =>
                                                setData('input_border_radius', parseInt(e.target.value) || 0)
                                            }
                                        />
                                        <InputError message={errors.input_border_radius} />
                                    </div>
                                </div>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="padding">Padding</Label>
                                        <Input
                                            id="padding"
                                            type="number"
                                            min={0}
                                            max={48}
                                            value={data.padding}
                                            onChange={(e) =>
                                                setData('padding', parseInt(e.target.value) || 0)
                                            }
                                        />
                                        <InputError message={errors.padding} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="columns">Columns</Label>
                                        <Input
                                            id="columns"
                                            type="number"
                                            min={1}
                                            max={4}
                                            value={data.columns}
                                            onChange={(e) =>
                                                setData('columns', parseInt(e.target.value) || 1)
                                            }
                                        />
                                        <InputError message={errors.columns} />
                                    </div>
                                </div>
                            </fieldset>

                            {/* Content */}
                            <fieldset className="space-y-4">
                                <legend className="text-sm font-medium">Content</legend>
                                <div className="grid gap-2">
                                    <Label htmlFor="button_text">Button Text</Label>
                                    <Input
                                        id="button_text"
                                        value={data.button_text}
                                        onChange={(e) => setData('button_text', e.target.value)}
                                        maxLength={50}
                                    />
                                    <InputError message={errors.button_text} />
                                </div>
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="show_features"
                                        checked={data.show_features}
                                        onCheckedChange={(checked) =>
                                            setData('show_features', checked === true)
                                        }
                                    />
                                    <Label htmlFor="show_features">Show features list</Label>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="show_description"
                                        checked={data.show_description}
                                        onCheckedChange={(checked) =>
                                            setData('show_description', checked === true)
                                        }
                                    />
                                    <Label htmlFor="show_description">Show plan description</Label>
                                </div>
                            </fieldset>

                            {/* Success Page */}
                            <fieldset className="space-y-4">
                                <legend className="text-sm font-medium">Success Page</legend>
                                <div className="grid gap-2">
                                    <Label htmlFor="success_heading">Heading</Label>
                                    <Input
                                        id="success_heading"
                                        value={data.success_heading}
                                        onChange={(e) => setData('success_heading', e.target.value)}
                                        maxLength={100}
                                    />
                                    <InputError message={errors.success_heading} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="success_message">Message</Label>
                                    <Input
                                        id="success_message"
                                        value={data.success_message}
                                        onChange={(e) => setData('success_message', e.target.value)}
                                        maxLength={255}
                                    />
                                    <InputError message={errors.success_message} />
                                </div>
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="show_access_code"
                                        checked={data.show_access_code}
                                        onCheckedChange={(checked) =>
                                            setData('show_access_code', checked === true)
                                        }
                                    />
                                    <Label htmlFor="show_access_code">Show access code</Label>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="show_success_details"
                                        checked={data.show_success_details}
                                        onCheckedChange={(checked) =>
                                            setData('show_success_details', checked === true)
                                        }
                                    />
                                    <Label htmlFor="show_success_details">Show membership details</Label>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="show_cta_card"
                                        checked={data.show_cta_card}
                                        onCheckedChange={(checked) =>
                                            setData('show_cta_card', checked === true)
                                        }
                                    />
                                    <Label htmlFor="show_cta_card">Show create account card</Label>
                                </div>
                            </fieldset>

                            <div className="flex items-center gap-3">
                                <Button disabled={processing}>Save Settings</Button>
                                {hasOverrides && (
                                    <Button type="button" variant="outline" onClick={handleReset}>
                                        Reset to Team Defaults
                                    </Button>
                                )}
                            </div>
                        </form>

                        {/* Preview & Embed Code */}
                        <div className="space-y-6">
                            <div className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <h3 className="text-sm font-medium">Live Preview</h3>
                                    <ToggleGroup
                                        type="single"
                                        value={previewView}
                                        onValueChange={(v) => {
                                            if (v) setPreviewView(v as PreviewView);
                                        }}
                                        variant="outline"
                                        size="sm"
                                    >
                                        <ToggleGroupItem value="plans">Plans</ToggleGroupItem>
                                        <ToggleGroupItem value="checkout">Checkout</ToggleGroupItem>
                                        <ToggleGroupItem value="success">Success</ToggleGroupItem>
                                    </ToggleGroup>
                                </div>
                                <div className="overflow-hidden rounded-lg border">
                                    <WidgetPreview settings={data} plans={plans} view={previewView} />
                                </div>
                            </div>

                            <div className="space-y-3">
                                <h3 className="text-sm font-medium">Embed Code</h3>
                                <p className="text-sm text-muted-foreground">
                                    Copy this code and paste it into your website's HTML.
                                </p>
                                <div className="relative">
                                    <pre className="overflow-x-auto rounded-lg border bg-muted/50 p-4 text-xs">
                                        <code>{embedCode}</code>
                                    </pre>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        className="absolute top-2 right-2"
                                        onClick={copyEmbedCode}
                                    >
                                        {copied ? 'Copied!' : 'Copy'}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </GymSettingsLayout>
            </div>
        </AppLayout>
    );
}

function ColorField({
    label,
    value,
    onChange,
    error,
}: {
    label: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
}) {
    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            <div className="flex items-center gap-2">
                <input
                    type="color"
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    className="h-9 w-12 cursor-pointer rounded border p-0.5"
                />
                <Input
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    className="flex-1"
                    maxLength={7}
                />
            </div>
            <InputError message={error} />
        </div>
    );
}
