<?php

namespace App\Http\Controllers\Team;

use App\Actions\Stripe\CompleteConnectOnboarding;
use App\Actions\Stripe\CreateConnectAccount;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StripeConnectController extends Controller
{
    public function onboard(Team $team, CreateConnectAccount $action): RedirectResponse
    {
        $url = $action->handle(
            $team,
            route('team.stripe.return', $team),
            route('team.stripe.refresh', $team),
        );

        return redirect()->away($url);
    }

    public function returnMethod(Team $team, CompleteConnectOnboarding $action): Response
    {
        $isComplete = $action->handle($team);

        return Inertia::render('team/stripe-return', [
            'team' => $team->fresh(),
            'onboardingComplete' => $isComplete,
        ]);
    }

    public function refresh(Team $team, CreateConnectAccount $action): RedirectResponse
    {
        $url = $action->handle(
            $team,
            route('team.stripe.return', $team),
            route('team.stripe.refresh', $team),
        );

        return redirect()->away($url);
    }

    public function dashboard(Team $team): RedirectResponse
    {
        abort_unless($team->hasStripeAccount(), 404);

        $link = app(\App\Services\StripeService::class)->createLoginLink($team->stripe_account_id);

        return redirect()->away($link->url);
    }
}
