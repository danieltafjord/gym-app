<?php

namespace App\Http\Controllers\Team;

use App\Actions\Team\CreateTeam;
use App\Actions\Team\UpdateTeam;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('team/create');
    }

    public function store(StoreTeamRequest $request, CreateTeam $action): RedirectResponse
    {
        $team = $action->handle($request->user(), $request->validated());

        return to_route('team.show', $team);
    }

    public function show(Team $team): Response
    {
        return Inertia::render('team/show', [
            'team' => $team->loadCount(['gyms', 'memberships'])
                ->load('membershipPlans'),
            'recentMemberships' => $team->memberships()
                ->with(['user', 'plan'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function edit(Team $team): Response
    {
        return Inertia::render('team/edit', [
            'team' => $team,
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team, UpdateTeam $action): RedirectResponse
    {
        $action->handle($team, $request->validated());

        return back();
    }
}
