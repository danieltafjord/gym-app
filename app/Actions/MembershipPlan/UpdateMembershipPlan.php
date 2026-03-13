<?php

namespace App\Actions\MembershipPlan;

use App\Enums\PlanType;
use App\Models\MembershipPlan;

class UpdateMembershipPlan
{
    /**
     * @param  array{name?: string, description?: string|null, price_cents?: int, yearly_price_cents?: int|null, billing_period?: string|null, plan_type?: string, access_duration_value?: int|null, access_duration_unit?: string|null, activation_mode?: string, requires_account?: bool, access_code_strategy?: string, max_entries?: int|null, features?: array<string>|null, is_active?: bool, sort_order?: int}  $data
     */
    public function handle(MembershipPlan $plan, array $data): MembershipPlan
    {
        $planType = $data['plan_type'] ?? $plan->plan_type->value;

        if ($planType === PlanType::Recurring->value) {
            $data['access_duration_value'] = null;
            $data['access_duration_unit'] = null;
            $data['max_entries'] = null;
        }

        if ($planType === PlanType::OneTime->value) {
            $data['yearly_price_cents'] = null;
        }

        $plan->update($data);

        return $plan->fresh();
    }
}
