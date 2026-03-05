import { Form, Head, router, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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
    const { errors: pageErrors } = usePage<{ errors: Record<string, string> }>().props;

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

    const monthlyPriceCentsRef = useRef<HTMLInputElement>(null);
    const yearlyPriceCentsRef = useRef<HTMLInputElement>(null);
    const monthlyPriceDollars = (plan.price_cents / 100).toFixed(2);
    const yearlyPriceDollars = plan.yearly_price_cents === null
        ? ''
        : (plan.yearly_price_cents / 100).toFixed(2);
    const priceCurrencyCode = currentTeam.default_currency ?? 'USD';

    const handleDeletePlan = () => {
        if (!confirm(`Delete "${plan.name}"? This action cannot be undone.`)) {
            return;
        }

        router.delete(
            MembershipPlanController.destroy.url({
                team: currentTeam.slug,
                plan: plan.id,
            }),
            { preserveScroll: true },
        );
    };

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
                        const monthlyPriceInput = form.querySelector<HTMLInputElement>('[data-monthly-price-dollars]');
                        if (monthlyPriceInput && monthlyPriceCentsRef.current) {
                            const dollars = parseFloat(monthlyPriceInput.value) || 0;
                            monthlyPriceCentsRef.current.value = Math.round(dollars * 100).toString();
                        }

                        const yearlyPriceInput = form.querySelector<HTMLInputElement>('[data-yearly-price-dollars]');
                        if (yearlyPriceInput && yearlyPriceCentsRef.current) {
                            if (yearlyPriceInput.value.trim() === '') {
                                yearlyPriceCentsRef.current.value = '';
                            } else {
                                const dollars = parseFloat(yearlyPriceInput.value) || 0;
                                yearlyPriceCentsRef.current.value = Math.round(dollars * 100).toString();
                            }
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
                                    <Label htmlFor="monthly_price_dollars">
                                        Monthly Price ({priceCurrencyCode})
                                    </Label>
                                    <Input
                                        id="monthly_price_dollars"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        defaultValue={monthlyPriceDollars}
                                        placeholder="0.00"
                                        data-monthly-price-dollars=""
                                    />
                                    <input
                                        ref={monthlyPriceCentsRef}
                                        type="hidden"
                                        name="price_cents"
                                        defaultValue={plan.price_cents.toString()}
                                    />
                                    <InputError message={errors.price_cents} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="yearly_price_dollars">
                                        Yearly Price ({priceCurrencyCode}){' '}
                                        <span className="text-muted-foreground">(optional)</span>
                                    </Label>
                                    <Input
                                        id="yearly_price_dollars"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        defaultValue={yearlyPriceDollars}
                                        placeholder="0.00"
                                        data-yearly-price-dollars=""
                                    />
                                    <input
                                        ref={yearlyPriceCentsRef}
                                        type="hidden"
                                        name="yearly_price_cents"
                                        defaultValue={plan.yearly_price_cents?.toString() ?? ''}
                                    />
                                    <InputError message={errors.yearly_price_cents} />
                                </div>
                            </div>

                            <input type="hidden" name="billing_period" value={plan.billing_period} />

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

                <div className="flex items-center justify-between rounded-lg border border-destructive/20 bg-destructive/5 p-4">
                    <div>
                        <p className="font-medium">Delete Plan</p>
                        <p className="text-sm text-muted-foreground">
                            This will permanently remove this plan.
                        </p>
                        <InputError message={pageErrors.delete_plan} />
                    </div>
                    <Button variant="destructive" onClick={handleDeletePlan}>
                        Delete
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
