<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Team\CreateTeam;
use App\Actions\Team\DeleteTeam;
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
    public function index(): Response
    {
        return Inertia::render('admin/teams/index', [
            'teams' => Team::query()
                ->with('owner')
                ->withCount(['gyms', 'memberships'])
                ->paginate(15),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/teams/create');
    }

    public function store(StoreTeamRequest $request, CreateTeam $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return to_route('admin.teams.index');
    }

    public function edit(Team $team): Response
    {
        return Inertia::render('admin/teams/edit', [
            'team' => $team,
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team, UpdateTeam $action): RedirectResponse
    {
        $action->handle($team, $request->validated());

        return back();
    }

    public function destroy(Team $team, DeleteTeam $action): RedirectResponse
    {
        $action->handle($team);

        return to_route('admin.teams.index');
    }
}
