<?php

use App\Models\CheckIn;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\CheckInSeeder;
use Database\Seeders\GymSeeder;
use Database\Seeders\MembershipPlanSeeder;
use Database\Seeders\MembershipSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Database\Seeders\TeamSeeder;

it('seeds a single-gym team owned by admin super-admin', function () {
    $this->seed([
        RolesAndPermissionsSeeder::class,
        SuperAdminSeeder::class,
        TeamSeeder::class,
        GymSeeder::class,
    ]);

    $admin = User::query()
        ->where('email', 'admin@gymapp.com')
        ->firstOrFail();

    $team = Team::query()
        ->where('slug', 'core-fit')
        ->firstOrFail();

    expect($team->owner_id)->toBe($admin->id)
        ->and($team->name)->toBe('Core Fit')
        ->and($team->default_currency)->toBe('NOK')
        ->and($team->default_language)->toBe('nb')
        ->and($team->gyms()->count())->toBe(1);

    $this->actingAs($admin)
        ->get(route('team.show', $team))
        ->assertSuccessful();
});

it('seeds richer sample data for the core fit team', function () {
    $this->seed([
        RolesAndPermissionsSeeder::class,
        SuperAdminSeeder::class,
        TeamSeeder::class,
        GymSeeder::class,
        MembershipPlanSeeder::class,
        MembershipSeeder::class,
        CheckInSeeder::class,
    ]);

    $team = Team::query()
        ->where('slug', 'core-fit')
        ->firstOrFail();

    $corePlanNames = MembershipPlan::query()
        ->where('team_id', $team->id)
        ->orderBy('sort_order')
        ->pluck('name')
        ->all();
    $corePlanPrices = MembershipPlan::query()
        ->where('team_id', $team->id)
        ->orderBy('sort_order')
        ->pluck('price_cents')
        ->all();
    $memberCount = Membership::query()
        ->where('team_id', $team->id)
        ->count();
    $checkInCount = CheckIn::query()
        ->where('team_id', $team->id)
        ->count();

    expect($corePlanNames)->toBe(['Core', 'Core Velvære'])
        ->and($corePlanPrices)->toBe([39900, 49900])
        ->and($memberCount)->toBeGreaterThanOrEqual(6)
        ->and($checkInCount)->toBeGreaterThanOrEqual(72);
});
