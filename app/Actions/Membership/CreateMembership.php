<?php

namespace App\Actions\Membership;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Support\Str;

class CreateMembership
{
    public function handle(
        ?User $user,
        MembershipPlan $plan,
        string $email,
        string $customerName,
        ?string $customerPhone = null,
        ?string $stripeSubscriptionId = null,
        ?string $stripePaymentIntentId = null,
        ?string $stripeStatus = null,
    ): Membership {
        return Membership::create([
            'user_id' => $user?->id,
            'team_id' => $plan->team_id,
            'membership_plan_id' => $plan->id,
            'email' => $email,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'access_code' => $this->generateAccessCode(),
            'status' => MembershipStatus::Active,
            'starts_at' => now(),
            'ends_at' => $this->calculateEndDate($plan),
            'stripe_subscription_id' => $stripeSubscriptionId,
            'stripe_payment_intent_id' => $stripePaymentIntentId,
            'stripe_status' => $stripeStatus,
        ]);
    }

    private function generateAccessCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Membership::where('access_code', $code)->exists());

        return $code;
    }

    private function calculateEndDate(MembershipPlan $plan): \DateTimeInterface
    {
        return match ($plan->billing_period) {
            \App\Enums\BillingPeriod::Weekly => now()->addWeek(),
            \App\Enums\BillingPeriod::Monthly => now()->addMonth(),
            \App\Enums\BillingPeriod::Quarterly => now()->addMonths(3),
            \App\Enums\BillingPeriod::Yearly => now()->addYear(),
        };
    }
}
