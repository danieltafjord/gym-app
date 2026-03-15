<?php

namespace App\Actions\Membership;

use App\Enums\MembershipStatus;
use App\Mail\MembershipPausedMail;
use App\Models\Membership;
use App\Services\StripeService;
use Illuminate\Support\Facades\Mail;

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

        $membership = $membership->fresh()->load('team', 'plan');

        Mail::to($membership->email)->queue(new MembershipPausedMail($membership));

        return $membership;
    }
}
