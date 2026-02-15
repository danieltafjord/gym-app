<?php

use App\Enums\MembershipStatus;
use App\Models\Gym;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows the scanner page for team owner', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->get(route('team.check-in.scanner', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('team/check-in/scanner')
            ->has('team')
            ->has('gyms')
            ->has('settings')
        );
});

it('requires authentication for scanner page', function () {
    $team = Team::factory()->create();

    $this->get(route('team.check-in.scanner', $team))
        ->assertRedirect(route('login'));
});

it('requires team access for scanner page', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $this->actingAs($user)
        ->get(route('team.check-in.scanner', $team))
        ->assertForbidden();
});

it('processes a check-in and redirects back with flash', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'access_code' => 'TESTCODETESTCODETESTCODE',
        'status' => MembershipStatus::Active,
    ]);

    $this->actingAs($user)
        ->post(route('team.check-in.store', $team), [
            'access_code' => 'TESTCODETESTCODETESTCODE',
            'gym_id' => $gym->id,
            'method' => 'qr_scan',
        ])
        ->assertRedirect()
        ->assertSessionHas('checkInResult.success', true);

    $this->assertDatabaseHas('check_ins', [
        'membership_id' => $membership->id,
        'team_id' => $team->id,
        'gym_id' => $gym->id,
        'checked_in_by' => $user->id,
        'method' => 'qr_scan',
    ]);
});

it('validates access_code is required', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('team.check-in.store', $team), [
            'access_code' => '',
            'method' => 'qr_scan',
        ])
        ->assertSessionHasErrors('access_code');
});

it('validates method is required and valid', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('team.check-in.store', $team), [
            'access_code' => 'TESTCODETESTCODETESTCODE',
            'method' => 'invalid_method',
        ])
        ->assertSessionHasErrors('method');
});

it('shows check-in history page', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('team.check-ins.index', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('team/check-in/index')
            ->has('team')
            ->has('checkIns')
            ->has('gyms')
        );
});

it('requires authentication for history page', function () {
    $team = Team::factory()->create();

    $this->get(route('team.check-ins.index', $team))
        ->assertRedirect(route('login'));
});

it('allows super-admin to access check-in scanner', function () {
    $admin = User::factory()->create();
    setPermissionsTeamId(0);
    $admin->assignRole('super-admin');

    $team = Team::factory()->create();

    $this->actingAs($admin)
        ->get(route('team.check-in.scanner', $team))
        ->assertOk();
});
