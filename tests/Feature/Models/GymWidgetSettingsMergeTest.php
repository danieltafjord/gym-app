<?php

use App\Models\Gym;
use App\Models\Team;

it('uses hardcoded defaults when neither team nor gym have settings', function () {
    $team = Team::factory()->create();
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $settings = $gym->widget_settings_with_defaults;

    expect($settings)->toBe(Gym::DEFAULT_WIDGET_SETTINGS);
});

it('merges team settings over defaults', function () {
    $team = Team::factory()->create([
        'widget_settings' => [
            'primary_color' => '#ff0000',
            'button_text' => 'Team Button',
            'yearly_toggle_promo_text' => 'Team Promo',
        ],
    ]);
    $gym = Gym::factory()->create(['team_id' => $team->id]);

    $settings = $gym->widget_settings_with_defaults;

    expect($settings['primary_color'])->toBe('#ff0000')
        ->and($settings['button_text'])->toBe('Team Button')
        ->and($settings['yearly_toggle_promo_text'])->toBe('Team Promo')
        ->and($settings['columns'])->toBe(Gym::DEFAULT_WIDGET_SETTINGS['columns']);
});

it('merges gym settings over team settings', function () {
    $team = Team::factory()->create([
        'widget_settings' => [
            'primary_color' => '#ff0000',
            'button_text' => 'Team Button',
            'yearly_toggle_promo_text' => 'Team Promo',
        ],
    ]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'widget_settings' => [
            'primary_color' => '#00ff00',
            'columns' => 2,
            'yearly_toggle_promo_text' => 'Gym Promo',
        ],
    ]);

    $settings = $gym->widget_settings_with_defaults;

    expect($settings['primary_color'])->toBe('#00ff00')
        ->and($settings['button_text'])->toBe('Team Button')
        ->and($settings['yearly_toggle_promo_text'])->toBe('Gym Promo')
        ->and($settings['columns'])->toBe(2);
});

it('falls back to defaults after gym reset', function () {
    $team = Team::factory()->create([
        'widget_settings' => [
            'primary_color' => '#ff0000',
        ],
    ]);
    $gym = Gym::factory()->create([
        'team_id' => $team->id,
        'widget_settings' => ['primary_color' => '#00ff00'],
    ]);

    $gym->update(['widget_settings' => null]);
    $gym->refresh();

    $settings = $gym->widget_settings_with_defaults;

    expect($settings['primary_color'])->toBe('#ff0000')
        ->and($settings['yearly_toggle_promo_text'])->toBe(Gym::DEFAULT_WIDGET_SETTINGS['yearly_toggle_promo_text'])
        ->and($settings['columns'])->toBe(Gym::DEFAULT_WIDGET_SETTINGS['columns']);
});

it('provides correct team widget settings with defaults', function () {
    $team = Team::factory()->create([
        'widget_settings' => ['primary_color' => '#ff0000'],
    ]);

    $settings = $team->widget_settings_with_defaults;

    expect($settings['primary_color'])->toBe('#ff0000')
        ->and($settings['columns'])->toBe(Gym::DEFAULT_WIDGET_SETTINGS['columns'])
        ->and($settings['button_text'])->toBe(Gym::DEFAULT_WIDGET_SETTINGS['button_text'])
        ->and($settings['yearly_toggle_promo_text'])->toBe(Gym::DEFAULT_WIDGET_SETTINGS['yearly_toggle_promo_text']);
});
