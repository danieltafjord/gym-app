<?php

namespace App\Actions\Membership;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Services\StripeService;

class CancelMembership
{
    public function __construct(private StripeService $stripe) {}

    public function handle(Membership $membership): Membership
    {
        if ($membership->stripe_subscription_id) {
            $this->stripe->cancelSubscription($membership->stripe_subscription_id);
        }

        $membership->update([
            'status' => MembershipStatus::Cancelled,
            'cancelled_at' => now(),
            'stripe_status' => $membership->stripe_subscription_id ? 'canceled' : null,
        ]);

        return $membership->fresh();
    }
}
