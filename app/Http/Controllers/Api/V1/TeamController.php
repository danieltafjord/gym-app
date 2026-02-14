<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TeamResource;
use App\Models\Team;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $teams = Team::query()
            ->active()
            ->withCount('gyms')
            ->paginate(15);

        return TeamResource::collection($teams);
    }

    public function show(Team $team): TeamResource
    {
        $team->loadMissing(['membershipPlans' => fn ($query) => $query->active()->ordered()])
            ->loadCount(['gyms', 'memberships']);

        return new TeamResource($team);
    }
}
