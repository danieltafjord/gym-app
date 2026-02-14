<?php

namespace App\Actions\MembershipPlan;

use App\Models\MembershipPlan;

class UpdateMembershipPlan
{
    /**
     * @param  array{name?: string, description?: string|null, price_cents?: int, billing_period?: string, features?: array<string>|null, is_active?: bool, sort_order?: int}  $data
     */
    public function handle(MembershipPlan $plan, array $data): MembershipPlan
    {
        $plan->update($data);

        return $plan->fresh();
    }
}
