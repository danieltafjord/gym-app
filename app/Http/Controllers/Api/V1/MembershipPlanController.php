<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MembershipPlanResource;
use App\Models\Team;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MembershipPlanController extends Controller
{
    public function index(Team $team): AnonymousResourceCollection
    {
        $plans = $team->membershipPlans()
            ->active()
            ->ordered()
            ->get();

        return MembershipPlanResource::collection($plans);
    }
}
