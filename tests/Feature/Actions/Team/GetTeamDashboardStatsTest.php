<?php

use App\Actions\Team\GetTeamDashboardStats;
use App\Enums\MembershipStatus;
use App\Models\CheckIn;
use App\Models\Gym;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->plan = MembershipPlan::factory()->create([
        'team_id' => $this->team->id,
        'price_cents' => 5000,
        'billing_period' => \App\Enums\BillingPeriod::Monthly,
    ]);
    $this->gym = Gym::factory()->create(['team_id' => $this->team->id]);
});

it('calculates active members count', function () {
    Membership::factory()->count(3)->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'status' => MembershipStatus::Active,
    ]);

    Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'status' => MembershipStatus::Cancelled,
    ]);

    $stats = app(GetTeamDashboardStats::class)->handle($this->team);

    expect($stats['active_members'])->toBe(3);
});

it('calculates MRR from active memberships', function () {
    Membership::factory()->count(2)->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
        'status' => MembershipStatus::Active,
    ]);

    $stats = app(GetTeamDashboardStats::class)->handle($this->team);

    expect($stats['mrr'])->toBe(100.0);
});

it('counts check-ins today', function () {
    $membership = Membership::factory()->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);

    CheckIn::factory()->count(5)->create([
        'team_id' => $this->team->id,
        'membership_id' => $membership->id,
        'gym_id' => $this->gym->id,
    ]);

    $stats = app(GetTeamDashboardStats::class)->handle($this->team);

    expect($stats['check_ins_today'])->toBe(5);
});

it('counts new members this month', function () {
    Membership::factory()->count(4)->create([
        'team_id' => $this->team->id,
        'membership_plan_id' => $this->plan->id,
    ]);

    $stats = app(GetTeamDashboardStats::class)->handle($this->team);

    expect($stats['new_members_this_month'])->toBe(4);
});

it('returns zero churn rate with no history', function () {
    $stats = app(GetTeamDashboardStats::class)->handle($this->team);

    expect($stats['churn_rate'])->toBe(0.0);
});

it('returns member growth series', function () {
    $series = app(GetTeamDashboardStats::class)->memberGrowth($this->team);

    expect($series)->toHaveCount(12)
        ->and($series[0])->toHaveKeys(['label', 'value']);
});

it('returns daily check-ins series', function () {
    $series = app(GetTeamDashboardStats::class)->checkInsDaily($this->team);

    expect($series)->toHaveCount(30)
        ->and($series[0])->toHaveKeys(['label', 'value']);
});
