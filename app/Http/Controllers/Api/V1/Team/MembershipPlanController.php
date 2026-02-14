<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Actions\MembershipPlan\CreateMembershipPlan;
use App\Actions\MembershipPlan\DeleteMembershipPlan;
use App\Actions\MembershipPlan\UpdateMembershipPlan;
use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipPlan\StoreMembershipPlanRequest;
use App\Http\Requests\MembershipPlan\UpdateMembershipPlanRequest;
use App\Http\Resources\V1\MembershipPlanResource;
use App\Models\MembershipPlan;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MembershipPlanController extends Controller
{
    public function index(Team $team): AnonymousResourceCollection
    {
        $plans = $team->membershipPlans()
            ->ordered()
            ->paginate(15);

        return MembershipPlanResource::collection($plans);
    }

    public function store(StoreMembershipPlanRequest $request, Team $team, CreateMembershipPlan $action): JsonResponse
    {
        $plan = $action->handle($team, $request->validated());

        return (new MembershipPlanResource($plan))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Team $team, MembershipPlan $plan): MembershipPlanResource
    {
        return new MembershipPlanResource($plan);
    }

    public function update(UpdateMembershipPlanRequest $request, Team $team, MembershipPlan $plan, UpdateMembershipPlan $action): MembershipPlanResource
    {
        $plan = $action->handle($plan, $request->validated());

        return new MembershipPlanResource($plan);
    }

    public function destroy(Team $team, MembershipPlan $plan, DeleteMembershipPlan $action): Response
    {
        $action->handle($plan);

        return response()->noContent();
    }
}
