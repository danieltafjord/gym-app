<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class WidgetController extends Controller
{
    public function data(Team $team, string $gym): JsonResponse
    {
        abort_unless($team->is_active, 404);

        $gym = $team->gyms()->where('slug', $gym)->active()->firstOrFail();
        $gym->setRelation('team', $team);

        $plans = $team->membershipPlans()
            ->active()
            ->ordered()
            ->get([
                'id',
                'name',
                'description',
                'price_cents',
                'yearly_price_cents',
                'billing_period',
                'plan_type',
                'features',
                'sort_order',
            ]);

        return response()->json([
            'gym' => [
                'name' => $gym->name,
                'slug' => $gym->slug,
            ],
            'team' => [
                'name' => $team->name,
                'slug' => $team->slug,
            ],
            'plans' => $plans->map(fn ($plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'price_formatted' => $plan->price_formatted,
                'yearly_price_cents' => $plan->yearly_price_cents,
                'yearly_price_formatted' => $plan->yearly_price_formatted,
                'billing_period' => $plan->billing_period->value,
                'plan_type' => $plan->plan_type->value,
                'features' => $plan->features,
            ]),
            'settings' => $gym->widget_settings_with_defaults,
            'stripe_key' => config('stripe.key'),
            'stripe_dev_mode' => (bool) config('stripe.dev_mode'),
            'checkout_intent_url' => route('widget.checkout.intent', [
                'team' => $team->slug,
                'gym' => $gym->slug,
                'membershipPlan' => '__PLAN_ID__',
            ]),
            'checkout_confirm_url' => route('widget.checkout.confirm', [
                'team' => $team->slug,
                'gym' => $gym->slug,
            ]),
            'stripe_ready' => $team->hasStripeAccount() || config('stripe.dev_mode'),
        ])->withHeaders([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    public function script(): Response
    {
        $scriptPath = resource_path('js/widget/embed.js');

        abort_unless(file_exists($scriptPath), 404);

        $contents = file_get_contents($scriptPath);
        $etag = '"'.md5($contents).'"';

        return response($contents)
            ->withHeaders([
                'Content-Type' => 'application/javascript',
                'Access-Control-Allow-Origin' => '*',
                'Cache-Control' => 'public, max-age=300',
                'ETag' => $etag,
            ]);
    }
}
