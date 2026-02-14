<?php

use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('allows team owner to access', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('team.show', $team))
        ->assertSuccessful();
});

it('denies unrelated user access', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('team.show', $team))
        ->assertForbidden();
});

it('allows super admin access to any team', function () {
    $admin = User::factory()->create();
    setPermissionsTeamId(0);
    $admin->assignRole('super-admin');

    $team = Team::factory()->create();

    $this->actingAs($admin)
        ->get(route('team.show', $team))
        ->assertSuccessful();
});

it('allows team admin access', function () {
    $admin = User::factory()->create();
    $team = Team::factory()->create();

    setPermissionsTeamId($team->id);
    $admin->assignRole('team-admin');

    // Reload user to clear Spatie's role cache
    $admin = $admin->fresh();

    $this->actingAs($admin)
        ->get(route('team.show', $team))
        ->assertSuccessful();
});

it('blocks inactive team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id, 'is_active' => false]);

    $this->actingAs($user)
        ->get(route('team.show', $team))
        ->assertNotFound();
});

it('requires authentication', function () {
    $team = Team::factory()->create();

    $this->get(route('team.show', $team))
        ->assertRedirect(route('login'));
});
