<?php

use App\Enums\BillingPeriod;
use App\Enums\PlanType;
use App\Models\Gym;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Services\StripeService;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->team = Team::factory()->create([
        'is_active' => true,
        'stripe_account_id' => 'acct_test_123',
        'stripe_onboarding_complete' => true,
    ]);
    $this->gym = Gym::factory()->create([
        'team_id' => $this->team->id,
        'is_active' => true,
    ]);
    $this->recurringPlan = MembershipPlan::factory()->create([
        'team_id' => $this->team->id,
        'is_active' => true,
        'plan_type' => PlanType::Recurring,
        'billing_period' => BillingPeriod::Monthly,
        'price_cents' => 4999,
        'stripe_product_id' => 'prod_test',
        'stripe_price_id' => 'price_test',
    ]);
    $this->oneTimePlan = MembershipPlan::factory()->create([
        'team_id' => $this->team->id,
        'is_active' => true,
        'plan_type' => PlanType::OneTime,
        'billing_period' => BillingPeriod::Monthly,
        'price_cents' => 9999,
        'stripe_product_id' => 'prod_test_ot',
        'stripe_price_id' => 'price_test_ot',
    ]);
    $this->contactData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '555-1234',
    ];
});

function intentUrl(Team $team, Gym $gym, MembershipPlan $plan): string
{
    return route('widget.checkout.intent', [
        'team' => $team->slug,
        'gym' => $gym->slug,
        'membershipPlan' => $plan->id,
    ]);
}

function confirmUrl(Team $team, Gym $gym): string
{
    return route('widget.checkout.confirm', [
        'team' => $team->slug,
        'gym' => $gym->slug,
    ]);
}

// ── createIntent Tests ──────────────────────────────────────────────

