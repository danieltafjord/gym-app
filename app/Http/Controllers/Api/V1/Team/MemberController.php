<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MembershipResource;
use App\Models\Membership;
use App\Models\Team;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MemberController extends Controller
{
    public function index(Team $team): AnonymousResourceCollection
    {
        $memberships = $team->memberships()
            ->with(['user', 'plan'])
            ->paginate(15);

        return MembershipResource::collection($memberships);
    }

    public function show(Team $team, Membership $membership): MembershipResource
    {
        $membership->loadMissing(['user', 'plan']);

        return new MembershipResource($membership);
    }

    public function destroy(Team $team, Membership $membership): Response
    {
        $membership->delete();

        return response()->noContent();
    }
}
