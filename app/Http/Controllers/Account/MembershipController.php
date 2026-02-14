<?php

namespace App\Http\Controllers\Account;

use App\Actions\Membership\CancelMembership;
use App\Actions\Membership\CreateMembership;
use App\Actions\Membership\PauseMembership;
use App\Actions\Membership\ResumeMembership;
use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\StoreMembershipRequest;
use App\Models\Membership;
use App\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;

class MembershipController extends Controller
{
    public function store(StoreMembershipRequest $request, CreateMembership $action): RedirectResponse
    {
        $plan = MembershipPlan::findOrFail($request->validated('membership_plan_id'));
        $user = $request->user();

        $action->handle($user, $plan, $user->email, $user->name);

        return to_route('account.dashboard');
    }

    public function cancel(Membership $membership, CancelMembership $action): RedirectResponse
    {
        $action->handle($membership);

        return back();
    }

    public function pause(Membership $membership, PauseMembership $action): RedirectResponse
    {
        $action->handle($membership);

        return back();
    }

    public function resume(Membership $membership, ResumeMembership $action): RedirectResponse
    {
        $action->handle($membership);

        return back();
    }
}
