<?php

namespace App\Actions\MembershipPlan;

use App\Actions\Stripe\SyncPlanToStripe;
use App\Enums\AccessCodeStrategy;
use App\Enums\ActivationMode;
use App\Enums\BillingPeriod;
use App\Enums\PlanType;
use App\Models\MembershipPlan;
use App\Models\Team;

class CreateMembershipPlan
{
    public function __construct(private SyncPlanToStripe $syncPlanToStripe) {}

    /**
     * @param  array{name: string, description?: string|null, price_cents: int, yearly_price_cents?: int|null, billing_period?: string|null, plan_type?: string, access_duration_value?: int|null, access_duration_unit?: string|null, activation_mode?: string, requires_account?: bool, access_code_strategy?: string, max_entries?: int|null, features?: array<string>|null, sort_order?: int}  $data
     */
    public function handle(Team $team, array $data): MembershipPlan
    {
        $planType = $data['plan_type'] ?? PlanType::Recurring->value;

        $plan = MembershipPlan::create([
            'team_id' => $team->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price_cents' => $data['price_cents'],
            'yearly_price_cents' => $planType === PlanType::Recurring->value ? ($data['yearly_price_cents'] ?? null) : null,
            'billing_period' => $data['billing_period'] ?? BillingPeriod::Monthly->value,
            'plan_type' => $planType,
            'access_duration_value' => $planType === PlanType::OneTime->value ? ($data['access_duration_value'] ?? null) : null,
            'access_duration_unit' => $planType === PlanType::OneTime->value ? ($data['access_duration_unit'] ?? null) : null,
            'activation_mode' => $data['activation_mode'] ?? ActivationMode::Purchase->value,
            'requires_account' => $data['requires_account'] ?? false,
            'access_code_strategy' => $data['access_code_strategy'] ?? AccessCodeStrategy::RotateOnCheckIn->value,
            'max_entries' => $data['max_entries'] ?? null,
            'features' => $data['features'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        if ($team->hasStripeAccount()) {
            $plan = $this->syncPlanToStripe->handle($plan);
        }

        return $plan;
    }
}
