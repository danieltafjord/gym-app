<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        /** @var Request $request */
        /** @var User $user */
        $user = $request->user();

        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        // Super admins go to admin dashboard
        if ($this->isSuperAdmin($user)) {
            return redirect()->intended('/admin');
        }

        // Team owners go to their team
        $teamSlug = $this->resolveTeamSlug($user);

        if ($teamSlug) {
            return redirect()->intended("/team/{$teamSlug}");
        }

        // Everyone else goes to account
        return redirect()->intended('/account');
    }

    private function isSuperAdmin(User $user): bool
    {
        $currentTeamId = getPermissionsTeamId();
        setPermissionsTeamId(0);
        $isSuperAdmin = $user->hasRole('super-admin');
        setPermissionsTeamId($currentTeamId);

        return $isSuperAdmin;
    }

    private function resolveTeamSlug(User $user): ?string
    {
        if ($user->last_visited_team_slug) {
            return $user->last_visited_team_slug;
        }

        $firstTeam = $user->ownedTeams()->first();

        return $firstTeam?->slug;
    }
}
