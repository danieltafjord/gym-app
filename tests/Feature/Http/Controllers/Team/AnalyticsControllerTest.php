<?php

use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows analytics page to team owner', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('team.analytics', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('team/analytics')
            ->has('team')
            ->has('stats')
            ->has('recentMemberships')
        );
});

it('denies analytics access to non-owner', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);

    $this->actingAs($other)
        ->get(route('team.analytics', $team))
        ->assertForbidden();
});

it('returns all expected stats', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('team.analytics', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('stats.active_members')
            ->has('stats.mrr')
            ->has('stats.check_ins_today')
            ->has('stats.new_members_this_month')
            ->has('stats.churn_rate')
        );
});
