<?php

namespace App\Http\Middleware;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;
use Spatie\Permission\Models\Role;

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
        $isSuperAdmin = false;

        if ($user) {
            $currentTeamId = getPermissionsTeamId();
            setPermissionsTeamId(0);
            if ($user->hasRole('super-admin')) {
                $roles[] = 'super-admin';
                $isSuperAdmin = true;
            }
            setPermissionsTeamId($currentTeamId);
        }

        $managedTeams = $this->resolveManagedTeams($user, $isSuperAdmin);
        $currentTeam = $this->resolveCurrentTeam($request, $managedTeams);
        $singleGym = $this->resolveSingleGym($currentTeam);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'roles' => $roles,
                'managedTeams' => $managedTeams,
            ],
            'currentTeam' => $currentTeam
                ? [
                    'id' => $currentTeam->id,
                    'name' => $currentTeam->name,
                    'slug' => $currentTeam->slug,
                    'singleGym' => $singleGym,
                ]
                : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'checkInResult' => fn () => $request->session()->get('checkInResult'),
            ],
        ];
    }

    /**
     * @param  Collection<int, Team>  $managedTeams
     */
    private function resolveCurrentTeam(Request $request, Collection $managedTeams): ?Team
    {
        $routeTeam = $request->route('team');

        if ($routeTeam instanceof Team) {
            return $routeTeam;
        }

        $user = $request->user();

        if (! $user || $managedTeams->isEmpty()) {
            return null;
        }

        if ($user->last_visited_team_slug) {
            $lastVisitedTeam = $managedTeams->firstWhere('slug', $user->last_visited_team_slug);

            if ($lastVisitedTeam instanceof Team) {
                return $lastVisitedTeam;
            }
        }

        $firstTeam = $managedTeams->first();

        return $firstTeam instanceof Team ? $firstTeam : null;
    }

    /**
     * @return array{id:int,name:string,slug:string}|null
     */
    private function resolveSingleGym(?Team $team): ?array
    {
        if (! $team) {
            return null;
        }

        $teamGyms = $team->gyms()
            ->select('id', 'name', 'slug')
            ->orderBy('id')
            ->limit(2)
            ->get();

        if ($teamGyms->count() !== 1) {
            return null;
        }

        $singleGym = $teamGyms->first();

        return [
            'id' => $singleGym->id,
            'name' => $singleGym->name,
            'slug' => $singleGym->slug,
        ];
    }

    /**
     * @return Collection<int, Team>
     */
    private function resolveManagedTeams(?User $user, bool $isSuperAdmin): Collection
    {
        if (! $user) {
            return collect();
        }

        if ($isSuperAdmin) {
            return Team::query()
                ->active()
                ->select('id', 'name', 'slug')
                ->orderBy('name')
                ->get();
        }

        $teamIds = $user->ownedTeams()
            ->active()
            ->pluck('id');

        $teamRoleIds = Role::query()
            ->whereIn('name', ['team-owner', 'team-admin'])
            ->pluck('id');

        if ($teamRoleIds->isNotEmpty()) {
            $tableNames = config('permission.table_names');
            $columnNames = config('permission.column_names');

            $modelHasRolesTable = $tableNames['model_has_roles'] ?? 'model_has_roles';
            $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_id';
            $rolePivotKey = $columnNames['role_pivot_key'] ?? 'role_id';
            $teamForeignKey = $columnNames['team_foreign_key'] ?? 'team_id';

            $scopedTeamIds = DB::table($modelHasRolesTable)
                ->where('model_type', User::class)
                ->where($modelMorphKey, $user->id)
                ->whereIn($rolePivotKey, $teamRoleIds)
                ->whereNotNull($teamForeignKey)
                ->pluck($teamForeignKey);

            $teamIds = $teamIds->merge($scopedTeamIds);
        }

        $teamIds = $teamIds->unique()->values();

        if ($teamIds->isEmpty()) {
            return collect();
        }

        return Team::query()
            ->active()
            ->whereIn('id', $teamIds)
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();
    }
}
