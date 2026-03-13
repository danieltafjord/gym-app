<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gym>
 */
class GymFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(2, true).' Gym';

        return [
            'team_id' => Team::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withOccupancyTracking(int $maxCapacity = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'occupancy_tracking_enabled' => true,
            'max_capacity' => $maxCapacity,
        ]);
    }

    public function withWidgetSettings(): static
    {
        return $this->state(fn (array $attributes) => [
            'widget_settings' => [
                'primary_color' => fake()->hexColor(),
                'background_color' => '#f9fafb',
                'text_color' => '#111827',
                'secondary_text_color' => '#6b7280',
                'button_text_color' => '#ffffff',
                'card_border_color' => '#e5e7eb',
                'input_border_color' => '#e5e7eb',
                'input_background_color' => '#ffffff',
                'card_border_radius' => fake()->numberBetween(0, 32),
                'button_border_radius' => fake()->numberBetween(0, 32),
                'input_border_radius' => fake()->numberBetween(0, 32),
                'padding' => fake()->numberBetween(8, 48),
                'columns' => fake()->numberBetween(1, 4),
                'button_text' => 'Join Now',
                'yearly_toggle_promo_text' => 'Get 1 month free',
                'show_features' => true,
                'show_description' => true,
                'show_access_code' => true,
                'show_success_details' => true,
                'show_cta_card' => true,
                'success_heading' => "You're all set!",
                'success_message' => 'Your membership is now active.',
            ],
        ]);
    }
}
