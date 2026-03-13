<?php

namespace Database\Seeders;

use App\Enums\CheckInMethod;
use App\Models\CheckIn;
use App\Models\Membership;
use Illuminate\Database\Seeder;

class CheckInSeeder extends Seeder
{
    /**
     * Typical gym hourly traffic weights (24 hours).
     * Higher values = busier hours.
     *
     * @var array<int, int>
     */
    private const HOURLY_WEIGHTS = [
        0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0,
        5 => 2, 6 => 8, 7 => 12, 8 => 10, 9 => 6,
        10 => 4, 11 => 5, 12 => 7, 13 => 5, 14 => 3,
        15 => 4, 16 => 8, 17 => 14, 18 => 12, 19 => 8,
        20 => 5, 21 => 3, 22 => 1, 23 => 0,
    ];

    public function run(): void
    {
        $memberships = Membership::with('team.gyms')->get();
        $methods = CheckInMethod::cases();

        $memberships->each(function (Membership $membership) use ($methods) {
            $gyms = $membership->team->gyms;

            if ($gyms->isEmpty()) {
                return;
            }

            $count = rand(3, 10);

            for ($i = 0; $i < $count; $i++) {
                CheckIn::create([
                    'membership_id' => $membership->id,
                    'team_id' => $membership->team_id,
                    'gym_id' => $gyms->random()->id,
                    'checked_in_by' => $membership->team->owner_id,
                    'method' => $methods[array_rand($methods)],
                    'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 12)),
                ]);
            }
        });

        $soloMemberships = Membership::query()
            ->whereHas('team', static fn ($query) => $query->where('slug', 'core-fit'))
            ->with('team.gyms')
            ->get();

        $soloMemberships->each(function (Membership $membership) use ($methods) {
            $gyms = $membership->team->gyms;

            if ($gyms->isEmpty()) {
                return;
            }

            $existingCount = CheckIn::query()
                ->where('membership_id', $membership->id)
                ->count();

            for ($i = $existingCount; $i < 12; $i++) {
                CheckIn::query()->create([
                    'membership_id' => $membership->id,
                    'team_id' => $membership->team_id,
                    'gym_id' => $gyms->random()->id,
                    'checked_in_by' => $membership->team->owner_id,
                    'method' => $methods[array_rand($methods)],
                    'created_at' => now()->subDays(rand(0, 21))->subHours(rand(0, 12)),
                ]);
            }
        });

        // Seed realistic hourly check-ins for the past 7 days
        // so the occupancy chart looks meaningful
        $this->seedRealisticCheckIns();
    }

    private function seedRealisticCheckIns(): void
    {
        $memberships = Membership::query()
            ->whereHas('team', static fn ($query) => $query->where('slug', 'fitlife-fitness'))
            ->with('team.gyms')
            ->get();

        if ($memberships->isEmpty()) {
            return;
        }

        $team = $memberships->first()->team;
        $gyms = $team->gyms;
        $methods = CheckInMethod::cases();

        if ($gyms->isEmpty()) {
            return;
        }

        $primaryGym = $gyms->first();

        // Enable occupancy tracking on the primary gym
        $primaryGym->update([
            'occupancy_tracking_enabled' => true,
            'max_capacity' => 80,
        ]);

        // Seed check-ins for the past 7 days with realistic hourly patterns
        for ($dayOffset = 0; $dayOffset <= 6; $dayOffset++) {
            $date = now()->subDays($dayOffset)->startOfDay();
            $isWeekend = $date->isWeekend();

            foreach (self::HOURLY_WEIGHTS as $hour => $weight) {
                // Reduce weekend traffic slightly
                $adjustedWeight = $isWeekend ? (int) ($weight * 0.7) : $weight;

                // Add some randomness
                $count = max(0, $adjustedWeight + rand(-2, 3));

                for ($i = 0; $i < $count; $i++) {
                    $randomMembership = $memberships->random();
                    $checkInTime = $date->copy()
                        ->addHours($hour)
                        ->addMinutes(rand(0, 59));

                    // Don't create future check-ins
                    if ($checkInTime->isFuture()) {
                        continue;
                    }

                    CheckIn::query()->create([
                        'membership_id' => $randomMembership->id,
                        'team_id' => $team->id,
                        'gym_id' => $primaryGym->id,
                        'checked_in_by' => $team->owner_id,
                        'method' => $methods[array_rand($methods)],
                        'created_at' => $checkInTime,
                    ]);
                }
            }
        }
    }
}
