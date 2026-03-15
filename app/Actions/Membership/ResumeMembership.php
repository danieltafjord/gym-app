<?php

namespace App\Actions\Membership;

use App\Enums\MembershipStatus;
use App\Mail\MembershipResumedMail;
use App\Models\Membership;
use App\Services\StripeService;
use Illuminate\Support\Facades\Mail;

class ResumeMembership
{
    public function __construct(private StripeService $stripe) {}

    public function handle(Membership $membership): Membership
    {
        if ($membership->stripe_subscription_id) {
            $this->stripe->resumeSubscription($membership->stripe_subscription_id);
        }

        $membership->update([
            'status' => MembershipStatus::Active,
        ]);

        $membership = $membership->fresh()->load('team', 'plan');

        Mail::to($membership->email)->queue(new MembershipResumedMail($membership));

        return $membership;
    }
}
