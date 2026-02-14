<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GymSeeder extends Seeder
{
    public function run(): void
    {
        $gymNames = [
            ['Downtown Branch', 'Westside Location', 'Airport Express'],
            ['Main Campus', 'South Side'],
            ['Central Studio', 'Harbor View', 'Mountain Retreat'],
        ];

        Team::all()->each(function (Team $team, int $index) use ($gymNames) {
            $names = $gymNames[$index] ?? ['Main Branch'];

            foreach ($names as $name) {
                Gym::create([
                    'team_id' => $team->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'address' => fake()->address(),
                    'phone' => fake()->phoneNumber(),
                    'email' => fake()->safeEmail(),
                ]);
            }
        });
    }
}
