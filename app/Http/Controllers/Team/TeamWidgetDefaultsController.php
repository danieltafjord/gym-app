<?php

namespace App\Http\Controllers\Team;

use App\Actions\Team\UpdateTeamWidgetSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\UpdateTeamWidgetSettingsRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamWidgetDefaultsController extends Controller
{
    public function edit(Team $team): Response
    {
        return Inertia::render('team/settings/widget-defaults', [
            'team' => $team,
            'settings' => $team->widget_settings_with_defaults,
            'plans' => $team->membershipPlans()->active()->ordered()->get(),
        ]);
    }

    public function update(
        UpdateTeamWidgetSettingsRequest $request,
        Team $team,
        UpdateTeamWidgetSettings $action,
    ): RedirectResponse {
        $action->handle($team, $request->validated());

        return back();
    }
}
