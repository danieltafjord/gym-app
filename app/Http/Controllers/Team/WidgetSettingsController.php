<?php

namespace App\Http\Controllers\Team;

use App\Actions\Gym\ResetWidgetSettings;
use App\Actions\Gym\UpdateWidgetSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\UpdateWidgetSettingsRequest;
use App\Models\Gym;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WidgetSettingsController extends Controller
{
    public function edit(Team $team, Gym $gym): Response
    {
        $gym->setRelation('team', $team);

        return Inertia::render('team/gyms/settings/widget', [
            'team' => $team,
            'gym' => $gym,
            'settings' => $gym->widget_settings_with_defaults,
            'plans' => $team->membershipPlans()->active()->ordered()->get(),
            'embedUrl' => route('widget.script'),
            'hasOverrides' => $gym->widget_settings !== null,
        ]);
    }

    public function update(
        UpdateWidgetSettingsRequest $request,
        Team $team,
        Gym $gym,
        UpdateWidgetSettings $action,
    ): RedirectResponse {
        $action->handle($gym, $request->validated());

        return back();
    }

    public function destroy(
        Team $team,
        Gym $gym,
        ResetWidgetSettings $action,
    ): RedirectResponse {
        $action->handle($gym);

        return back();
    }
}
