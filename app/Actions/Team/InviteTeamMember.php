<?php

namespace App\Actions\Team;

use App\Mail\TeamInvitationMail;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InviteTeamMember
{
    /**
     * @param  array{email: string, role: string}  $data
     */
    public function handle(Team $team, User $inviter, array $data): TeamInvitation
    {
        $this->ensureNoDuplicatePendingInvite($team, $data['email']);
        $this->ensureNotExistingStaff($team, $data['email']);

        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'email' => $data['email'],
            'role' => $data['role'],
            'token' => Str::random(64),
            'invited_by' => $inviter->id,
            'expires_at' => now()->addDays(7),
        ]);

        $invitation->load('team');

        Mail::to($data['email'])->queue(new TeamInvitationMail($invitation));

        return $invitation;
    }

    private function ensureNoDuplicatePendingInvite(Team $team, string $email): void
    {
        $exists = TeamInvitation::where('team_id', $team->id)
            ->where('email', $email)
            ->pending()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'email' => 'An invitation has already been sent to this email address.',
            ]);
        }
    }

    private function ensureNotExistingStaff(Team $team, string $email): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return;
        }

        setPermissionsTeamId($team->id);
        $hasRole = $user->hasAnyRole(['team-owner', 'team-admin']);
        $user->unsetRelation('roles');

        if ($hasRole) {
            throw ValidationException::withMessages([
                'email' => 'This user is already a staff member of this team.',
            ]);
        }
    }
}
