<?php

namespace App\Actions\MembershipPlan;

use App\Actions\Stripe\SyncPlanToStripe;
use App\Models\MembershipPlan;
use App\Models\Team;

class CreateMembershipPlan
{
    public function __construct(private SyncPlanToStripe $syncPlanToStripe) {}

    /**
     * @param  array{name: string, description?: string|null, price_cents: int, billing_period: string, plan_type?: string, features?: array<string>|null, sort_order?: int}  $data
     */
    public function handle(Team $team, array $data): MembershipPlan
    {
        $plan = MembershipPlan::create([
            'team_id' => $team->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price_cents' => $data['price_cents'],
            'billing_period' => $data['billing_period'],
            'plan_type' => $data['plan_type'] ?? 'recurring',
            'features' => $data['features'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        if ($team->hasStripeAccount()) {
            $plan = $this->syncPlanToStripe->handle($plan);
        }

        return $plan;
    }
}
