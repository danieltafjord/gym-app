<?php

namespace App\Actions\Stripe;

use App\Models\Team;
use App\Services\StripeService;

class CreateConnectAccount
{
    public function __construct(private StripeService $stripe) {}

    /**
     * Create a Stripe Express Connect account and return the onboarding URL.
     */
    public function handle(Team $team, string $returnUrl, string $refreshUrl): string
    {
        if (! $team->stripe_account_id) {
            $account = $this->stripe->createConnectAccount($team);
            $team->update(['stripe_account_id' => $account->id]);
        }

        $link = $this->stripe->createAccountLink(
            $team->stripe_account_id,
            $returnUrl,
            $refreshUrl,
        );

        return $link->url;
    }
}
