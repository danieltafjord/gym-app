<?php

namespace App\Actions\Stripe;

use App\Enums\BillingPeriod;
use App\Enums\PlanType;
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

        if (
            ! $team->hasStripeAccount() ||
            (bool) config('stripe.dev_mode') ||
            blank(config('stripe.secret'))
        ) {
            return $plan;
        }

        $product = $this->stripe->createProduct($plan, $team->stripe_account_id);

        if ($plan->plan_type === PlanType::Recurring && $plan->hasYearlyPricingOption()) {
            $monthlyPrice = $this->stripe->createPrice(
                $plan,
                $product->id,
                $team->stripe_account_id,
                BillingPeriod::Monthly,
                $plan->price_cents,
            );

            $yearlyPrice = $this->stripe->createPrice(
                $plan,
                $product->id,
                $team->stripe_account_id,
                BillingPeriod::Yearly,
                $plan->yearly_price_cents,
            );

            $plan->update([
                'stripe_product_id' => $product->id,
                'stripe_price_id' => $monthlyPrice->id,
                'stripe_yearly_price_id' => $yearlyPrice->id,
            ]);

            return $plan->fresh();
        }

        $price = $this->stripe->createPrice($plan, $product->id, $team->stripe_account_id);

        $plan->update([
            'stripe_product_id' => $product->id,
            'stripe_price_id' => $price->id,
            'stripe_yearly_price_id' => null,
        ]);

        return $plan->fresh();
    }
}
