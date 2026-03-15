<?php

namespace App\Http\Controllers\Team;

use App\Actions\Team\GetTeamDashboardStats;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function show(Team $team, GetTeamDashboardStats $statsAction): Response
    {
        return Inertia::render('team/analytics', [
            'team' => $team,
            'stats' => $statsAction->handle($team),
            'recentMemberships' => $team->memberships()
                ->with(['user', 'plan'])
                ->latest()
                ->limit(10)
                ->get(),
            'memberGrowth' => Inertia::defer(fn () => $statsAction->memberGrowth($team), 'charts'),
            'checkInsDaily' => Inertia::defer(fn () => $statsAction->checkInsDaily($team), 'charts'),
        ]);
    }
}