describe('createIntent', function () {
    it('returns dev mode response when stripe dev mode is enabled', function () {
        config(['stripe.dev_mode' => true]);

        $this->postJson(intentUrl($this->team, $this->gym, $this->recurringPlan), $this->contactData)
            ->assertSuccessful()
            ->assertJsonStructure(['clientSecret', 'subscriptionId', 'devMode', 'membershipPlanId'])
            ->assertJson(['devMode' => true]);
    });

    it('returns subscription id for recurring plans in dev mode', function () {
        config(['stripe.dev_mode' => true]);

        $response = $this->postJson(intentUrl($this->team, $this->gym, $this->recurringPlan), $this->contactData)
            ->assertSuccessful();

        expect($response->json('subscriptionId'))->toStartWith('dev_sub_');
        expect($response->json('paymentIntentId'))->toBeNull();
    });

    it('returns payment intent id for one-time plans in dev mode', function () {
        config(['stripe.dev_mode' => true]);

        $response = $this->postJson(intentUrl($this->team, $this->gym, $this->oneTimePlan), $this->contactData)
            ->assertSuccessful();

        expect($response->json('paymentIntentId'))->toStartWith('dev_pi_');
        expect($response->json('subscriptionId'))->toBeNull();
    });

    it('creates subscription for recurring plans via stripe', function () {
        config(['stripe.dev_mode' => false]);

        $mockSubscription = \Stripe\Subscription::constructFrom([
            'id' => 'sub_test_123',
            'latest_invoice' => [
                'id' => 'in_test',
                'payment_intent' => [
                    'id' => 'pi_test',
                    'client_secret' => 'pi_secret_test',
                ],
            ],
        ]);

        $stripe = $this->mock(StripeService::class);
        $stripe->shouldReceive('getOrCreateCustomerForCheckout')
            ->with(null, 'john@example.com', 'John Doe')
            ->andReturn('cus_test_123');
        $stripe->shouldReceive('createSubscription')
            ->with('cus_test_123', 'price_test', 'acct_test_123')
            ->andReturn($mockSubscription);

        $this->postJson(intentUrl($this->team, $this->gym, $this->recurringPlan), $this->contactData)
            ->assertSuccessful()
            ->assertJson([
                'clientSecret' => 'pi_secret_test',
                'subscriptionId' => 'sub_test_123',
            ]);
    });

    it('creates yearly subscription for recurring plans when yearly period is requested', function () {
        config(['stripe.dev_mode' => false]);

        $this->recurringPlan->update([
            'yearly_price_cents' => 49990,
            'stripe_yearly_price_id' => 'price_test_yearly',
        ]);

        $mockSubscription = \Stripe\Subscription::constructFrom([
            'id' => 'sub_test_yearly',
            'latest_invoice' => [
                'id' => 'in_test',
                'payment_intent' => [
                    'id' => 'pi_test',
                    'client_secret' => 'pi_secret_test',
                ],
            ],
        ]);

        $stripe = $this->mock(StripeService::class);
        $stripe->shouldReceive('getOrCreateCustomerForCheckout')
            ->with(null, 'john@example.com', 'John Doe')
            ->andReturn('cus_test_123');
        $stripe->shouldReceive('createSubscription')
            ->with('cus_test_123', 'price_test_yearly', 'acct_test_123')
            ->andReturn($mockSubscription);

        $this->postJson(intentUrl($this->team, $this->gym, $this->recurringPlan), [
            ...$this->contactData,
            'billing_period' => 'yearly',
        ])
            ->assertSuccessful()
            ->assertJson([
                'clientSecret' => 'pi_secret_test',
                'subscriptionId' => 'sub_test_yearly',
                'billingPeriod' => 'yearly',
            ]);
    });

    it('creates payment intent for one-time plans via stripe', function () {
        config(['stripe.dev_mode' => false]);

        $mockPaymentIntent = \Stripe\PaymentIntent::constructFrom([
            'id' => 'pi_test_123',
            'client_secret' => 'pi_secret_test',
        ]);

        $stripe = $this->mock(StripeService::class);
        $stripe->shouldReceive('getOrCreateCustomerForCheckout')
            ->with(null, 'john@example.com', 'John Doe')
            ->andReturn('cus_test_123');
        $stripe->shouldReceive('createPaymentIntent')
            ->with(9999, 'cus_test_123', 'acct_test_123')
            ->andReturn($mockPaymentIntent);

        $this->postJson(intentUrl($this->team, $this->gym, $this->oneTimePlan), $this->contactData)
            ->assertSuccessful()
            ->assertJson([
                'clientSecret' => 'pi_secret_test',
                'paymentIntentId' => 'pi_test_123',
            ]);
    });

    it('validates required contact fields', function () {
        config(['stripe.dev_mode' => true]);

        $this->postJson(intentUrl($this->team, $this->gym, $this->recurringPlan), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);
    });

    it('returns 404 for inactive team', function () {
        config(['stripe.dev_mode' => true]);
        $this->team->update(['is_active' => false]);

        $this->postJson(intentUrl($this->team, $this->gym, $this->recurringPlan), $this->contactData)
            ->assertNotFound();
    });

    it('returns 404 for plan not belonging to team', function () {
        config(['stripe.dev_mode' => true]);
        $otherTeam = Team::factory()->create(['is_active' => true]);
        $otherPlan = MembershipPlan::factory()->create(['team_id' => $otherTeam->id, 'is_active' => true]);

        $this->postJson(intentUrl($this->team, $this->gym, $otherPlan), $this->contactData)
            ->assertNotFound();
    });

    it('returns 404 for inactive plan', function () {
        config(['stripe.dev_mode' => true]);
        $this->recurringPlan->update(['is_active' => false]);

        $this->postJson(intentUrl($this->team, $this->gym, $this->recurringPlan), $this->contactData)
            ->assertNotFound();
    });

    it('returns CORS headers', function () {
        config(['stripe.dev_mode' => true]);

        $this->postJson(intentUrl($this->team, $this->gym, $this->recurringPlan), $this->contactData)
            ->assertSuccessful()
            ->assertHeader('Access-Control-Allow-Origin');
    });
});

// ── confirm Tests ───────────────────────────────────────────────────

