<?php

namespace App\Services;

use App\Enums\BillingPeriod;
use App\Enums\PlanType;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use Stripe\StripeClient;

class StripeService
{
    private ?StripeClient $client = null;

    public function __construct()
    {
        $secret = config('stripe.secret');

        if ($secret) {
            $this->client = new StripeClient($secret);
        }
    }

    private function client(): StripeClient
    {
        if (! $this->client) {
            throw new \RuntimeException('Stripe API key is not configured.');
        }

        return $this->client;
    }

    public function getClient(): StripeClient
    {
        return $this->client();
    }

    // ── Connect Account Management ──────────────────────────────────

    public function createConnectAccount(Team $team): \Stripe\Account
    {
        return $this->client()->accounts->create([
            'type' => 'express',
            'country' => 'US',
            'email' => $team->owner?->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
            'metadata' => [
                'team_id' => $team->id,
            ],
        ]);
    }

    public function createAccountLink(string $accountId, string $returnUrl, string $refreshUrl): \Stripe\AccountLink
    {
        return $this->client()->accountLinks->create([
            'account' => $accountId,
            'return_url' => $returnUrl,
            'refresh_url' => $refreshUrl,
            'type' => 'account_onboarding',
        ]);
    }

    public function retrieveAccount(string $accountId): \Stripe\Account
    {
        return $this->client()->accounts->retrieve($accountId);
    }

    public function createLoginLink(string $accountId): \Stripe\LoginLink
    {
        return $this->client()->accounts->createLoginLink($accountId);
    }

    // ── Customer Management ─────────────────────────────────────────

    public function createCustomer(User $user): \Stripe\Customer
    {
        return $this->client()->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);
    }

    public function getOrCreateCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        $customer = $this->createCustomer($user);
        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    public function getOrCreateCustomerForCheckout(?User $user, string $email, ?string $name = null): string
    {
        if ($user) {
            return $this->getOrCreateCustomer($user);
        }

        $customer = $this->client()->customers->create(array_filter([
            'email' => $email,
            'name' => $name,
        ]));

        return $customer->id;
    }

    // ── Product & Price Management ──────────────────────────────────

    public function createProduct(MembershipPlan $plan, string $connectedAccountId): \Stripe\Product
    {
        return $this->client()->products->create([
            'name' => $plan->name,
            'description' => $plan->description,
            'metadata' => [
                'membership_plan_id' => $plan->id,
            ],
        ], ['stripe_account' => $connectedAccountId]);
    }

    /**
     * @return array{interval: string, interval_count: int}
     */
    public function billingPeriodToStripeInterval(BillingPeriod $period): array
    {
        return match ($period) {
            BillingPeriod::Weekly => ['interval' => 'week', 'interval_count' => 1],
            BillingPeriod::Monthly => ['interval' => 'month', 'interval_count' => 1],
            BillingPeriod::Quarterly => ['interval' => 'month', 'interval_count' => 3],
            BillingPeriod::Yearly => ['interval' => 'year', 'interval_count' => 1],
        };
    }

    public function createPrice(MembershipPlan $plan, string $productId, string $connectedAccountId): \Stripe\Price
    {
        $params = [
            'product' => $productId,
            'unit_amount' => $plan->price_cents,
            'currency' => config('stripe.currency'),
            'metadata' => [
                'membership_plan_id' => $plan->id,
            ],
        ];

        if ($plan->plan_type === PlanType::Recurring) {
            $interval = $this->billingPeriodToStripeInterval($plan->billing_period);
            $params['recurring'] = $interval;
        }

        return $this->client()->prices->create($params, ['stripe_account' => $connectedAccountId]);
    }

    // ── Subscription Management ─────────────────────────────────────

    public function createSubscription(
        string $customerId,
        string $priceId,
        string $connectedAccountId,
    ): \Stripe\Subscription {
        $feePercent = config('stripe.application_fee_percent');

        return $this->client()->subscriptions->create([
            'customer' => $customerId,
            'items' => [['price' => $priceId]],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.payment_intent'],
            'application_fee_percent' => $feePercent,
            'transfer_data' => ['destination' => $connectedAccountId],
        ]);
    }

    public function cancelSubscription(string $subscriptionId): \Stripe\Subscription
    {
        return $this->client()->subscriptions->cancel($subscriptionId);
    }

    public function pauseSubscription(string $subscriptionId): \Stripe\Subscription
    {
        return $this->client()->subscriptions->update($subscriptionId, [
            'pause_collection' => ['behavior' => 'void'],
        ]);
    }

    public function resumeSubscription(string $subscriptionId): \Stripe\Subscription
    {
        return $this->client()->subscriptions->update($subscriptionId, [
            'pause_collection' => '',
        ]);
    }

    public function retrieveSubscription(string $subscriptionId): \Stripe\Subscription
    {
        return $this->client()->subscriptions->retrieve($subscriptionId);
    }

    // ── Payment Intent (One-Time) ───────────────────────────────────

    public function createPaymentIntent(
        int $amount,
        string $customerId,
        string $connectedAccountId,
    ): \Stripe\PaymentIntent {
        $feePercent = config('stripe.application_fee_percent');
        $applicationFee = (int) round($amount * $feePercent / 100);

        return $this->client()->paymentIntents->create([
            'amount' => $amount,
            'currency' => config('stripe.currency'),
            'customer' => $customerId,
            'application_fee_amount' => $applicationFee,
            'transfer_data' => ['destination' => $connectedAccountId],
        ]);
    }

    public function retrievePaymentIntent(string $paymentIntentId): \Stripe\PaymentIntent
    {
        return $this->client()->paymentIntents->retrieve($paymentIntentId);
    }

    // ── Webhook Verification ────────────────────────────────────────

    public function constructWebhookEvent(string $payload, string $signature, string $secret): \Stripe\Event
    {
        return \Stripe\Webhook::constructEvent($payload, $signature, $secret);
    }
}
