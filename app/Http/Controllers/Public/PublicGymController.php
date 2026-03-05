<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class PublicGymController extends Controller
{
    public function showTeam(Team $team): Response
    {
        abort_unless($team->is_active, 404);

        return Inertia::render('public/team', [
            'team' => $team->load(['gyms' => fn ($query) => $query->active()]),
        ]);
    }

    public function showGym(Team $team, string $gymSlug): Response
    {
        abort_unless($team->is_active, 404);

        $gym = $team->gyms()->where('slug', $gymSlug)->active()->firstOrFail();

        return Inertia::render('public/gym', [
            'team' => $team,
            'gym' => $gym,
            'plans' => $team->membershipPlans()->active()->ordered()->get(),
            'stripeReady' => $team->hasStripeAccount() || config('stripe.dev_mode'),
            'widgetDemoUrl' => route('public.widget', [
                'team' => $team->slug,
                'gym' => $gym->slug,
            ]),
        ]);
    }

    public function widget(Team $team, string $gymSlug): Response
    {
        abort_unless($team->is_active, 404);

        $gym = $team->gyms()->where('slug', $gymSlug)->active()->firstOrFail();

        return Inertia::render('public/widget', [
            'team' => $team,
            'gym' => $gym,
            'widgetScriptUrl' => route('widget.script'),
            'gymPageUrl' => route('public.gym', [
                'team' => $team->slug,
                'gym' => $gym->slug,
            ]),
        ]);
    }
}
