<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'FitLife Fitness', 'description' => 'Premium fitness centers focused on holistic wellness.'],
            ['name' => 'Iron Temple Gym', 'description' => 'Hardcore training facilities for serious athletes.'],
            ['name' => 'Zen Wellness Club', 'description' => 'Mind and body wellness with yoga and meditation.'],
        ];

        foreach ($teams as $teamData) {
            $owner = User::factory()->create([
                'email_verified_at' => now(),
            ]);

            $team = Team::create([
                'owner_id' => $owner->id,
                'name' => $teamData['name'],
                'slug' => Str::slug($teamData['name']),
                'description' => $teamData['description'],
                'stripe_account_id' => 'acct_test_'.Str::random(8),
                'stripe_onboarding_complete' => true,
            ]);

            setPermissionsTeamId($team->id);
            $owner->assignRole('team-owner');
        }
    }
}
