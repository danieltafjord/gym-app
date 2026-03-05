<?php

namespace App\Http\Controllers\Public;

use App\Actions\Membership\CreateMembership;
use App\Enums\BillingPeriod;
use App\Enums\PlanType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Widget\WidgetCheckoutConfirmRequest;
use App\Http\Requests\Widget\WidgetCheckoutRequest;
use App\Mail\MembershipConfirmationMail;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class WidgetCheckoutController extends Controller
{
    public function createIntent(
        WidgetCheckoutRequest $request,
        Team $team,
        string $gym,
        MembershipPlan $membershipPlan,
        StripeService $stripe,
    ): JsonResponse {
        abort_unless($team->is_active, 404);
        abort_unless($membershipPlan->team_id === $team->id, 404);
        abort_unless($membershipPlan->is_active, 404);

        $devMode = (bool) config('stripe.dev_mode');

        if (! $devMode) {
            abort_unless($team->hasStripeAccount(), 404);
        }

        $selectedBillingPeriod = $this->resolveCheckoutBillingPeriod(
            $membershipPlan,
            $request->validated('billing_period'),
        );

        if ($devMode) {
            $fakeId = $membershipPlan->plan_type === PlanType::Recurring
                ? 'dev_sub_'.$membershipPlan->id.'_'.time()
                : 'dev_pi_'.$membershipPlan->id.'_'.time();

            return response()->json([
                'clientSecret' => null,
                'subscriptionId' => $membershipPlan->plan_type === PlanType::Recurring ? $fakeId : null,
                'paymentIntentId' => $membershipPlan->plan_type === PlanType::OneTime ? $fakeId : null,
                'devMode' => true,
                'membershipPlanId' => $membershipPlan->id,
                'billingPeriod' => $selectedBillingPeriod->value,
            ]);
        }

        $customerId = $stripe->getOrCreateCustomerForCheckout(
            null,
            $request->validated('email'),
            $request->validated('name'),
        );

        if ($membershipPlan->plan_type === PlanType::Recurring) {
            $stripePriceId = $membershipPlan->stripePriceIdForBillingPeriod($selectedBillingPeriod);

            abort_unless($stripePriceId, 422);

            $subscription = $stripe->createSubscription(
                $customerId,
                $stripePriceId,
                $team->stripe_account_id,
            );

            return response()->json([
                'clientSecret' => $subscription->latest_invoice->payment_intent->client_secret,
                'subscriptionId' => $subscription->id,
                'billingPeriod' => $selectedBillingPeriod->value,
            ]);
        }

        $paymentIntent = $stripe->createPaymentIntent(
            $membershipPlan->price_cents,
            $customerId,
            $team->stripe_account_id,
        );

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
            'paymentIntentId' => $paymentIntent->id,
        ]);
    }

    public function confirm(
        WidgetCheckoutConfirmRequest $request,
        Team $team,
        string $gym,
        CreateMembership $createMembership,
        StripeService $stripe,
    ): JsonResponse {
        abort_unless($team->is_active, 404);

        $gymModel = $team->gyms()->where('slug', $gym)->active()->firstOrFail();

        $plan = $team->membershipPlans()
            ->where('id', $request->validated('membership_plan'))
            ->active()
            ->firstOrFail();

        $subscriptionId = $request->validated('subscription_id');
        $paymentIntentId = $request->validated('payment_intent_id');
        $devMode = (bool) config('stripe.dev_mode');
        $selectedBillingPeriod = $this->resolveCheckoutBillingPeriod(
            $plan,
            $request->validated('billing_period'),
        );

        // Idempotency: check for existing membership
        $existingMembership = Membership::query()
            ->when($subscriptionId, fn ($q) => $q->where('stripe_subscription_id', $subscriptionId))
            ->when($paymentIntentId, fn ($q) => $q->orWhere('stripe_payment_intent_id', $paymentIntentId))
            ->first();

        if ($existingMembership) {
            return $this->buildConfirmResponse($existingMembership, $selectedBillingPeriod);
        }

        // Verify payment status
        $isDevTransaction = $devMode && (
            str_starts_with((string) $subscriptionId, 'dev_') ||
            str_starts_with((string) $paymentIntentId, 'dev_')
        );

        $stripeStatus = 'dev_mode';

        if (! $isDevTransaction) {
            if ($subscriptionId) {
                $subscription = $stripe->retrieveSubscription($subscriptionId);
                $stripeStatus = $subscription->status;
                abort_unless(in_array($stripeStatus, ['active', 'trialing']), 422, 'Payment has not been completed.');

                $subscriptionPriceId = data_get($subscription, 'items.data.0.price.id');
                if (is_string($subscriptionPriceId)) {
                    $selectedBillingPeriod = $plan->hasYearlyPricingOption() && $plan->stripe_yearly_price_id === $subscriptionPriceId
                        ? BillingPeriod::Yearly
                        : $plan->billing_period;
                }
            } elseif ($paymentIntentId) {
                $paymentIntent = $stripe->retrievePaymentIntent($paymentIntentId);
                $stripeStatus = $paymentIntent->status;
                abort_unless($stripeStatus === 'succeeded', 422, 'Payment has not been completed.');
            }
        }

        $membership = $createMembership->handle(
            user: null,
            plan: $plan,
            email: $request->validated('email'),
            customerName: $request->validated('name'),
            customerPhone: $request->validated('phone'),
            stripeSubscriptionId: $subscriptionId,
            stripePaymentIntentId: $paymentIntentId,
            stripeStatus: $stripeStatus,
            billingPeriod: $selectedBillingPeriod,
        );

        Mail::to($request->validated('email'))->send(new MembershipConfirmationMail(
            $membership->load('plan', 'team'),
            $gymModel,
        ));

        return $this->buildConfirmResponse($membership, $selectedBillingPeriod);
    }

    private function buildConfirmResponse(
        Membership $membership,
        BillingPeriod $selectedBillingPeriod,
    ): JsonResponse {
        $membership->loadMissing('plan');

        $priceFormatted = $selectedBillingPeriod === BillingPeriod::Yearly
            && $membership->plan->yearly_price_formatted !== null
            ? $membership->plan->yearly_price_formatted
            : $membership->plan->price_formatted;

        return response()->json([
            'membership' => [
                'access_code' => $membership->access_code,
                'status' => $membership->status->value,
                'starts_at' => $membership->starts_at?->toDateString(),
                'ends_at' => $membership->ends_at?->toDateString(),
            ],
            'plan' => [
                'name' => $membership->plan->name,
                'price_formatted' => $priceFormatted,
                'billing_period' => $selectedBillingPeriod->value,
                'plan_type' => $membership->plan->plan_type->value,
            ],
            'email' => $membership->email,
        ]);
    }

    private function resolveCheckoutBillingPeriod(
        MembershipPlan $membershipPlan,
        ?string $billingPeriod,
    ): BillingPeriod {
        if ($membershipPlan->plan_type !== PlanType::Recurring) {
            return $membershipPlan->billing_period;
        }

        if ($billingPeriod === null) {
            return $membershipPlan->billing_period;
        }

        $selectedBillingPeriod = BillingPeriod::tryFrom($billingPeriod);

        abort_unless($selectedBillingPeriod, 422);

        if ($selectedBillingPeriod === BillingPeriod::Yearly) {
            abort_unless($membershipPlan->hasYearlyPricingOption(), 422);

            return BillingPeriod::Yearly;
        }

        abort_unless($selectedBillingPeriod === $membershipPlan->billing_period, 422);

        return $selectedBillingPeriod;
    }
}
