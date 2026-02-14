<?php

use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('requires authentication', function () {
    $this->get(route('account.dashboard'))
        ->assertRedirect(route('login'));
});

it('displays account dashboard for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('account.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/dashboard')
            ->has('memberships')
        );
});

it('shows user memberships on account dashboard', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $plan = MembershipPlan::factory()->create(['team_id' => $team->id]);
    Membership::factory()->create([
        'user_id' => $user->id,
        'team_id' => $team->id,
        'membership_plan_id' => $plan->id,
    ]);

    $this->actingAs($user)
        ->get(route('account.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/dashboard')
            ->has('memberships', 1)
        );
});
