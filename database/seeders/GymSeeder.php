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
        $gymNamesByTeamSlug = [
            'fitlife-fitness' => ['Downtown Branch', 'Westside Location', 'Airport Express'],
            'iron-temple-gym' => ['Main Campus', 'South Side'],
            'zen-wellness-club' => ['Central Studio', 'Harbor View', 'Mountain Retreat'],
            'core-fit' => ['Main Studio'],
        ];

        Team::all()->each(function (Team $team) use ($gymNamesByTeamSlug) {
            $names = $gymNamesByTeamSlug[$team->slug] ?? ['Main Branch'];

            foreach ($names as $name) {
                Gym::query()->firstOrCreate(
                    [
                        'team_id' => $team->id,
                        'slug' => Str::slug($name),
                    ],
                    [
                        'name' => $name,
                        'address' => fake()->address(),
                        'phone' => fake()->phoneNumber(),
                        'email' => fake()->safeEmail(),
                    ],
                );
            }
        });
    }
}
