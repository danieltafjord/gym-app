<?php

use App\Enums\PlanType;
use App\Models\Gym;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use App\Services\StripeService;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config()->set('stripe.dev_mode', false);
});

it('shows checkout page for authenticated user', function () {
    $team = Team::factory()->create([
        'is_active' => true,
        'stripe_account_id' => 'acct_test',
        'stripe_onboarding_complete' => true,
    ]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('public.checkout', [
            'team' => $team,
            'gym' => $gym->slug,
            'membershipPlan' => $plan,
        ]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/checkout')
            ->has('team')
            ->has('gym')
            ->has('plan')
            ->has('stripeKey')
        );
});

it('returns 404 for checkout when stripe not connected', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('public.checkout', [
            'team' => $team,
            'gym' => $gym->slug,
            'membershipPlan' => $plan,
        ]))
        ->assertNotFound();
});

it('allows guest checkout without authentication', function () {
    $team = Team::factory()->create([
        'is_active' => true,
        'stripe_account_id' => 'acct_test',
        'stripe_onboarding_complete' => true,
    ]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->get(route('public.checkout', [
        'team' => $team,
        'gym' => $gym->slug,
        'membershipPlan' => $plan,
    ]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/checkout')
        );
});

it('creates a subscription intent for recurring plans', function () {
    $user = User::factory()->create(['stripe_customer_id' => 'cus_test_123']);
    $team = Team::factory()->create([
        'is_active' => true,
        'stripe_account_id' => 'acct_test',
        'stripe_onboarding_complete' => true,
    ]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $plan = MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'plan_type' => PlanType::Recurring,
        'stripe_price_id' => 'price_test_123',
    ]);

    $mockSubscription = \Stripe\Subscription::constructFrom([
        'id' => 'sub_test_123',
        'latest_invoice' => [
            'id' => 'in_test',
            'object' => 'invoice',
            'payment_intent' => [
                'id' => 'pi_test',
                'object' => 'payment_intent',
                'client_secret' => 'pi_secret_test',
            ],
        ],
    ]);

    $mockStripe = Mockery::mock(StripeService::class);
    $mockStripe->shouldReceive('getOrCreateCustomerForCheckout')
        ->once()
        ->andReturn('cus_test_123');
    $mockStripe->shouldReceive('createSubscription')
        ->once()
        ->with('cus_test_123', 'price_test_123', 'acct_test')
        ->andReturn($mockSubscription);

    $this->app->instance(StripeService::class, $mockStripe);

    $this->actingAs($user)
        ->postJson(route('public.checkout.intent', [
            'team' => $team,
            'gym' => $gym->slug,
            'membershipPlan' => $plan,
        ]), ['name' => 'Test User', 'email' => 'test@example.com'])
        ->assertSuccessful()
        ->assertJson([
            'clientSecret' => 'pi_secret_test',
            'subscriptionId' => 'sub_test_123',
        ]);
});

it('creates a payment intent for one-time plans', function () {
    $user = User::factory()->create(['stripe_customer_id' => 'cus_test_123']);
    $team = Team::factory()->create([
        'is_active' => true,
        'stripe_account_id' => 'acct_test',
        'stripe_onboarding_complete' => true,
    ]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $plan = MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'plan_type' => PlanType::OneTime,
        'price_cents' => 4999,
        'stripe_price_id' => 'price_test_onetime',
    ]);

    $mockPaymentIntent = \Stripe\PaymentIntent::constructFrom([
        'id' => 'pi_test_123',
        'client_secret' => 'pi_secret_onetime_test',
    ]);

    $mockStripe = Mockery::mock(StripeService::class);
    $mockStripe->shouldReceive('getOrCreateCustomerForCheckout')
        ->once()
        ->andReturn('cus_test_123');
    $mockStripe->shouldReceive('createPaymentIntent')
        ->once()
        ->with(4999, 'cus_test_123', 'acct_test')
        ->andReturn($mockPaymentIntent);

    $this->app->instance(StripeService::class, $mockStripe);

    $this->actingAs($user)
        ->postJson(route('public.checkout.intent', [
            'team' => $team,
            'gym' => $gym->slug,
            'membershipPlan' => $plan,
        ]), ['name' => 'Test User', 'email' => 'test@example.com'])
        ->assertSuccessful()
        ->assertJson([
            'clientSecret' => 'pi_secret_onetime_test',
            'paymentIntentId' => 'pi_test_123',
        ]);
});

