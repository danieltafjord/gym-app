<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Membership\CancelMembership;
use App\Actions\Membership\CreateMembership;
use App\Actions\Membership\PauseMembership;
use App\Actions\Membership\ResumeMembership;
use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\StoreMembershipRequest;
use App\Http\Resources\V1\MembershipResource;
use App\Models\Membership;
use App\Models\MembershipPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MembershipController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $memberships = $request->user()
            ->memberships()
            ->with(['team', 'plan'])
            ->paginate(15);

        return MembershipResource::collection($memberships);
    }

    public function store(StoreMembershipRequest $request, CreateMembership $action): JsonResponse
    {
        $plan = MembershipPlan::findOrFail($request->validated('membership_plan_id'));

        $membership = $action->handle($request->user(), $plan);

        return (new MembershipResource($membership))
            ->response()
            ->setStatusCode(201);
    }

    public function cancel(Membership $membership, CancelMembership $action): MembershipResource
    {
        $membership = $action->handle($membership);

        return new MembershipResource($membership);
    }

    public function pause(Membership $membership, PauseMembership $action): MembershipResource
    {
        $membership = $action->handle($membership);

        return new MembershipResource($membership);
    }

    public function resume(Membership $membership, ResumeMembership $action): MembershipResource
    {
        $membership = $action->handle($membership);

        return new MembershipResource($membership);
    }
}
