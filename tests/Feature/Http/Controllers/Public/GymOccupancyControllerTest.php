<?php

use App\Models\CheckIn;
use App\Models\Gym;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;

it('returns hourly occupancy data for authenticated user', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'occupancy_tracking_enabled' => true,
        'max_capacity' => 100,
    ]);
    $membership = Membership::factory()->create(['team_id' => $team->id]);

    CheckIn::factory()->create([
        'team_id' => $team->id,
        'gym_id' => $gym->id,
        'membership_id' => $membership->id,
        'created_at' => now()->startOfDay()->addHours(9),
    ]);
    CheckIn::factory()->create([
        'team_id' => $team->id,
        'gym_id' => $gym->id,
        'membership_id' => $membership->id,
        'created_at' => now()->startOfDay()->addHours(9)->addMinutes(30),
    ]);
    CheckIn::factory()->create([
        'team_id' => $team->id,
        'gym_id' => $gym->id,
        'membership_id' => $membership->id,
        'created_at' => now()->startOfDay()->addHours(17),
    ]);

    $this->actingAs($user)
        ->getJson(route('gym.occupancy', ['team' => $team, 'gym' => $gym->slug]))
        ->assertSuccessful()
        ->assertJsonStructure([
            'date',
            'is_today',
            'hours',
            'current_hour',
            'current_count',
            'max_capacity',
        ])
        ->assertJsonFragment(['max_capacity' => 100])
        ->assertJsonPath('hours.9', 2)
        ->assertJsonPath('hours.17', 1);
});

it('requires authentication', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'occupancy_tracking_enabled' => true,
        'max_capacity' => 100,
    ]);

    $this->getJson(route('gym.occupancy', ['team' => $team, 'gym' => $gym->slug]))
        ->assertUnauthorized();
});

it('returns 404 when occupancy tracking is disabled', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'occupancy_tracking_enabled' => false,
        'max_capacity' => 100,
    ]);

    $this->actingAs($user)
        ->getJson(route('gym.occupancy', ['team' => $team, 'gym' => $gym->slug]))
        ->assertNotFound();
});

it('returns 404 when max capacity is not set', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'occupancy_tracking_enabled' => true,
        'max_capacity' => null,
    ]);

    $this->actingAs($user)
        ->getJson(route('gym.occupancy', ['team' => $team, 'gym' => $gym->slug]))
        ->assertNotFound();
});

it('accepts a date parameter for past days', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'occupancy_tracking_enabled' => true,
        'max_capacity' => 50,
    ]);
    $membership = Membership::factory()->create(['team_id' => $team->id]);

    $yesterday = now()->subDay();

    CheckIn::factory()->create([
        'team_id' => $team->id,
        'gym_id' => $gym->id,
        'membership_id' => $membership->id,
        'created_at' => $yesterday->copy()->startOfDay()->addHours(10),
    ]);

    $this->actingAs($user)
        ->getJson(route('gym.occupancy', [
            'team' => $team,
            'gym' => $gym->slug,
            'date' => $yesterday->toDateString(),
        ]))
        ->assertSuccessful()
        ->assertJsonPath('is_today', false)
        ->assertJsonPath('hours.10', 1);
});

it('returns zero counts for hours with no check-ins', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'occupancy_tracking_enabled' => true,
        'max_capacity' => 50,
    ]);

    $this->actingAs($user)
        ->getJson(route('gym.occupancy', ['team' => $team, 'gym' => $gym->slug]))
        ->assertSuccessful()
        ->assertJsonPath('hours.0', 0)
        ->assertJsonPath('hours.12', 0)
        ->assertJsonPath('hours.23', 0);
});

it('passes occupancy gyms to account dashboard for active memberships', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'occupancy_tracking_enabled' => true,
        'show_occupancy_to_members' => true,
        'max_capacity' => 80,
    ]);
    Membership::factory()->create([
        'user_id' => $user->id,
        'team_id' => $team->id,
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->get(route('account.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('occupancyGyms', 1)
            ->where('occupancyGyms.0.gym_name', $gym->name)
        );
});

it('returns null predictions when predictions are disabled', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'occupancy_tracking_enabled' => true,
        'show_occupancy_predictions' => false,
        'max_capacity' => 100,
    ]);

    $this->actingAs($user)
        ->getJson(route('gym.occupancy', ['team' => $team, 'gym' => $gym->slug]))
        ->assertSuccessful()
        ->assertJsonPath('predictions', null);
});

it('returns null predictions when not enough historical data', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'occupancy_tracking_enabled' => true,
        'show_occupancy_predictions' => true,
        'max_capacity' => 100,
    ]);
    $membership = Membership::factory()->create(['team_id' => $team->id]);

    // Only 2 weeks of data (need at least 4)
    foreach ([1, 2] as $weeksAgo) {
        $date = now()->subWeeks($weeksAgo);
        // Match the same day of week as today
        CheckIn::factory()->create([
            'team_id' => $team->id,
            'gym_id' => $gym->id,
            'membership_id' => $membership->id,
            'created_at' => $date->copy()->startOfDay()->addHours(10),
        ]);
    }

    $this->actingAs($user)
        ->getJson(route('gym.occupancy', ['team' => $team, 'gym' => $gym->slug]))
        ->assertSuccessful()
        ->assertJsonPath('predictions', null);
});

it('returns predictions when enough historical data exists', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'occupancy_tracking_enabled' => true,
        'show_occupancy_predictions' => true,
        'max_capacity' => 100,
    ]);
    $membership = Membership::factory()->create(['team_id' => $team->id]);

    // Create 5 weeks of data for the same day-of-week as today
    foreach (range(1, 5) as $weeksAgo) {
        $date = now()->subWeeks($weeksAgo);

        CheckIn::factory()->create([
            'team_id' => $team->id,
            'gym_id' => $gym->id,
            'membership_id' => $membership->id,
            'created_at' => $date->copy()->startOfDay()->addHours(9),
        ]);
        CheckIn::factory()->create([
            'team_id' => $team->id,
            'gym_id' => $gym->id,
            'membership_id' => $membership->id,
            'created_at' => $date->copy()->startOfDay()->addHours(17),
        ]);
    }

    $response = $this->actingAs($user)
        ->getJson(route('gym.occupancy', ['team' => $team, 'gym' => $gym->slug]))
        ->assertSuccessful();

    $predictions = $response->json('predictions');

    expect($predictions)->not->toBeNull()
        ->and($predictions)->toHaveCount(24)
        ->and($predictions['9'])->toBeGreaterThan(0)
        ->and($predictions['17'])->toBeGreaterThan(0)
        ->and($predictions['3'])->toBe(0); // No check-ins at 3am
});

it('does not pass occupancy gyms when member has no active memberships', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['is_active' => true]);
    Gym::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'occupancy_tracking_enabled' => true,
        'max_capacity' => 80,
    ]);
    Membership::factory()->create([
        'user_id' => $user->id,
        'team_id' => $team->id,
        'status' => 'cancelled',
    ]);

    $this->actingAs($user)
        ->get(route('account.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('occupancyGyms', 0)
        );
});
