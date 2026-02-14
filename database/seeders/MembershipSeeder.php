<?php

namespace Database\Seeders;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        $plans = MembershipPlan::all();
        $members = User::factory(20)->create([
            'email_verified_at' => now(),
        ]);

        $members->each(function (User $user) use ($plans) {
            $plan = $plans->random();

            setPermissionsTeamId($plan->team_id);
            $user->assignRole('member');

            Membership::create([
                'user_id' => $user->id,
                'team_id' => $plan->team_id,
                'membership_plan_id' => $plan->id,
                'email' => $user->email,
                'customer_name' => $user->name,
                'access_code' => strtoupper(Str::random(8)),
                'status' => MembershipStatus::Active,
                'starts_at' => now()->subDays(rand(1, 90)),
                'ends_at' => now()->addDays(rand(30, 365)),
            ]);
        });
    }
}
