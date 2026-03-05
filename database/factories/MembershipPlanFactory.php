<?php

namespace Database\Factories;

use App\Enums\BillingPeriod;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MembershipPlan>
 */
class MembershipPlanFactory extends Factory
{
    public function definition(): array
    {
        $billingPeriod = fake()->randomElement(BillingPeriod::cases());
        $priceCents = fake()->randomElement([2999, 4999, 7999, 9999, 14999]);

        return [
            'team_id' => Team::factory(),
            'name' => fake()->randomElement(['Basic', 'Premium', 'VIP', 'Student', 'Family']),
            'description' => fake()->sentence(),
            'price_cents' => $priceCents,
            'yearly_price_cents' => $billingPeriod === BillingPeriod::Monthly && fake()->boolean(40)
                ? $priceCents * 10
                : null,
            'billing_period' => $billingPeriod,
            'features' => ['Access to gym floor', 'Locker room access'],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
