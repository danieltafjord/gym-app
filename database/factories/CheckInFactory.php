<?php

namespace Database\Factories;

use App\Enums\CheckInMethod;
use App\Models\Gym;
use App\Models\Membership;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CheckIn>
 */
class CheckInFactory extends Factory
{
    public function definition(): array
    {
        return [
            'membership_id' => Membership::factory(),
            'team_id' => Team::factory(),
            'gym_id' => Gym::factory(),
            'checked_in_by' => User::factory(),
            'method' => CheckInMethod::QrScan,
        ];
    }

    public function barcodeScan(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => CheckInMethod::BarcodeScanner,
        ]);
    }

    public function manualEntry(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => CheckInMethod::ManualEntry,
        ]);
    }

    public function withoutGym(): static
    {
        return $this->state(fn (array $attributes) => [
            'gym_id' => null,
        ]);
    }

    public function withoutStaff(): static
    {
        return $this->state(fn (array $attributes) => [
            'checked_in_by' => null,
        ]);
    }
}
