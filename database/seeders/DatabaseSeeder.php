<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SuperAdminSeeder::class,
            TeamSeeder::class,
            GymSeeder::class,
            MembershipPlanSeeder::class,
            MembershipSeeder::class,
            CheckInSeeder::class,
            EmailTemplateSeeder::class,
        ]);
    }
}
