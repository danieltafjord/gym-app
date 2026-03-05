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
            $teamSlug = Str::slug($teamData['name']);
            $team = Team::query()
                ->where('slug', $teamSlug)
                ->first();

            if ($team) {
                $team->update([
                    'name' => $teamData['name'],
                    'description' => $teamData['description'],
                    'default_currency' => 'USD',
                    'default_language' => 'en',
                    'stripe_onboarding_complete' => true,
                ]);

                continue;
            }

            $owner = User::factory()->create(['email_verified_at' => now()]);

            $team = Team::query()->create([
                'owner_id' => $owner->id,
                'name' => $teamData['name'],
                'slug' => $teamSlug,
                'description' => $teamData['description'],
                'default_currency' => 'USD',
                'default_language' => 'en',
                'stripe_account_id' => 'acct_test_'.Str::random(8),
                'stripe_onboarding_complete' => true,
            ]);

            setPermissionsTeamId($team->id);
            $owner->assignRole('team-owner');
        }

        $superAdmin = User::query()
            ->where('email', 'admin@gymapp.com')
            ->first();

        if (! $superAdmin) {
            return;
        }

        $singleGymTeam = Team::updateOrCreate(
            ['slug' => 'core-fit'],
            [
                'owner_id' => $superAdmin->id,
                'name' => 'Core Fit',
                'description' => 'Single-location team for streamlined backoffice flow.',
                'default_currency' => 'NOK',
                'default_language' => 'nb',
                'stripe_account_id' => 'acct_test_corefit',
                'stripe_onboarding_complete' => true,
            ],
        );

        setPermissionsTeamId($singleGymTeam->id);
        $superAdmin->assignRole('team-owner');
    }
}
