import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, MembershipPlan, Team } from '@/types';
import MembershipPlanController from '@/actions/App/Http/Controllers/Team/MembershipPlanController';
import team from '@/routes/team';
import { useRef } from 'react';

export default function EditPlan({
    team: currentTeam,
    plan,
}: {
    team: Team;
    plan: MembershipPlan;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Plans',
            href: team.plans.index(currentTeam.slug).url,
        },
        {
            title: plan.name,
            href: team.plans.edit({
                team: currentTeam.slug,
                plan: plan.id,
            }).url,
        },
    ];

    const priceCentsRef = useRef<HTMLInputElement>(null);
    const priceDollars = (plan.price_cents / 100).toFixed(2);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${plan.name}`} />

            <div className="mx-auto w-full max-w-2xl space-y-6 p-4">
                <Heading
                    title="Edit Plan"
                    description={`Update details for ${plan.name}.`}
                />

                <Form
                    {...MembershipPlanController.update.form.patch({
                        team: currentTeam.slug,
                        plan: plan.id,
                    })}
                    onSubmit={(e) => {
                        const form = e.currentTarget;
                        const priceInput = form.querySelector<HTMLInputElement>('[data-price-dollars]');
                        if (priceInput && priceCentsRef.current) {
                            const dollars = parseFloat(priceInput.value) || 0;
                            priceCentsRef.current.value = Math.round(dollars * 100).toString();
                        }
                    }}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Plan Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={plan.name}
                                    required
                                    placeholder="e.g. Basic, Premium, VIP"
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
                                    defaultValue={plan.description ?? ''}
                                    placeholder="Describe what's included in this plan"
                                    rows={3}
                                />
                                <InputError message={errors.description} />
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="price_dollars">Price ($)</Label>
                                    <Input
                                        id="price_dollars"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        defaultValue={priceDollars}
                                        placeholder="0.00"
                                        data-price-dollars=""
                                    />
                                    <input
                                        ref={priceCentsRef}
                                        type="hidden"
                                        name="price_cents"
                                        defaultValue={plan.price_cents.toString()}
                                    />
                                    <InputError message={errors.price_cents} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="billing_period">Billing Period</Label>
                                    <Select name="billing_period" defaultValue={plan.billing_period}>
                                        <SelectTrigger id="billing_period">
                                            <SelectValue placeholder="Select billing period" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="weekly">Weekly</SelectItem>
                                            <SelectItem value="monthly">Monthly</SelectItem>
                                            <SelectItem value="quarterly">Quarterly</SelectItem>
                                            <SelectItem value="yearly">Yearly</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.billing_period} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="features">
                                    Features{' '}
                                    <span className="text-muted-foreground">(comma-separated)</span>
                                </Label>
                                <Input
                                    id="features"
                                    name="features"
                                    defaultValue={plan.features?.join(', ') ?? ''}
                                    placeholder="e.g. Unlimited access, Personal trainer, Sauna"
                                />
                                <InputError message={errors.features} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>
                                    Save Changes
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
