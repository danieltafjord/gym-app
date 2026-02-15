<?php

namespace App\Http\Controllers\Team;

use App\Actions\CheckIn\UpdateCheckInSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckIn\UpdateCheckInSettingsRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CheckInSettingsController extends Controller
{
    public function edit(Team $team): Response
    {
        return Inertia::render('team/settings/check-in', [
            'team' => $team,
            'settings' => $team->check_in_settings_with_defaults,
            'gyms' => $team->gyms()->active()->get(),
        ]);
    }

    public function update(
        UpdateCheckInSettingsRequest $request,
        Team $team,
        UpdateCheckInSettings $action,
    ): RedirectResponse {
        $action->handle($team, $request->validated());

        return back();
    }
}
