<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Actions\Gym\CreateGym;
use App\Actions\Gym\DeleteGym;
use App\Actions\Gym\UpdateGym;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gym\StoreGymRequest;
use App\Http\Requests\Gym\UpdateGymRequest;
use App\Http\Resources\V1\GymResource;
use App\Models\Gym;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GymController extends Controller
{
    public function index(Team $team): AnonymousResourceCollection
    {
        $gyms = $team->gyms()->paginate(15);

        return GymResource::collection($gyms);
    }

    public function store(StoreGymRequest $request, Team $team, CreateGym $action): JsonResponse
    {
        $gym = $action->handle($team, $request->validated());

        return (new GymResource($gym))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Team $team, Gym $gym): GymResource
    {
        return new GymResource($gym);
    }

    public function update(UpdateGymRequest $request, Team $team, Gym $gym, UpdateGym $action): GymResource
    {
        $gym = $action->handle($gym, $request->validated());

        return new GymResource($gym);
    }

    public function destroy(Team $team, Gym $gym, DeleteGym $action): Response
    {
        $action->handle($gym);

        return response()->noContent();
    }
}
