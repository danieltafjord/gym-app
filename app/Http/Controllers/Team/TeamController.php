<?php

namespace App\Http\Controllers\Team;

use App\Actions\Team\CreateTeam;
use App\Actions\Team\GetTeamDashboardStats;
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

    public function show(Team $team, GetTeamDashboardStats $statsAction): Response
    {
        $occupancyGyms = $team->gyms()
            ->where('occupancy_tracking_enabled', true)
            ->whereNotNull('max_capacity')
            ->active()
            ->get()
            ->map(fn ($gym) => [
                'gym_name' => $gym->name,
                'occupancy_url' => route('gym.occupancy', [
                    'team' => $team->slug,
                    'gym' => $gym->slug,
                ]),
            ]);

        return Inertia::render('team/show', [
            'team' => $team->loadCount(['gyms', 'memberships'])
                ->load('membershipPlans'),
            'occupancyGyms' => $occupancyGyms,
            'stats' => $statsAction->dashboardStats($team),
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
