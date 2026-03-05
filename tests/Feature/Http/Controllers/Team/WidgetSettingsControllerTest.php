<?php

use App\Models\Gym;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function validWidgetSettings(array $overrides = []): array
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
        'yearly_toggle_promo_text' => 'Get 1 month free',
        'show_access_code' => true,
        'show_success_details' => true,
        'show_cta_card' => true,
        'success_heading' => "You're all set!",
        'success_message' => 'Your membership is now active.',
    ], $overrides);
}

it('shows widget settings page for team owner', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);
    MembershipPlan::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->actingAs($user)
        ->get(route('team.gyms.settings.widget', ['team' => $team, 'gym' => $gym]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('team/gyms/settings/widget')
            ->has('team')
            ->has('gym')
            ->has('settings')
            ->has('plans', 1)
            ->has('embedUrl')
        );
});

it('requires authentication', function () {
    $team = Team::factory()->create();
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->get(route('team.gyms.settings.widget', ['team' => $team, 'gym' => $gym]))
        ->assertRedirect(route('login'));
});

it('requires team access', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->get(route('team.gyms.settings.widget', ['team' => $team, 'gym' => $gym]))
        ->assertForbidden();
});

it('loads default settings when none configured', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->get(route('team.gyms.settings.widget', ['team' => $team, 'gym' => $gym]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('settings.primary_color', '#2563eb')
            ->where('settings.columns', 3)
            ->where('settings.button_text', 'Sign Up')
            ->where('settings.card_border_radius', 16)
            ->where('settings.button_border_radius', 8)
            ->where('settings.yearly_toggle_promo_text', 'Get 1 month free')
            ->where('settings.success_heading', "You're all set!")
        );
});

it('updates widget settings', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $settings = validWidgetSettings([
        'primary_color' => '#ff0000',
        'card_border_radius' => 12,
        'padding' => 24,
        'columns' => 2,
        'show_description' => false,
        'button_text' => 'Join Now',
        'yearly_toggle_promo_text' => 'Get 1 month free',
        'success_heading' => 'Welcome!',
        'show_cta_card' => false,
    ]);

    $this->actingAs($user)
        ->patch(route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]), $settings)
        ->assertRedirect();

    expect($gym->fresh()->widget_settings)->toMatchArray([
        'primary_color' => '#ff0000',
        'columns' => 2,
        'button_text' => 'Join Now',
        'yearly_toggle_promo_text' => 'Get 1 month free',
        'card_border_radius' => 12,
        'success_heading' => 'Welcome!',
        'show_cta_card' => false,
    ]);
});

it('stores a blank yearly_toggle_promo_text as an empty string', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['yearly_toggle_promo_text' => '']),
        )
        ->assertRedirect();

    expect($gym->fresh()->widget_settings['yearly_toggle_promo_text'])->toBe('');
});

it('validates hex color format', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['primary_color' => 'not-a-color']),
        )
        ->assertSessionHasErrors('primary_color');
});

it('validates card_border_radius range', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['card_border_radius' => 50]),
        )
        ->assertSessionHasErrors('card_border_radius');
});

it('validates button_border_radius range', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['button_border_radius' => 50]),
        )
        ->assertSessionHasErrors('button_border_radius');
});

it('validates input_border_radius range', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['input_border_radius' => 50]),
        )
        ->assertSessionHasErrors('input_border_radius');
});

it('validates input color fields as hex', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['input_border_color' => 'invalid']),
        )
        ->assertSessionHasErrors('input_border_color');

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['input_background_color' => 'invalid']),
        )
        ->assertSessionHasErrors('input_background_color');
});

it('validates button_text max length', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['button_text' => str_repeat('a', 51)]),
        )
        ->assertSessionHasErrors('button_text');
});

it('validates yearly_toggle_promo_text max length', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['yearly_toggle_promo_text' => str_repeat('a', 51)]),
        )
        ->assertSessionHasErrors('yearly_toggle_promo_text');
});

it('validates success_heading max length', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['success_heading' => str_repeat('a', 101)]),
        )
        ->assertSessionHasErrors('success_heading');
});

it('validates success_message max length', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['success_message' => str_repeat('a', 256)]),
        )
        ->assertSessionHasErrors('success_message');
});

it('validates boolean fields', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->patch(
            route('team.gyms.settings.widget.update', ['team' => $team, 'gym' => $gym]),
            validWidgetSettings(['show_access_code' => 'not-boolean']),
        )
        ->assertSessionHasErrors('show_access_code');
});

it('resets gym widget settings to null', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->withWidgetSettings()->create(['team_id' => $team->id]);

    expect($gym->widget_settings)->not->toBeNull();

    $this->actingAs($user)
        ->delete(route('team.gyms.settings.widget.destroy', ['team' => $team, 'gym' => $gym]))
        ->assertRedirect();

    expect($gym->fresh()->widget_settings)->toBeNull();
});

it('passes hasOverrides prop correctly', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['owner_id' => $user->id]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $this->actingAs($user)
        ->get(route('team.gyms.settings.widget', ['team' => $team, 'gym' => $gym]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('hasOverrides', false)
        );

    $gym->update(['widget_settings' => ['primary_color' => '#ff0000']]);

    $this->actingAs($user)
        ->get(route('team.gyms.settings.widget', ['team' => $team, 'gym' => $gym]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('hasOverrides', true)
        );
});