describe('confirm', function () {
    it('creates membership in dev mode', function () {
        config(['stripe.dev_mode' => true]);
        Mail::fake();

        $response = $this->postJson(confirmUrl($this->team, $this->gym), [
            'subscription_id' => 'dev_sub_1_12345',
            'membership_plan' => $this->recurringPlan->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '555-1234',
        ])->assertSuccessful();

        expect($response->json('membership.access_code'))->not->toBeEmpty();
        expect($response->json('membership.status'))->toBe('active');
        expect($response->json('plan.name'))->toBe($this->recurringPlan->name);
        expect($response->json('email'))->toBe('john@example.com');

        $this->assertDatabaseHas('memberships', [
            'email' => 'john@example.com',
            'customer_name' => 'John Doe',
            'membership_plan_id' => $this->recurringPlan->id,
            'stripe_subscription_id' => 'dev_sub_1_12345',
        ]);
    });

    it('sends confirmation email', function () {
        config(['stripe.dev_mode' => true]);
        Mail::fake();

        $this->postJson(confirmUrl($this->team, $this->gym), [
            'subscription_id' => 'dev_sub_1_12345',
            'membership_plan' => $this->recurringPlan->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])->assertSuccessful();

        Mail::assertSent(\App\Mail\MembershipConfirmationMail::class, function ($mail) {
            return $mail->hasTo('john@example.com');
        });
    });

    it('returns existing membership on duplicate call (idempotency)', function () {
        config(['stripe.dev_mode' => true]);
        Mail::fake();

        $existing = Membership::factory()->guest()->create([
            'team_id' => $this->team->id,
            'membership_plan_id' => $this->recurringPlan->id,
            'stripe_subscription_id' => 'dev_sub_1_12345',
            'email' => 'john@example.com',
        ]);

        $response = $this->postJson(confirmUrl($this->team, $this->gym), [
            'subscription_id' => 'dev_sub_1_12345',
            'membership_plan' => $this->recurringPlan->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])->assertSuccessful();

        expect($response->json('membership.access_code'))->toBe($existing->access_code);

        Mail::assertNothingSent();
    });

    it('creates membership after stripe subscription verification', function () {
        config(['stripe.dev_mode' => false]);
        Mail::fake();

        $mockSubscription = \Stripe\Subscription::constructFrom([
            'id' => 'sub_real_123',
            'status' => 'active',
        ]);

        $stripe = $this->mock(StripeService::class);
        $stripe->shouldReceive('retrieveSubscription')
            ->with('sub_real_123')
            ->andReturn($mockSubscription);

        $this->postJson(confirmUrl($this->team, $this->gym), [
            'subscription_id' => 'sub_real_123',
            'membership_plan' => $this->recurringPlan->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])->assertSuccessful();

        $this->assertDatabaseHas('memberships', [
            'email' => 'john@example.com',
            'stripe_subscription_id' => 'sub_real_123',
            'stripe_status' => 'active',
        ]);
    });

    it('creates membership after stripe payment intent verification', function () {
        config(['stripe.dev_mode' => false]);
        Mail::fake();

        $mockPaymentIntent = \Stripe\PaymentIntent::constructFrom([
            'id' => 'pi_real_123',
            'status' => 'succeeded',
        ]);

        $stripe = $this->mock(StripeService::class);
        $stripe->shouldReceive('retrievePaymentIntent')
            ->with('pi_real_123')
            ->andReturn($mockPaymentIntent);

        $this->postJson(confirmUrl($this->team, $this->gym), [
            'payment_intent_id' => 'pi_real_123',
            'membership_plan' => $this->oneTimePlan->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])->assertSuccessful();

        $this->assertDatabaseHas('memberships', [
            'email' => 'john@example.com',
            'stripe_payment_intent_id' => 'pi_real_123',
            'stripe_status' => 'succeeded',
        ]);
    });

    it('returns 422 when payment has not succeeded', function () {
        config(['stripe.dev_mode' => false]);

        $mockPaymentIntent = \Stripe\PaymentIntent::constructFrom([
            'id' => 'pi_failed_123',
            'status' => 'requires_payment_method',
        ]);

        $stripe = $this->mock(StripeService::class);
        $stripe->shouldReceive('retrievePaymentIntent')
            ->with('pi_failed_123')
            ->andReturn($mockPaymentIntent);

        $this->postJson(confirmUrl($this->team, $this->gym), [
            'payment_intent_id' => 'pi_failed_123',
            'membership_plan' => $this->oneTimePlan->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])->assertUnprocessable();
    });

    it('returns 404 for inactive team', function () {
        config(['stripe.dev_mode' => true]);
        $this->team->update(['is_active' => false]);

        $this->postJson(confirmUrl($this->team, $this->gym), [
            'subscription_id' => 'dev_sub_1_12345',
            'membership_plan' => $this->recurringPlan->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])->assertNotFound();
    });

    it('returns 404 for inactive gym', function () {
        config(['stripe.dev_mode' => true]);
        $this->gym->update(['is_active' => false]);

        $this->postJson(confirmUrl($this->team, $this->gym), [
            'subscription_id' => 'dev_sub_1_12345',
            'membership_plan' => $this->recurringPlan->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])->assertNotFound();
    });

    it('validates required fields', function () {
        config(['stripe.dev_mode' => true]);

        $this->postJson(confirmUrl($this->team, $this->gym), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['membership_plan', 'name', 'email']);
    });

    it('validates at least one stripe id is required', function () {
        config(['stripe.dev_mode' => true]);

        $this->postJson(confirmUrl($this->team, $this->gym), [
            'membership_plan' => $this->recurringPlan->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['subscription_id']);
    });
});
