<?php

namespace Database\Seeders;

use App\Enums\CheckInMethod;
use App\Models\CheckIn;
use App\Models\Membership;
use Illuminate\Database\Seeder;

class CheckInSeeder extends Seeder
{
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
    }
}
