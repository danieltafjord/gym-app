<?php

namespace App\Http\Controllers\Team;

use App\Actions\MembershipPlan\CreateMembershipPlan;
use App\Actions\MembershipPlan\DeleteMembershipPlan;
use App\Actions\MembershipPlan\UpdateMembershipPlan;
use App\Http\Controllers\Controller;
use App\Http\Requests\MembershipPlan\StoreMembershipPlanRequest;
use App\Http\Requests\MembershipPlan\UpdateMembershipPlanRequest;
use App\Models\MembershipPlan;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MembershipPlanController extends Controller
{
    public function index(Team $team): Response
    {
        return Inertia::render('team/plans/index', [
            'team' => $team,
            'plans' => $team->membershipPlans()->ordered()->paginate(15),
        ]);
    }

    public function create(Team $team): Response
    {
        return Inertia::render('team/plans/create', [
            'team' => $team,
        ]);
    }

    public function store(StoreMembershipPlanRequest $request, Team $team, CreateMembershipPlan $action): RedirectResponse
    {
        $action->handle($team, $request->validated());

        return to_route('team.plans.index', $team);
    }

    public function edit(Team $team, MembershipPlan $plan): Response
    {
        return Inertia::render('team/plans/edit', [
            'team' => $team,
            'plan' => $plan,
        ]);
    }

    public function update(UpdateMembershipPlanRequest $request, Team $team, MembershipPlan $plan, UpdateMembershipPlan $action): RedirectResponse
    {
        $action->handle($plan, $request->validated());

        return back();
    }

    public function destroy(Team $team, MembershipPlan $plan, DeleteMembershipPlan $action): RedirectResponse
    {
        $action->handle($plan);

        return to_route('team.plans.index', $team);
    }
}
