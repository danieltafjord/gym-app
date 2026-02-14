<?php

use App\Models\Gym;
use App\Models\MembershipPlan;
use App\Models\Team;
use Inertia\Testing\AssertableInertia as Assert;

it('shows an active team page', function () {
    $team = Team::factory()->create(['is_active' => true]);
    Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->get(route('public.team', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/team')
            ->has('team')
            ->has('team.gyms', 1)
        );
});

it('returns 404 for inactive team', function () {
    $team = Team::factory()->create(['is_active' => false]);

    $this->get(route('public.team', $team))
        ->assertNotFound();
});

it('shows a gym page with plans', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    MembershipPlan::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->get(route('public.gym', ['team' => $team, 'gym' => $gym->slug]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/gym')
            ->has('team')
            ->has('gym')
            ->has('plans', 1)
        );
});

it('returns 404 for inactive gym', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => false]);

    $this->get(route('public.gym', ['team' => $team, 'gym' => $gym->slug]))
        ->assertNotFound();
});

it('returns 404 for gym slug not belonging to team', function () {
    $team = Team::factory()->create(['is_active' => true]);

    $this->get(route('public.gym', ['team' => $team, 'gym' => 'nonexistent-gym']))
        ->assertNotFound();
});