it('returns 422 when plan has no stripe price id', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create([
        'is_active' => true,
        'stripe_account_id' => 'acct_test',
        'stripe_onboarding_complete' => true,
    ]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $plan = MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'stripe_price_id' => null,
    ]);

    $this->app->instance(StripeService::class, Mockery::mock(StripeService::class));

    $this->actingAs($user)
        ->postJson(route('public.checkout.intent', [
            'team' => $team,
            'gym' => $gym->slug,
            'membershipPlan' => $plan,
        ]), ['name' => 'Test User', 'email' => 'test@example.com'])
        ->assertStatus(422);
});

it('creates intent for guest with contact fields', function () {
    $team = Team::factory()->create([
        'is_active' => true,
        'stripe_account_id' => 'acct_test',
        'stripe_onboarding_complete' => true,
    ]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $plan = MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'plan_type' => PlanType::OneTime,
        'price_cents' => 2999,
        'stripe_price_id' => 'price_guest_test',
    ]);

    $mockPaymentIntent = \Stripe\PaymentIntent::constructFrom([
        'id' => 'pi_guest_123',
        'client_secret' => 'pi_secret_guest',
    ]);

    $mockStripe = Mockery::mock(StripeService::class);
    $mockStripe->shouldReceive('getOrCreateCustomerForCheckout')
        ->once()
        ->with(null, 'guest@example.com', 'Guest User')
        ->andReturn('cus_guest_123');
    $mockStripe->shouldReceive('createPaymentIntent')
        ->once()
        ->andReturn($mockPaymentIntent);

    $this->app->instance(StripeService::class, $mockStripe);

    $this->postJson(route('public.checkout.intent', [
        'team' => $team,
        'gym' => $gym->slug,
        'membershipPlan' => $plan,
    ]), [
        'name' => 'Guest User',
        'email' => 'guest@example.com',
        'phone' => '555-1234',
    ])
        ->assertSuccessful()
        ->assertJson([
            'clientSecret' => 'pi_secret_guest',
            'paymentIntentId' => 'pi_guest_123',
        ]);
});

it('requires name and email for creating intent', function () {
    $team = Team::factory()->create([
        'is_active' => true,
        'stripe_account_id' => 'acct_test',
        'stripe_onboarding_complete' => true,
    ]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $plan = MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'stripe_price_id' => 'price_test',
    ]);

    $this->app->instance(StripeService::class, Mockery::mock(StripeService::class));

    $this->postJson(route('public.checkout.intent', [
        'team' => $team,
        'gym' => $gym->slug,
        'membershipPlan' => $plan,
    ]), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email']);
});

it('shows checkout page without stripe account in dev mode', function () {
    config()->set('stripe.dev_mode', true);

    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->get(route('public.checkout', [
        'team' => $team,
        'gym' => $gym->slug,
        'membershipPlan' => $plan,
    ]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/checkout')
            ->where('stripeDevMode', true)
            ->has('team')
            ->has('gym')
            ->has('plan')
        );
});

it('allows full checkout flow in dev mode without stripe', function () {
    config()->set('stripe.dev_mode', true);

    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    $plan = MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'plan_type' => PlanType::OneTime,
        'price_cents' => 2999,
    ]);

    $intentResponse = $this->postJson(route('public.checkout.intent', [
        'team' => $team,
        'gym' => $gym->slug,
        'membershipPlan' => $plan,
    ]), [
        'name' => 'Dev User',
        'email' => 'dev@example.com',
        'phone' => '555-0000',
    ]);

    $intentResponse->assertSuccessful()
        ->assertJson([
            'clientSecret' => null,
            'devMode' => true,
            'membershipPlanId' => $plan->id,
        ]);

    $intentData = $intentResponse->json();

    expect($intentData['paymentIntentId'])->toStartWith('dev_pi_');

    $successResponse = $this->get(route('public.checkout.success', [
        'team' => $team,
        'gym' => $gym->slug,
        'payment_intent' => $intentData['paymentIntentId'],
        'membership_plan' => $plan->id,
    ]));

    $successResponse->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/checkout-success')
            ->has('membership')
            ->where('email', 'dev@example.com')
        );

    $this->assertDatabaseHas('memberships', [
        'email' => 'dev@example.com',
        'customer_name' => 'Dev User',
        'membership_plan_id' => $plan->id,
        'stripe_status' => 'dev_mode',
    ]);
});

it('generates unique access code on membership creation', function () {
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);

    $action = new \App\Actions\Membership\CreateMembership;

    $membership1 = $action->handle(null, $plan, 'a@example.com', 'User A');
    $membership2 = $action->handle(null, $plan, 'b@example.com', 'User B');

    expect($membership1->access_code)->toHaveLength(24);
    expect($membership2->access_code)->toHaveLength(24);
    expect($membership1->access_code)->not->toBe($membership2->access_code);
    expect($membership1->user_id)->toBeNull();
    expect($membership1->email)->toBe('a@example.com');
    expect($membership1->customer_name)->toBe('User A');
});
