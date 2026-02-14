<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $roles = [];

        if ($user) {
            $currentTeamId = getPermissionsTeamId();
            setPermissionsTeamId(0);
            if ($user->hasRole('super-admin')) {
                $roles[] = 'super-admin';
            }
            setPermissionsTeamId($currentTeamId);
        }

        $routeTeam = $request->route('team');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'roles' => $roles,
                'managedTeams' => $user?->ownedTeams()
                    ->select('id', 'name', 'slug')
                    ->get(),
            ],
            'currentTeam' => $routeTeam instanceof Team
                ? ['id' => $routeTeam->id, 'name' => $routeTeam->name, 'slug' => $routeTeam->slug]
                : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
