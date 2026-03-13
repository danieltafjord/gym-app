<?php

namespace App\Actions\Membership;

use App\Enums\ActivationMode;
use App\Enums\BillingPeriod;
use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Carbon\CarbonImmutable;
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
        $startsAt = CarbonImmutable::instance($startsAt ?? now());
        $activateOnPurchase = $plan->activation_mode !== ActivationMode::FirstCheckIn;

        return Membership::create([
            'user_id' => $user?->id,
            'team_id' => $plan->team_id,
            'membership_plan_id' => $plan->id,
            'email' => $email,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'access_code' => Membership::generateAccessCode(),
            'status' => MembershipStatus::Active,
            'starts_at' => $activateOnPurchase ? $startsAt : null,
            'ends_at' => $activateOnPurchase ? $plan->calculateEndsAt($startsAt, $billingPeriod) : null,
            'activated_at' => $activateOnPurchase ? $startsAt : null,
            'entries_used' => 0,
            'stripe_subscription_id' => $stripeSubscriptionId,
            'stripe_payment_intent_id' => $stripePaymentIntentId,
            'stripe_status' => $stripeStatus,
        ]);
    }
}
