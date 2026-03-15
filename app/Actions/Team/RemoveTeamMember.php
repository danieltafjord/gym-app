<?php

namespace App\Actions\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RemoveTeamMember
{
    public function handle(Team $team, User $user): void
    {
        if ($team->owner_id === $user->id) {
            throw ValidationException::withMessages([
                'user' => 'The team owner cannot be removed.',
            ]);
        }

        setPermissionsTeamId($team->id);
        $user->removeRole('team-admin');
        $user->unsetRelation('roles');
    }
}
