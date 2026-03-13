<?php

namespace Database\Factories;

use App\Enums\AccessCodeStrategy;
use App\Enums\AccessDurationUnit;
use App\Enums\ActivationMode;
use App\Enums\BillingPeriod;
use App\Enums\PlanType;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MembershipPlan>
 */
class MembershipPlanFactory extends Factory
{
    public function definition(): array
    {
        $planType = PlanType::Recurring;
        $billingPeriod = fake()->randomElement(BillingPeriod::cases());
        $priceCents = fake()->randomElement([2999, 4999, 7999, 9999, 14999]);

        return [
            'team_id' => Team::factory(),
            'name' => fake()->randomElement(['Basic', 'Premium', 'VIP', 'Student', 'Family']),
            'description' => fake()->sentence(),
            'price_cents' => $priceCents,
            'yearly_price_cents' => $planType === PlanType::Recurring && $billingPeriod === BillingPeriod::Monthly && fake()->boolean(40)
                ? $priceCents * 10
                : null,
            'billing_period' => $billingPeriod,
            'plan_type' => $planType,
            'access_duration_value' => $planType === PlanType::OneTime ? fake()->numberBetween(1, 24) : null,
            'access_duration_unit' => $planType === PlanType::OneTime ? fake()->randomElement([AccessDurationUnit::Hour, AccessDurationUnit::Day]) : null,
            'activation_mode' => $planType === PlanType::OneTime ? fake()->randomElement(ActivationMode::cases()) : ActivationMode::Purchase,
            'requires_account' => false,
            'access_code_strategy' => $planType === PlanType::OneTime ? AccessCodeStrategy::Static : AccessCodeStrategy::RotateOnCheckIn,
            'max_entries' => null,
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
