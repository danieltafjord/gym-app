<?php

namespace App\Actions\Stripe;

use App\Models\MembershipPlan;
use App\Services\StripeService;

class SyncPlanToStripe
{
    public function __construct(private StripeService $stripe) {}

    /**
     * Create a Stripe Product and Price for the given membership plan.
     */
    public function handle(MembershipPlan $plan): MembershipPlan
    {
        $team = $plan->team;

        if (! $team->hasStripeAccount()) {
            return $plan;
        }

        $product = $this->stripe->createProduct($plan, $team->stripe_account_id);
        $price = $this->stripe->createPrice($plan, $product->id, $team->stripe_account_id);

        $plan->update([
            'stripe_product_id' => $product->id,
            'stripe_price_id' => $price->id,
        ]);

        return $plan->fresh();
    }
}
