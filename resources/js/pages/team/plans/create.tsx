import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, Team } from '@/types';
import MembershipPlanController from '@/actions/App/Http/Controllers/Team/MembershipPlanController';
import team from '@/routes/team';
import { useRef } from 'react';

export default function CreatePlan({ team: currentTeam }: { team: Team }) {
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
            title: 'Create',
            href: team.plans.create(currentTeam.slug).url,
        },
    ];

    const monthlyPriceCentsRef = useRef<HTMLInputElement>(null);
    const yearlyPriceCentsRef = useRef<HTMLInputElement>(null);
    const priceCurrencyCode = currentTeam.default_currency ?? 'USD';

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${currentTeam.name} - Create Plan`} />

            <div className="mx-auto w-full max-w-2xl space-y-6 p-4">
                <Heading
                    title="Create Plan"
                    description="Set up a new membership plan for your team."
                />

                <Form
                    {...MembershipPlanController.store.form(currentTeam.slug)}
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
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Plan Name</Label>
                                <Input
                                    id="name"
                                    name="name"
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
                                        placeholder="0.00"
                                        data-monthly-price-dollars=""
                                    />
                                    <input
                                        ref={monthlyPriceCentsRef}
                                        type="hidden"
                                        name="price_cents"
                                        defaultValue="0"
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
                                        placeholder="0.00"
                                        data-yearly-price-dollars=""
                                    />
                                    <input
                                        ref={yearlyPriceCentsRef}
                                        type="hidden"
                                        name="yearly_price_cents"
                                        defaultValue=""
                                    />
                                    <InputError message={errors.yearly_price_cents} />
                                </div>
                            </div>

                            <input type="hidden" name="billing_period" value="monthly" />

                            <div className="grid gap-2">
                                <Label htmlFor="features">
                                    Features{' '}
                                    <span className="text-muted-foreground">(comma-separated)</span>
                                </Label>
                                <Input
                                    id="features"
                                    name="features"
                                    placeholder="e.g. Unlimited access, Personal trainer, Sauna"
                                />
                                <InputError message={errors.features} />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>
                                    Create Plan
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
