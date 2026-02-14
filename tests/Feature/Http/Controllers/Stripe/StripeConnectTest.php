<?php

use App\Models\Team;
use App\Models\User;
use App\Services\StripeService;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('redirects to stripe onboarding for team owner', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $mockStripe = Mockery::mock(StripeService::class);
    $mockStripe->shouldReceive('createConnectAccount')
        ->once()
        ->andReturn(\Stripe\Account::constructFrom(['id' => 'acct_test_123']));
    $mockStripe->shouldReceive('createAccountLink')
        ->once()
        ->andReturn(\Stripe\AccountLink::constructFrom(['url' => 'https://connect.stripe.com/setup/test']));

    $this->app->instance(StripeService::class, $mockStripe);

    $this->actingAs($user)
        ->get(route('team.stripe.onboard', $team))
        ->assertRedirect('https://connect.stripe.com/setup/test');

    expect($team->fresh()->stripe_account_id)->toBe('acct_test_123');
});

it('reuses existing stripe account on re-onboard', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create([
        'owner_id' => $user->id,
        'stripe_account_id' => 'acct_existing_123',
    ]);

    $mockStripe = Mockery::mock(StripeService::class);
    $mockStripe->shouldNotReceive('createConnectAccount');
    $mockStripe->shouldReceive('createAccountLink')
        ->once()
        ->andReturn(\Stripe\AccountLink::constructFrom(['url' => 'https://connect.stripe.com/setup/existing']));

    $this->app->instance(StripeService::class, $mockStripe);

    $this->actingAs($user)
        ->get(route('team.stripe.onboard', $team))
        ->assertRedirect('https://connect.stripe.com/setup/existing');
});

it('handles successful onboarding return', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create([
        'owner_id' => $user->id,
        'stripe_account_id' => 'acct_test_123',
    ]);

    $mockStripe = Mockery::mock(StripeService::class);
    $mockStripe->shouldReceive('retrieveAccount')
        ->once()
        ->with('acct_test_123')
        ->andReturn(\Stripe\Account::constructFrom([
            'id' => 'acct_test_123',
            'charges_enabled' => true,
            'details_submitted' => true,
        ]));

    $this->app->instance(StripeService::class, $mockStripe);

    $this->actingAs($user)
        ->get(route('team.stripe.return', $team))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('team/stripe-return')
            ->where('onboardingComplete', true)
        );

    expect($team->fresh()->stripe_onboarding_complete)->toBeTrue();
});

it('handles incomplete onboarding return', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create([
        'owner_id' => $user->id,
        'stripe_account_id' => 'acct_test_123',
    ]);

    $mockStripe = Mockery::mock(StripeService::class);
    $mockStripe->shouldReceive('retrieveAccount')
        ->once()
        ->andReturn(\Stripe\Account::constructFrom([
            'id' => 'acct_test_123',
            'charges_enabled' => false,
            'details_submitted' => false,
        ]));

    $this->app->instance(StripeService::class, $mockStripe);

    $this->actingAs($user)
        ->get(route('team.stripe.return', $team))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('team/stripe-return')
            ->where('onboardingComplete', false)
        );

    expect($team->fresh()->stripe_onboarding_complete)->toBeFalse();
});

it('redirects to stripe express dashboard', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create([
        'owner_id' => $user->id,
        'stripe_account_id' => 'acct_test_123',
        'stripe_onboarding_complete' => true,
    ]);

    $mockStripe = Mockery::mock(StripeService::class);
    $mockStripe->shouldReceive('createLoginLink')
        ->once()
        ->with('acct_test_123')
        ->andReturn(\Stripe\LoginLink::constructFrom(['url' => 'https://connect.stripe.com/express/test']));

    $this->app->instance(StripeService::class, $mockStripe);

    $this->actingAs($user)
        ->get(route('team.stripe.dashboard', $team))
        ->assertRedirect('https://connect.stripe.com/express/test');
});

it('returns 404 for dashboard when stripe not connected', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('team.stripe.dashboard', $team))
        ->assertNotFound();
});

it('denies stripe access to non-owner', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($other)
        ->get(route('team.stripe.onboard', $team))
        ->assertForbidden();
});
