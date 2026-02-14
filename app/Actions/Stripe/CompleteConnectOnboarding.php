<?php

namespace App\Actions\Stripe;

use App\Models\Team;
use App\Services\StripeService;

class CompleteConnectOnboarding
{
    public function __construct(private StripeService $stripe) {}

    /**
     * Check Stripe account status and update onboarding flag.
     */
    public function handle(Team $team): bool
    {
        if (! $team->stripe_account_id) {
            return false;
        }

        $account = $this->stripe->retrieveAccount($team->stripe_account_id);

        $isComplete = $account->charges_enabled && $account->details_submitted;

        $team->update(['stripe_onboarding_complete' => $isComplete]);

        return $isComplete;
    }
}
