<?php

namespace App\Http\Controllers\Team;

use App\Actions\Team\UpdateTeam;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamGeneralSettingsController extends Controller
{
    public function edit(Team $team): Response
    {
        return Inertia::render('team/settings/general', [
            'team' => $team,
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team, UpdateTeam $action): RedirectResponse
    {
        $action->handle($team, $request->validated());

        return back();
    }
}
