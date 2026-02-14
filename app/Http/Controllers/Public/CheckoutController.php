<?php

namespace App\Http\Controllers\Public;

use App\Actions\Membership\CreateMembership;
use App\Enums\PlanType;
use App\Http\Controllers\Controller;
use App\Mail\MembershipConfirmationMail;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(Team $team, string $gymSlug, MembershipPlan $membershipPlan): Response
    {
        abort_unless($team->is_active, 404);

        $devMode = (bool) config('stripe.dev_mode');

        if (! $devMode) {
            abort_unless($team->hasStripeAccount(), 404);
        }

        $gym = $team->gyms()->where('slug', $gymSlug)->active()->firstOrFail();

        abort_unless($membershipPlan->team_id === $team->id, 404);
        abort_unless($membershipPlan->is_active, 404);

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

        $devMode = (bool) config('stripe.dev_mode');

        if (! $devMode) {
            abort_unless($team->hasStripeAccount(), 404);
            abort_unless($membershipPlan->stripe_price_id, 422);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $request->session()->put('checkout_email', $validated['email']);
        $request->session()->put('checkout_name', $validated['name']);
        $request->session()->put('checkout_phone', $validated['phone'] ?? null);

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
            ]);
        }

        $customerId = $stripe->getOrCreateCustomerForCheckout(
            $request->user(),
            $validated['email'],
            $validated['name'],
        );

        if ($membershipPlan->plan_type === PlanType::Recurring) {
            $subscription = $stripe->createSubscription(
                $customerId,
                $membershipPlan->stripe_price_id,
                $team->stripe_account_id,
            );

            return response()->json([
                'clientSecret' => $subscription->latest_invoice->payment_intent->client_secret,
                'subscriptionId' => $subscription->id,
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
        $devMode = (bool) config('stripe.dev_mode');

        $plan = null;
        $stripeStatus = null;

        $isDevTransaction = $devMode && (
            str_starts_with((string) $subscriptionId, 'dev_') ||
            str_starts_with((string) $paymentIntentId, 'dev_')
        );

        if ($isDevTransaction) {
            $membershipPlanId = $request->query('membership_plan');
            $plan = MembershipPlan::where('id', $membershipPlanId)
                ->where('team_id', $team->id)
                ->first();
            $stripeStatus = 'dev_mode';
        } elseif ($subscriptionId) {
            $subscription = $stripe->retrieveSubscription($subscriptionId);
            $plan = MembershipPlan::where('stripe_price_id', $subscription->items->data[0]->price->id)->first();
            $stripeStatus = $subscription->status;
        } elseif ($paymentIntentId) {
            $paymentIntent = $stripe->retrievePaymentIntent($paymentIntentId);
            $stripeStatus = $paymentIntent->status;

            $plan = MembershipPlan::query()
                ->where('team_id', $team->id)
                ->where('price_cents', $paymentIntent->amount)
                ->where('plan_type', PlanType::OneTime)
                ->first();
        }

        abort_unless($plan, 404);

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
                $user,
                $plan,
                $email,
                $name,
                $phone,
                $subscriptionId,
                $paymentIntentId,
                $stripeStatus,
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
        ]);
    }
}
