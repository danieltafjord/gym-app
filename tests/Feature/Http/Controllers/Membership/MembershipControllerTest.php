<?php

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('creates a membership', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->post(route('account.memberships.store'), [
            'membership_plan_id' => $plan->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('memberships', [
        'user_id' => $user->id,
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
        'status' => 'active',
    ]);
});

it('cancels a membership', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'user_id' => $user->id,
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
    ]);

    $this->actingAs($user)
        ->patch(route('account.memberships.cancel', $membership))
        ->assertRedirect();

    expect($membership->fresh()->status)->toBe(MembershipStatus::Cancelled);
});

it('pauses a membership', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'user_id' => $user->id,
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
    ]);

    $this->actingAs($user)
        ->patch(route('account.memberships.pause', $membership))
        ->assertRedirect();

    expect($membership->fresh()->status)->toBe(MembershipStatus::Paused);
});

it('resumes a paused membership', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->paused()->create([
        'user_id' => $user->id,
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
    ]);

    $this->actingAs($user)
        ->patch(route('account.memberships.resume', $membership))
        ->assertRedirect();

    expect($membership->fresh()->status)->toBe(MembershipStatus::Active);
});
