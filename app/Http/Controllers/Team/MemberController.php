<?php

namespace App\Http\Controllers\Team;

use App\Enums\MembershipStatus;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(Team $team): Response
    {
        return Inertia::render('team/members/index', [
            'team' => $team,
            'members' => $team->memberships()
                ->with(['user', 'plan'])
                ->paginate(15),
        ]);
    }

    public function show(Team $team, Membership $membership): Response
    {
        return Inertia::render('team/members/show', [
            'team' => $team,
            'membership' => $membership->load(['user', 'plan']),
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

    public function destroy(Team $team, Membership $membership): RedirectResponse
    {
        $membership->delete();

        return to_route('team.members.index', $team);
    }
}
