<?php

use App\Models\Gym;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

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
            'default_currency' => 'NOK',
            'default_language' => 'nb',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('teams', [
        'name' => 'My New Gym',
        'owner_id' => $user->id,
        'default_currency' => 'NOK',
        'default_language' => 'nb',
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

it('validates default currency and language on create', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->post(route('team.store'), [
            'name' => 'My New Gym',
            'default_currency' => 'SEK',
            'default_language' => 'no',
        ])
        ->assertSessionHasErrors(['default_currency', 'default_language']);
});

it('shows team dashboard to owner', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $this->actingAs($user)
        ->get(route('team.show', $team))
        ->assertSuccessful();
});

it('shares single gym context for teams with one gym', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->get(route('team.show', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('currentTeam.singleGym.slug', $gym->slug)
        );
});

it('shares null single gym context for teams with multiple gyms', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    Gym::factory()->count(2)->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->get(route('team.show', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('currentTeam.singleGym', null)
        );
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

it('shares all active teams in managed teams for super-admins', function () {
    $admin = User::factory()->create();
    setPermissionsTeamId(0);
    $admin->assignRole('super-admin');

    $alphaTeam = Team::factory()->create([
        'name' => 'Alpha Team',
        'slug' => 'alpha-team',
    ]);
    Team::factory()->create([
        'name' => 'Beta Team',
        'slug' => 'beta-team',
    ]);

    $this->actingAs($admin)
        ->get(route('team.show', $alphaTeam))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('auth.managedTeams', 2)
            ->where('auth.managedTeams.0.slug', 'alpha-team')
            ->where('auth.managedTeams.1.slug', 'beta-team')
        );
});

it('shares team scoped managed teams for team-admins', function () {
    $teamAdmin = User::factory()->create();
    $team = Team::factory()->create();
    Team::factory()->create();

    setPermissionsTeamId($team->id);
    $teamAdmin->assignRole('team-admin');
    $teamAdmin = $teamAdmin->fresh();

    $this->actingAs($teamAdmin)
        ->get(route('team.show', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('auth.managedTeams', 1)
            ->where('auth.managedTeams.0.slug', $team->slug)
        );
});

it('keeps current team context visible on admin routes', function () {
    $admin = User::factory()->create();
    setPermissionsTeamId(0);
    $admin->assignRole('super-admin');

    $activeTeam = Team::factory()->create([
        'name' => 'Focus Team',
        'slug' => 'focus-team',
    ]);
    $admin->update(['last_visited_team_slug' => $activeTeam->slug]);

    $singleGym = Gym::factory()->create(['team_id' => $activeTeam->id]);
    Team::factory()->create([
        'name' => 'Other Team',
        'slug' => 'other-team',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('currentTeam.slug', 'focus-team')
            ->where('currentTeam.singleGym.slug', $singleGym->slug)
        );
});

it('updates team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $this->actingAs($user)
        ->patch(route('team.update', $team), [
            'name' => 'Updated Name',
            'default_currency' => 'EUR',
            'default_language' => 'sv',
        ])
        ->assertRedirect();

    expect($team->fresh()->name)->toBe('Updated Name')
        ->and($team->fresh()->default_currency)->toBe('EUR')
        ->and($team->fresh()->default_language)->toBe('sv');
});
