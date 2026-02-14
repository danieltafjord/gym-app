<?php

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Services\StripeService;

beforeEach(function () {
    $mockStripe = Mockery::mock(StripeService::class);
    $mockStripe->shouldReceive('constructWebhookEvent')
        ->andReturnUsing(function (string $payload) {
            return \Stripe\Event::constructFrom(json_decode($payload, true));
        });

    $this->app->instance(StripeService::class, $mockStripe);
});

it('handles invoice.payment_succeeded webhook', function () {
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_test_123',
        'stripe_status' => 'incomplete',
    ]);

    $this->postJson(route('stripe.webhook'), [
        'id' => 'evt_test',
        'object' => 'event',
        'type' => 'invoice.payment_succeeded',
        'data' => [
            'object' => [
                'object' => 'invoice',
                'subscription' => 'sub_test_123',
            ],
        ],
    ], ['Stripe-Signature' => 'test_sig'])
        ->assertSuccessful();

    $membership->refresh();
    expect($membership->status)->toBe(MembershipStatus::Active);
    expect($membership->stripe_status)->toBe('active');
});

it('handles invoice.payment_failed webhook', function () {
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_fail_123',
        'stripe_status' => 'active',
    ]);

    $this->postJson(route('stripe.webhook'), [
        'id' => 'evt_test',
        'object' => 'event',
        'type' => 'invoice.payment_failed',
        'data' => [
            'object' => [
                'object' => 'invoice',
                'subscription' => 'sub_fail_123',
            ],
        ],
    ], ['Stripe-Signature' => 'test_sig'])
        ->assertSuccessful();

    $membership->refresh();
    expect($membership->stripe_status)->toBe('past_due');
});

it('handles customer.subscription.deleted webhook', function () {
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
        'stripe_subscription_id' => 'sub_del_123',
        'status' => MembershipStatus::Active,
    ]);

    $this->postJson(route('stripe.webhook'), [
        'id' => 'evt_test',
        'object' => 'event',
        'type' => 'customer.subscription.deleted',
        'data' => [
            'object' => [
                'object' => 'subscription',
                'id' => 'sub_del_123',
            ],
        ],
    ], ['Stripe-Signature' => 'test_sig'])
        ->assertSuccessful();

    $membership->refresh();
    expect($membership->status)->toBe(MembershipStatus::Cancelled);
    expect($membership->stripe_status)->toBe('canceled');
    expect($membership->cancelled_at)->not->toBeNull();
});

it('handles account.updated connect webhook', function () {
    $team = Team::factory()->create([
        'stripe_account_id' => 'acct_connect_123',
        'stripe_onboarding_complete' => false,
    ]);

    $this->postJson(route('stripe.connect-webhook'), [
        'id' => 'evt_test',
        'object' => 'event',
        'type' => 'account.updated',
        'data' => [
            'object' => [
                'object' => 'account',
                'id' => 'acct_connect_123',
                'charges_enabled' => true,
                'details_submitted' => true,
            ],
        ],
    ], ['Stripe-Signature' => 'test_sig'])
        ->assertSuccessful();

    expect($team->fresh()->stripe_onboarding_complete)->toBeTrue();
});

it('returns 200 for unhandled event types', function () {
    $this->postJson(route('stripe.webhook'), [
        'id' => 'evt_test',
        'object' => 'event',
        'type' => 'some.unhandled.event',
        'data' => [
            'object' => [
                'object' => 'balance',
            ],
        ],
    ], ['Stripe-Signature' => 'test_sig'])
        ->assertSuccessful();
});
