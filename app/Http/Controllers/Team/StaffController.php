<?php

namespace App\Http\Controllers\Team;

use App\Actions\Team\InviteTeamMember;
use App\Actions\Team\RemoveTeamMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\StoreTeamInvitationRequest;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(Team $team): Response
    {
        $staffMembers = $this->getStaffMembers($team);

        $pendingInvitations = TeamInvitation::where('team_id', $team->id)
            ->pending()
            ->with('inviter:id,name')
            ->latest()
            ->get();

        return Inertia::render('team/settings/staff', [
            'team' => $team,
            'staffMembers' => $staffMembers,
            'pendingInvitations' => $pendingInvitations,
        ]);
    }

    public function store(StoreTeamInvitationRequest $request, Team $team, InviteTeamMember $action): RedirectResponse
    {
        $action->handle($team, $request->user(), $request->validated());

        return back()->with('success', 'Invitation sent successfully.');
    }

    public function destroyInvitation(Team $team, TeamInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->team_id === $team->id, 404);

        $invitation->delete();

        return back()->with('success', 'Invitation cancelled.');
    }

    public function removeStaff(Team $team, User $user, RemoveTeamMember $action): RedirectResponse
    {
        $action->handle($team, $user);

        return back()->with('success', 'Staff member removed.');
    }

    /**
     * @return array<int, array{id: int, name: string, email: string, role: string}>
     */
    private function getStaffMembers(Team $team): array
    {
        setPermissionsTeamId($team->id);

        $owner = $team->owner;
        $staff = [
            [
                'id' => $owner->id,
                'name' => $owner->name,
                'email' => $owner->email,
                'role' => 'team-owner',
            ],
        ];

        $admins = User::role('team-admin', 'web')->get();

        foreach ($admins as $admin) {
            if ($admin->id === $owner->id) {
                continue;
            }
            $staff[] = [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'team-admin',
            ];
        }

        return $staff;
    }
}
