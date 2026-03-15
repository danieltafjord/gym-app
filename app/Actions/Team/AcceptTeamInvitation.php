<?php

namespace App\Actions\Team;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AcceptTeamInvitation
{
    public function handle(TeamInvitation $invitation, User $user): void
    {
        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'token' => 'This invitation is no longer valid.',
            ]);
        }

        if ($invitation->email !== $user->email) {
            throw ValidationException::withMessages([
                'token' => 'This invitation was sent to a different email address.',
            ]);
        }

        setPermissionsTeamId($invitation->team_id);
        $user->assignRole($invitation->role);
        $user->unsetRelation('roles');

        $invitation->update(['accepted_at' => now()]);
    }
}
