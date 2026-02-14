<?php

use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows create team form', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->get(route('team.create'))
        ->assertSuccessful();
});

it('creates a team', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('team.store'), [
            'name' => 'My New Gym',
            'description' => 'A great gym',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('teams', [
        'name' => 'My New Gym',
        'owner_id' => $user->id,
    ]);
});

it('validates team name is required', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('team.store'), [
            'name' => '',
        ])
        ->assertSessionHasErrors('name');
});

it('shows team dashboard to owner', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $this->actingAs($user)
        ->get(route('team.show', $team))
        ->assertSuccessful();
});

it('denies team access to non-owner', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $owner->id]);
    $this->actingAs($other)
        ->get(route('team.show', $team))
        ->assertForbidden();
});

it('allows super-admin to access any team', function () {
    $admin = User::factory()->create();
    setPermissionsTeamId(0);
    $admin->assignRole('super-admin');

    $team = Team::factory()->create();
    $this->actingAs($admin)
        ->get(route('team.show', $team))
        ->assertSuccessful();
});

it('updates team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $this->actingAs($user)
        ->patch(route('team.update', $team), [
            'name' => 'Updated Name',
        ])
        ->assertRedirect();

    expect($team->fresh()->name)->toBe('Updated Name');
});
