<?php

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;

it('can be created with factory', function () {
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
    ]);
    expect($membership)->toBeInstanceOf(Membership::class)
        ->and($membership->status)->toBe(MembershipStatus::Active);
});

it('casts status to enum', function () {
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
    ]);
    expect($membership->status)->toBeInstanceOf(MembershipStatus::class);
});

it('has user relationship', function () {
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
    ]);
    expect($membership->user)->toBeInstanceOf(User::class);
});

it('has team relationship', function () {
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
    ]);
    expect($membership->team)->toBeInstanceOf(Team::class);
});

it('has plan relationship', function () {
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    $membership = Membership::factory()->create([
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
    ]);
    expect($membership->plan)->toBeInstanceOf(MembershipPlan::class);
});

it('scopes to active memberships', function () {
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    Membership::factory()->create(['team_id' => $team->id, 'membership_plan_id' => $plan->id, 'status' => MembershipStatus::Active]);
    Membership::factory()->create(['team_id' => $team->id, 'membership_plan_id' => $plan->id, 'status' => MembershipStatus::Cancelled]);
    expect(Membership::active()->count())->toBe(1);
});

it('scopes to team', function () {
    $team1 = Team::factory()->create();
    $team2 = Team::factory()->create();
    $plan1 = MembershipPlan::factory()->create(['team_id' => $team1->id]);
    $plan2 = MembershipPlan::factory()->create(['team_id' => $team2->id]);
    Membership::factory()->count(2)->create(['team_id' => $team1->id, 'membership_plan_id' => $plan1->id]);
    Membership::factory()->create(['team_id' => $team2->id, 'membership_plan_id' => $plan2->id]);
    expect(Membership::forTeam($team1->id)->count())->toBe(2);
});
