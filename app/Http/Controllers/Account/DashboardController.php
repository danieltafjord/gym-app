<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Gym;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $memberships = $user->memberships()
            ->with(['team:id,name,slug', 'plan:id,name,price_cents,billing_period,plan_type,access_duration_value,access_duration_unit,activation_mode,requires_account,access_code_strategy,max_entries'])
            ->latest()
            ->get();

        // Get team IDs where user has active memberships
        $activeTeamIds = $memberships
            ->filter(fn ($m) => $m->is_currently_valid)
            ->pluck('team_id')
            ->unique();

        // Find gyms with occupancy tracking enabled in those teams
        $occupancyGyms = Gym::query()
            ->whereIn('team_id', $activeTeamIds)
            ->where('occupancy_tracking_enabled', true)
            ->where('show_occupancy_to_members', true)
            ->whereNotNull('max_capacity')
            ->where('is_active', true)
            ->with('team:id,name,slug')
            ->get()
            ->map(fn (Gym $gym) => [
                'gym_name' => $gym->name,
                'team_name' => $gym->team->name,
                'occupancy_url' => route('gym.occupancy', [
                    'team' => $gym->team->slug,
                    'gym' => $gym->slug,
                ]),
            ]);

        return Inertia::render('account/dashboard', [
            'memberships' => $memberships,
            'occupancyGyms' => $occupancyGyms,
        ]);
    }
}
