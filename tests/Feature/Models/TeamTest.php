<?php

use App\Models\Gym;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;

it('can be created with factory', function () {
    $team = Team::factory()->create();
    expect($team)->toBeInstanceOf(Team::class)
        ->and($team->name)->not->toBeEmpty()
        ->and($team->slug)->not->toBeEmpty()
        ->and($team->is_active)->toBeTrue();
});

it('resolves route key by slug', function () {
    $team = Team::factory()->create();
    expect($team->getRouteKeyName())->toBe('slug');
});

it('has owner relationship', function () {
    $team = Team::factory()->create();
    expect($team->owner)->toBeInstanceOf(User::class);
});

it('has gyms relationship', function () {
    $team = Team::factory()->create();
    Gym::factory()->count(3)->create(['team_id' => $team->id]);
    expect($team->gyms)->toHaveCount(3);
});

it('has membership plans relationship', function () {
    $team = Team::factory()->create();
    MembershipPlan::factory()->count(2)->create(['team_id' => $team->id]);
    expect($team->membershipPlans)->toHaveCount(2);
});

it('has memberships relationship', function () {
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    Membership::factory()->count(2)->create(['team_id' => $team->id, 'membership_plan_id' => $plan->id]);
    expect($team->memberships)->toHaveCount(2);
});

it('scopes to active teams', function () {
    Team::factory()->create(['is_active' => true]);
    Team::factory()->create(['is_active' => false]);
    expect(Team::active()->count())->toBe(1);
});
