<?php

use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows plans index with price_formatted', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $plan = MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'price_cents' => 4999,
    ]);

    $this->actingAs($user)
        ->get(route('team.plans.index', $team))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('team/plans/index')
            ->has('plans.data', 1)
            ->where('plans.data.0.price_formatted', '49.99')
        );
});
