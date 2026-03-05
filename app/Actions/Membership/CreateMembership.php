<?php

namespace App\Actions\Membership;

use App\Enums\BillingPeriod;
use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use DateTimeInterface;

class CreateMembership
{
    public function handle(
        ?User $user,
        MembershipPlan $plan,
        string $email,
        string $customerName,
        ?string $customerPhone = null,
        ?DateTimeInterface $startsAt = null,
        ?string $stripeSubscriptionId = null,
        ?string $stripePaymentIntentId = null,
        ?string $stripeStatus = null,
        ?BillingPeriod $billingPeriod = null,
    ): Membership {
        $startsAt = $startsAt ?? now();

        return Membership::create([
            'user_id' => $user?->id,
            'team_id' => $plan->team_id,
            'membership_plan_id' => $plan->id,
            'email' => $email,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'access_code' => Membership::generateAccessCode(),
            'status' => MembershipStatus::Active,
            'starts_at' => $startsAt,
            'ends_at' => $this->calculateEndDate($plan, $startsAt, $billingPeriod),
            'stripe_subscription_id' => $stripeSubscriptionId,
            'stripe_payment_intent_id' => $stripePaymentIntentId,
            'stripe_status' => $stripeStatus,
        ]);
    }

    private function calculateEndDate(
        MembershipPlan $plan,
        DateTimeInterface $startsAt,
        ?BillingPeriod $billingPeriod = null,
    ): DateTimeInterface {
        $start = \Carbon\Carbon::instance($startsAt);
        $effectiveBillingPeriod = $billingPeriod ?? $plan->billing_period;

        return match ($effectiveBillingPeriod) {
            BillingPeriod::Weekly => $start->copy()->addWeek(),
            BillingPeriod::Monthly => $start->copy()->addMonth(),
            BillingPeriod::Quarterly => $start->copy()->addMonths(3),
            BillingPeriod::Yearly => $start->copy()->addYear(),
        };
    }
}
