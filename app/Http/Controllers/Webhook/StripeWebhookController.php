<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\MembershipStatus;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Team;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(private StripeService $stripe) {}

    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            $event = $this->stripe->constructWebhookEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('stripe.webhook_secret'),
            );
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        return match ($event->type) {
            'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceeded($event),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            default => response()->json(['message' => 'Unhandled event type']),
        };
    }

    public function handleConnectWebhook(Request $request): JsonResponse
    {
        try {
            $event = $this->stripe->constructWebhookEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('stripe.connect_webhook_secret'),
            );
        } catch (\Exception $e) {
            Log::error('Stripe Connect webhook signature verification failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        return match ($event->type) {
            'account.updated' => $this->handleAccountUpdated($event),
            default => response()->json(['message' => 'Unhandled event type']),
        };
    }

    private function handleInvoicePaymentSucceeded(\Stripe\Event $event): JsonResponse
    {
        $invoice = $event->data->object;
        $subscriptionId = $invoice->subscription;

        if (! $subscriptionId) {
            return response()->json(['message' => 'No subscription on invoice']);
        }

        $membership = Membership::where('stripe_subscription_id', $subscriptionId)->first();

        if ($membership) {
            $membership->update([
                'status' => MembershipStatus::Active,
                'stripe_status' => 'active',
                'ends_at' => now()->addMonth(),
            ]);
        }

        return response()->json(['message' => 'Handled']);
    }

    private function handleInvoicePaymentFailed(\Stripe\Event $event): JsonResponse
    {
        $invoice = $event->data->object;
        $subscriptionId = $invoice->subscription;

        if (! $subscriptionId) {
            return response()->json(['message' => 'No subscription on invoice']);
        }

        $membership = Membership::where('stripe_subscription_id', $subscriptionId)->first();

        if ($membership) {
            $membership->update([
                'stripe_status' => 'past_due',
            ]);
        }

        return response()->json(['message' => 'Handled']);
    }

    private function handleSubscriptionDeleted(\Stripe\Event $event): JsonResponse
    {
        $subscription = $event->data->object;

        $membership = Membership::where('stripe_subscription_id', $subscription->id)->first();

        if ($membership) {
            $membership->update([
                'status' => MembershipStatus::Cancelled,
                'stripe_status' => 'canceled',
                'cancelled_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Handled']);
    }

    private function handleAccountUpdated(\Stripe\Event $event): JsonResponse
    {
        $account = $event->data->object;

        $team = Team::where('stripe_account_id', $account->id)->first();

        if ($team) {
            $team->update([
                'stripe_onboarding_complete' => $account->charges_enabled && $account->details_submitted,
            ]);
        }

        return response()->json(['message' => 'Handled']);
    }
}
