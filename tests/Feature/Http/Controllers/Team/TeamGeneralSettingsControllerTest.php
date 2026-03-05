<?php

use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows general settings page for team owner', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('team.settings.general', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('team/settings/general')
            ->has('team')
            ->where('team.default_currency', $team->default_currency)
            ->where('team.default_language', $team->default_language)
        );
});

it('updates default currency and language from team settings', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create([
        'owner_id' => $user->id,
        'default_currency' => 'USD',
        'default_language' => 'en',
    ]);

    $this->actingAs($user)
        ->patch(route('team.settings.general.update', $team), [
            'default_currency' => 'NOK',
            'default_language' => 'nb',
        ])
        ->assertRedirect();

    expect($team->fresh()->default_currency)->toBe('NOK')
        ->and($team->fresh()->default_language)->toBe('nb');
});

it('validates allowed currency and language values in team settings', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->patch(route('team.settings.general.update', $team), [
            'default_currency' => 'SEK',
            'default_language' => 'no',
        ])
        ->assertSessionHasErrors(['default_currency', 'default_language']);
});
