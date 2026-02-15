<?php

use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function validTeamWidgetSettings(array $overrides = []): array
{
    return array_merge([
        'primary_color' => '#2563eb',
        'background_color' => '#ffffff',
        'text_color' => '#111827',
        'secondary_text_color' => '#6b7280',
        'card_border_color' => '#e5e7eb',
        'button_text_color' => '#ffffff',
        'input_border_color' => '#e5e7eb',
        'input_background_color' => '#ffffff',
        'font_family' => 'Inter, sans-serif',
        'card_border_radius' => 16,
        'button_border_radius' => 8,
        'input_border_radius' => 8,
        'padding' => 16,
        'columns' => 3,
        'show_features' => true,
        'show_description' => true,
        'button_text' => 'Sign Up',
        'show_access_code' => true,
        'show_success_details' => true,
        'show_cta_card' => true,
        'success_heading' => "You're all set!",
        'success_message' => 'Your membership is now active.',
    ], $overrides);
}

it('shows team widget defaults page for team owner', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    MembershipPlan::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->actingAs($user)
        ->get(route('team.settings.widget-defaults', ['team' => $team]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('team/settings/widget-defaults')
            ->has('team')
            ->has('settings')
            ->has('plans', 1)
        );
});

it('loads hardcoded defaults when team has no widget settings', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('team.settings.widget-defaults', ['team' => $team]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('settings.primary_color', '#2563eb')
            ->where('settings.columns', 3)
            ->where('settings.button_text', 'Sign Up')
        );
});

it('loads team widget settings when configured', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create([
        'owner_id' => $user->id,
        'widget_settings' => ['primary_color' => '#ff0000', 'button_text' => 'Join'],
    ]);

    $this->actingAs($user)
        ->get(route('team.settings.widget-defaults', ['team' => $team]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('settings.primary_color', '#ff0000')
            ->where('settings.button_text', 'Join')
            ->where('settings.columns', 3)
        );
});

it('requires authentication', function () {
    $team = Team::factory()->create();

    $this->get(route('team.settings.widget-defaults', ['team' => $team]))
        ->assertRedirect(route('login'));
});

it('requires team access', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    $this->actingAs($user)
        ->get(route('team.settings.widget-defaults', ['team' => $team]))
        ->assertForbidden();
});

it('updates team widget defaults', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $settings = validTeamWidgetSettings([
        'primary_color' => '#ff0000',
        'card_border_radius' => 12,
        'button_text' => 'Join Now',
    ]);

    $this->actingAs($user)
        ->patch(route('team.settings.widget-defaults.update', ['team' => $team]), $settings)
        ->assertRedirect();

    expect($team->fresh()->widget_settings)->toMatchArray([
        'primary_color' => '#ff0000',
        'card_border_radius' => 12,
        'button_text' => 'Join Now',
    ]);
});

it('validates hex color format on team widget defaults', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->patch(
            route('team.settings.widget-defaults.update', ['team' => $team]),
            validTeamWidgetSettings(['primary_color' => 'not-a-color']),
        )
        ->assertSessionHasErrors('primary_color');
});

it('validates border radius range on team widget defaults', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->patch(
            route('team.settings.widget-defaults.update', ['team' => $team]),
            validTeamWidgetSettings(['card_border_radius' => 50]),
        )
        ->assertSessionHasErrors('card_border_radius');
});

it('validates button_text max length on team widget defaults', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->patch(
            route('team.settings.widget-defaults.update', ['team' => $team]),
            validTeamWidgetSettings(['button_text' => str_repeat('a', 51)]),
        )
        ->assertSessionHasErrors('button_text');
});
