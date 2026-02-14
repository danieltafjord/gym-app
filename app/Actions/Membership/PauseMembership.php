<?php

namespace App\Actions\Membership;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Services\StripeService;

class PauseMembership
{
    public function __construct(private StripeService $stripe) {}

    public function handle(Membership $membership): Membership
    {
        if ($membership->stripe_subscription_id) {
            $this->stripe->pauseSubscription($membership->stripe_subscription_id);
        }

        $membership->update([
            'status' => MembershipStatus::Paused,
        ]);

        return $membership->fresh();
    }
}
