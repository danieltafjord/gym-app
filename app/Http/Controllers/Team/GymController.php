<?php

namespace App\Http\Controllers\Team;

use App\Actions\Gym\CreateGym;
use App\Actions\Gym\DeleteGym;
use App\Actions\Gym\UpdateGym;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\StoreGymRequest;
use App\Http\Requests\Gym\UpdateGymRequest;
use App\Models\Gym;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GymController extends Controller
{
    public function index(Team $team): Response|RedirectResponse
    {
        $teamGyms = $team->gyms()
            ->select('id', 'slug')
            ->orderBy('id')
            ->limit(2)
            ->get();

        if ($teamGyms->count() === 1) {
            return to_route('team.gyms.settings.general', [
                'team' => $team,
                'gym' => $teamGyms->first(),
            ]);
        }

        return Inertia::render('team/gyms/index', [
            'team' => $team,
            'gyms' => $team->gyms()->paginate(15),
        ]);
    }

    public function create(Team $team): Response
    {
        return Inertia::render('team/gyms/create', [
            'team' => $team,
        ]);
    }

    public function store(StoreGymRequest $request, Team $team, CreateGym $action): RedirectResponse
    {
        $action->handle($team, $request->validated());

        return to_route('team.gyms.index', $team);
    }

    public function edit(Team $team, Gym $gym): Response
    {
        return Inertia::render('team/gyms/settings/general', [
            'team' => $team,
            'gym' => $gym,
        ]);
    }

    public function occupancy(Team $team, Gym $gym): Response
    {
        return Inertia::render('team/gyms/settings/occupancy', [
            'team' => $team,
            'gym' => $gym,
        ]);
    }

    public function update(UpdateGymRequest $request, Team $team, Gym $gym, UpdateGym $action): RedirectResponse
    {
        $action->handle($gym, $request->validated());

        return back();
    }

    public function destroy(Team $team, Gym $gym, DeleteGym $action): RedirectResponse
    {
        $action->handle($gym);

        return to_route('team.gyms.index', $team);
    }
}
