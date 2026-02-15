<?php

use App\Models\Gym;
use App\Models\MembershipPlan;
use App\Models\Team;

it('returns widget data for an active gym', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);
    MembershipPlan::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->getJson(route('widget.data', ['team' => $team->slug, 'gym' => $gym->slug]))
        ->assertOk()
        ->assertJsonStructure([
            'gym' => ['name', 'slug'],
            'team' => ['name', 'slug'],
            'plans',
            'settings',
            'stripe_key',
            'stripe_dev_mode',
            'checkout_intent_url',
            'checkout_confirm_url',
            'stripe_ready',
        ]);
});

it('returns 404 for inactive team', function () {
    $team = Team::factory()->create(['is_active' => false]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->getJson(route('widget.data', ['team' => $team->slug, 'gym' => $gym->slug]))
        ->assertNotFound();
});

it('returns 404 for inactive gym', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => false]);

    $this->getJson(route('widget.data', ['team' => $team->slug, 'gym' => $gym->slug]))
        ->assertNotFound();
});

it('returns 404 for non-existent gym', function () {
    $team = Team::factory()->create(['is_active' => true]);

    $this->getJson(route('widget.data', ['team' => $team->slug, 'gym' => 'nonexistent']))
        ->assertNotFound();
});

it('only returns active plans ordered by sort_order', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'sort_order' => 2,
        'name' => 'Second',
    ]);
    MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'is_active' => false,
        'name' => 'Inactive',
    ]);
    MembershipPlan::factory()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'sort_order' => 1,
        'name' => 'First',
    ]);

    $response = $this->getJson(route('widget.data', ['team' => $team->slug, 'gym' => $gym->slug]))
        ->assertOk();

    $plans = $response->json('plans');
    expect($plans)->toHaveCount(2);
    expect($plans[0]['name'])->toBe('First');
    expect($plans[1]['name'])->toBe('Second');
});

it('includes widget settings with defaults', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $response = $this->getJson(route('widget.data', ['team' => $team->slug, 'gym' => $gym->slug]))
        ->assertOk();

    $settings = $response->json('settings');
    expect($settings['primary_color'])->toBe('#2563eb');
    expect($settings['columns'])->toBe(3);
    expect($settings['button_text'])->toBe('Sign Up');
});

it('includes custom widget settings when configured', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->withWidgetSettings()->create([
        'team_id' => $team->id,
        'is_active' => true,
        'widget_settings' => ['primary_color' => '#ff0000', 'columns' => 2],
    ]);

    $response = $this->getJson(route('widget.data', ['team' => $team->slug, 'gym' => $gym->slug]))
        ->assertOk();

    $settings = $response->json('settings');
    expect($settings['primary_color'])->toBe('#ff0000');
    expect($settings['columns'])->toBe(2);
    // Defaults should still be present for unset values
    expect($settings['button_text'])->toBe('Sign Up');
});

it('returns CORS headers', function () {
    $team = Team::factory()->create(['is_active' => true]);
    $gym = Gym::factory()->create(['team_id' => $team->id, 'is_active' => true]);

    $this->getJson(route('widget.data', ['team' => $team->slug, 'gym' => $gym->slug]))
        ->assertOk()
        ->assertHeader('Access-Control-Allow-Origin', '*');
});

it('serves embed.js with correct content type', function () {
    $this->get(route('widget.script'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/javascript')
        ->assertHeader('Access-Control-Allow-Origin', '*');
});
