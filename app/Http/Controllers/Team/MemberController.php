<?php

namespace App\Http\Controllers\Team;

use App\Actions\Membership\CreateMembership;
use App\Actions\Membership\ExtendMembership;
use App\Actions\Membership\UpdateMembershipDetails;
use App\Enums\MembershipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\ExtendMembershipRequest;
use App\Http\Requests\Membership\StoreTeamMemberRequest;
use App\Http\Requests\Membership\UpdateMembershipDetailsRequest;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(Request $request, Team $team): Response
    {
        $members = $team->memberships()
            ->with(['user', 'plan'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->value();
                $query->where(function ($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->value());
            })
            ->when($request->filled('plan'), function ($query) use ($request) {
                $query->where('membership_plan_id', $request->integer('plan'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('team/members/index', [
            'team' => $team,
            'members' => $members,
            'plans' => $team->membershipPlans()->active()->ordered()->get(),
            'filters' => [
                'search' => $request->string('search')->value(),
                'status' => $request->string('status')->value(),
                'plan' => $request->string('plan')->value(),
            ],
        ]);
    }

    public function create(Team $team): Response
    {
        return Inertia::render('team/members/create', [
            'team' => $team,
            'plans' => $team->membershipPlans()->active()->ordered()->get(),
        ]);
    }

    public function store(StoreTeamMemberRequest $request, Team $team, CreateMembership $createMembership): RedirectResponse
    {
        $validated = $request->validated();
        $plan = MembershipPlan::findOrFail($validated['membership_plan_id']);

        $startsAt = ! empty($validated['starts_at']) ? Carbon::parse($validated['starts_at']) : null;

        $createMembership->handle(
            user: null,
            plan: $plan,
            email: $validated['email'],
            customerName: $validated['customer_name'],
            customerPhone: $validated['customer_phone'] ?? null,
            startsAt: $startsAt,
        );

        return to_route('team.members.index', $team);
    }

    public function show(Team $team, Membership $membership): Response
    {
        $membership->load(['user', 'plan']);

        $checkIns = $membership->checkIns()
            ->with(['gym', 'checkedInBy'])
            ->latest()
            ->paginate(10, ['*'], 'check_ins_page');

        $notes = $membership->notes()
            ->with('author')
            ->latest()
            ->get();

        return Inertia::render('team/members/show', [
            'team' => $team,
            'membership' => $membership,
            'plans' => $team->membershipPlans()->active()->ordered()->get(),
            'checkIns' => $checkIns,
            'notes' => $notes,
        ]);
    }

    public function update(Request $request, Team $team, Membership $membership): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(MembershipStatus::cases())],
        ]);

        $membership->update($validated);

        return back();
    }

    public function updateDetails(
        UpdateMembershipDetailsRequest $request,
        Team $team,
        Membership $membership,
        UpdateMembershipDetails $action,
    ): RedirectResponse {
        $action->handle($membership, $request->validated());

        return back();
    }

    public function extend(
        ExtendMembershipRequest $request,
        Team $team,
        Membership $membership,
        ExtendMembership $action,
    ): RedirectResponse {
        $validated = $request->validated();

        $action->handle(
            $membership,
            Carbon::parse($validated['ends_at']),
            $validated['reactivate'] ?? false,
        );

        return back();
    }

    public function destroy(Team $team, Membership $membership): RedirectResponse
    {
        $membership->delete();

        return to_route('team.members.index', $team);
    }
}
