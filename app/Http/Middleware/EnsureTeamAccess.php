<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $team = $request->route('team');

        if (! $team instanceof Team) {
            abort(404);
        }

        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->last_visited_team_slug !== $team->slug) {
            $user->updateQuietly(['last_visited_team_slug' => $team->slug]);
        }

        if ($team->owner_id === $user->id) {
            return $next($request);
        }

        // Check for global super-admin role
        setPermissionsTeamId(0);
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // Clear cached roles before checking team-scoped roles
        $user->unsetRelation('roles');

        // Check for team-scoped roles
        setPermissionsTeamId($team->id);
        if ($user->hasRole(['team-owner', 'team-admin'])) {
            return $next($request);
        }

        abort(403);
    }
}
