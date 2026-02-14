<?php

namespace Database\Factories;

use App\Enums\MembershipStatus;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membership>
 */
class MembershipFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'team_id' => Team::factory(),
            'membership_plan_id' => MembershipPlan::factory(),
            'email' => fake()->safeEmail(),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'access_code' => Str::random(8),
            'status' => MembershipStatus::Active,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'cancelled_at' => null,
        ];
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MembershipStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MembershipStatus::Paused,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MembershipStatus::Expired,
            'ends_at' => now()->subDay(),
        ]);
    }
}
