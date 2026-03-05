<?php

use App\Models\Gym;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('redirects gym index to settings when team has exactly one gym', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->get(route('team.gyms.index', $team))
        ->assertRedirect(route('team.gyms.settings.general', [
            'team' => $team,
            'gym' => $gym,
        ]));
});

it('shows gym index when team has multiple gyms', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    Gym::factory()->count(2)->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->get(route('team.gyms.index', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('team/gyms/index')
            ->has('gyms.data', 2)
        );
});
