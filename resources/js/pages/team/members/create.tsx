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
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, MembershipPlan, Team } from '@/types';
import MemberController from '@/actions/App/Http/Controllers/Team/MemberController';
import team from '@/routes/team';

export default function CreateMember({
    team: currentTeam,
    plans,
}: {
    team: Team;
    plans: MembershipPlan[];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: currentTeam.name,
            href: team.show(currentTeam.slug).url,
        },
        {
            title: 'Members',
            href: team.members.index(currentTeam.slug).url,
        },
        {
            title: 'Add Member',
            href: team.members.create(currentTeam.slug).url,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${currentTeam.name} - Add Member`} />

            <div className="mx-auto w-full max-w-2xl space-y-6 p-4">
                <Heading
                    title="Add Member"
                    description="Manually add a new member to your team."
                />

                <Form
                    {...MemberController.store.form(currentTeam.slug)}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="customer_name">Name</Label>
                                <Input
                                    id="customer_name"
                                    name="customer_name"
                                    required
                                    placeholder="Full name"
                                />
                                <InputError message={errors.customer_name} />
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        required
                                        placeholder="email@example.com"
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="customer_phone">
                                        Phone{' '}
                                        <span className="text-muted-foreground">(optional)</span>
                                    </Label>
                                    <Input
                                        id="customer_phone"
                                        name="customer_phone"
                                        placeholder="Phone number"
                                    />
                                    <InputError message={errors.customer_phone} />
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="membership_plan_id">Plan</Label>
                                    <Select name="membership_plan_id" required>
                                        <SelectTrigger id="membership_plan_id">
                                            <SelectValue placeholder="Select a plan" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {plans.map((plan) => (
                                                <SelectItem key={plan.id} value={String(plan.id)}>
                                                    {plan.name} — ${plan.price_formatted}/{plan.billing_period}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.membership_plan_id} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="starts_at">
                                        Start Date{' '}
                                        <span className="text-muted-foreground">(defaults to today)</span>
                                    </Label>
                                    <Input
                                        id="starts_at"
                                        name="starts_at"
                                        type="date"
                                    />
                                    <InputError message={errors.starts_at} />
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>
                                    Add Member
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
