<?php

use App\Models\Gym;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows check-in settings page for team owner', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('team.settings.check-in', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('team/settings/check-in')
            ->has('team')
            ->has('settings')
            ->has('gyms')
        );
});

it('loads default settings when team has no check-in settings', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('team.settings.check-in', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('settings.enabled', true)
            ->where('settings.prevent_duplicate_minutes', 5)
            ->where('settings.kiosk_mode', 'camera')
        );
});

it('requires authentication', function () {
    $team = Team::factory()->create();

    $this->get(route('team.settings.check-in', $team))
        ->assertRedirect(route('login'));
});

it('requires team access', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $this->actingAs($user)
        ->get(route('team.settings.check-in', $team))
        ->assertForbidden();
});

it('updates check-in settings', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->patch(route('team.settings.check-in.update', $team), [
            'enabled' => false,
            'allowed_methods' => ['qr_scan'],
            'require_gym_selection' => false,
            'prevent_duplicate_minutes' => 10,
            'kiosk_mode' => 'barcode_scanner',
        ])
        ->assertRedirect();

    expect($team->fresh()->check_in_settings)->toMatchArray([
        'enabled' => false,
        'allowed_methods' => ['qr_scan'],
        'require_gym_selection' => false,
        'prevent_duplicate_minutes' => 10,
        'kiosk_mode' => 'barcode_scanner',
    ]);
});

it('validates allowed_methods is required and not empty', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->patch(route('team.settings.check-in.update', $team), [
            'enabled' => true,
            'allowed_methods' => [],
            'require_gym_selection' => true,
            'prevent_duplicate_minutes' => 5,
            'kiosk_mode' => 'camera',
        ])
        ->assertSessionHasErrors('allowed_methods');
});

it('validates allowed_methods contains valid values', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->patch(route('team.settings.check-in.update', $team), [
            'enabled' => true,
            'allowed_methods' => ['invalid_method'],
            'require_gym_selection' => true,
            'prevent_duplicate_minutes' => 5,
            'kiosk_mode' => 'camera',
        ])
        ->assertSessionHasErrors('allowed_methods.0');
});

it('validates prevent_duplicate_minutes range', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->patch(route('team.settings.check-in.update', $team), [
            'enabled' => true,
            'allowed_methods' => ['qr_scan'],
            'require_gym_selection' => true,
            'prevent_duplicate_minutes' => 9999,
            'kiosk_mode' => 'camera',
        ])
        ->assertSessionHasErrors('prevent_duplicate_minutes');
});

it('validates kiosk_mode is required and valid', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->patch(route('team.settings.check-in.update', $team), [
            'enabled' => true,
            'allowed_methods' => ['qr_scan'],
            'require_gym_selection' => true,
            'prevent_duplicate_minutes' => 5,
            'kiosk_mode' => 'invalid',
        ])
        ->assertSessionHasErrors('kiosk_mode');
});

it('shows kiosk URLs for active gyms', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->actingAs($user)
        ->get(route('team.settings.check-in', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('gyms', 1)
        );
});
