<?php

namespace App\Http\Controllers\Public;

use App\Actions\Membership\CreateMembership;
use App\Enums\BillingPeriod;
use App\Enums\PlanType;
use App\Http\Controllers\Controller;
use App\Mail\MembershipConfirmationMail;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(Team $team, string $gymSlug, MembershipPlan $membershipPlan): Response|RedirectResponse
    {
        abort_unless($team->is_active, 404);

        $devMode = (bool) config('stripe.dev_mode');

        if (! $devMode) {
            abort_unless($team->hasStripeAccount(), 404);
        }

        $gym = $team->gyms()->where('slug', $gymSlug)->active()->firstOrFail();

        abort_unless($membershipPlan->team_id === $team->id, 404);
        abort_unless($membershipPlan->is_active, 404);

        if ($membershipPlan->requires_account && ! request()->user()) {
            return redirect()->guest(route('login'));
        }

        return Inertia::render('public/checkout', [
            'team' => $team,
            'gym' => $gym,
            'plan' => $membershipPlan,
            'stripeKey' => config('stripe.key'),
            'stripeDevMode' => $devMode,
        ]);
    }

    public function createIntent(
        Request $request,
        Team $team,
        string $gymSlug,
        MembershipPlan $membershipPlan,
        StripeService $stripe,
    ): JsonResponse {
        abort_unless($team->is_active, 404);
        abort_unless($membershipPlan->team_id === $team->id, 404);

        abort_unless(! $membershipPlan->requires_account || $request->user(), 403, 'This product requires an account.');

        $devMode = (bool) config('stripe.dev_mode');

        if (! $devMode) {
            abort_unless($team->hasStripeAccount(), 404);
            abort_unless($membershipPlan->stripe_price_id, 422);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'billing_period' => ['nullable', 'string', 'in:monthly,yearly'],
        ]);

        $selectedBillingPeriod = $this->resolveCheckoutBillingPeriod(
            $membershipPlan,
            $validated['billing_period'] ?? null,
        );

        $request->session()->put('checkout_email', $validated['email']);
        $request->session()->put('checkout_name', $validated['name']);
        $request->session()->put('checkout_phone', $validated['phone'] ?? null);
        $request->session()->put('checkout_billing_period', $selectedBillingPeriod->value);

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
            $request->user(),
            $validated['email'],
            $validated['name'],
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

    public function success(
        Request $request,
        Team $team,
        string $gymSlug,
        CreateMembership $createMembership,
        StripeService $stripe,
    ): Response {
        abort_unless($team->is_active, 404);

        $gym = $team->gyms()->where('slug', $gymSlug)->active()->firstOrFail();

        $subscriptionId = $request->query('subscription_id');
        $paymentIntentId = $request->query('payment_intent');
        $membershipPlanId = $request->query('membership_plan');
        $devMode = (bool) config('stripe.dev_mode');

        $plan = null;
        $stripeStatus = null;
        $selectedBillingPeriod = null;

        $isDevTransaction = $devMode && (
            str_starts_with((string) $subscriptionId, 'dev_') ||
            str_starts_with((string) $paymentIntentId, 'dev_')
        );

        if ($isDevTransaction) {
            $plan = MembershipPlan::where('id', $membershipPlanId)
                ->where('team_id', $team->id)
                ->first();
            $stripeStatus = 'dev_mode';
        } elseif ($subscriptionId) {
            $subscription = $stripe->retrieveSubscription($subscriptionId);
            $stripePriceId = data_get($subscription, 'items.data.0.price.id');
            if (is_string($stripePriceId)) {
                $plan = MembershipPlan::query()
                    ->where('team_id', $team->id)
                    ->where(function ($query) use ($stripePriceId) {
                        $query
                            ->where('stripe_price_id', $stripePriceId)
                            ->orWhere('stripe_yearly_price_id', $stripePriceId);
                    })
                    ->first();
            }
            $stripeStatus = $subscription->status;
            if ($plan && is_string($stripePriceId)) {
                $selectedBillingPeriod = $plan->hasYearlyPricingOption() && $plan->stripe_yearly_price_id === $stripePriceId
                    ? BillingPeriod::Yearly
                    : $plan->billing_period;
            }
        } elseif ($paymentIntentId) {
            $paymentIntent = $stripe->retrievePaymentIntent($paymentIntentId);
            $stripeStatus = $paymentIntent->status;

            $plan = MembershipPlan::query()
                ->where('team_id', $team->id)
                ->when(
                    $membershipPlanId,
                    fn ($query) => $query->where('id', $membershipPlanId),
                    fn ($query) => $query
                        ->where('price_cents', $paymentIntent->amount)
                        ->where('plan_type', PlanType::OneTime)
                )
                ->first();
        }

        abort_unless($plan, 404);

        if ($isDevTransaction) {
            $selectedBillingPeriod = $this->resolveCheckoutBillingPeriod(
                $plan,
                $request->query('billing_period') ?: $request->session()->pull('checkout_billing_period'),
            );
        }

        $effectiveBillingPeriod = $selectedBillingPeriod ?? $plan->billing_period;

        $existingMembership = Membership::query()
            ->where(function ($q) use ($subscriptionId, $paymentIntentId) {
                if ($subscriptionId) {
                    $q->where('stripe_subscription_id', $subscriptionId);
                }
                if ($paymentIntentId) {
                    $q->orWhere('stripe_payment_intent_id', $paymentIntentId);
                }
            })
            ->first();

        if (! $existingMembership) {
            $user = $request->user();
            $email = $user?->email ?? $request->session()->pull('checkout_email');
            $name = $user?->name ?? $request->session()->pull('checkout_name');
            $phone = $request->session()->pull('checkout_phone');

            $existingMembership = $createMembership->handle(
                user: $user,
                plan: $plan,
                email: $email,
                customerName: $name,
                customerPhone: $phone,
                stripeSubscriptionId: $subscriptionId,
                stripePaymentIntentId: $paymentIntentId,
                stripeStatus: $stripeStatus,
                billingPeriod: $effectiveBillingPeriod,
            );

            Mail::to($email)->send(new MembershipConfirmationMail(
                $existingMembership->load('plan', 'team'),
                $gym,
            ));
        }

        return Inertia::render('public/checkout-success', [
            'team' => $team,
            'gym' => $gym,
            'plan' => $plan,
            'membership' => $existingMembership->load('plan'),
            'email' => $existingMembership->email,
            'selectedBillingPeriod' => $effectiveBillingPeriod->value,
            'selectedPriceFormatted' => $effectiveBillingPeriod === BillingPeriod::Yearly && $plan->yearly_price_formatted !== null
                ? $plan->yearly_price_formatted
                : $plan->price_formatted,
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
