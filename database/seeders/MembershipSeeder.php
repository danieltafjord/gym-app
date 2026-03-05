<?php

namespace Database\Seeders;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        $plans = MembershipPlan::all();

        // Known test member for easy login
        $testMember = User::firstOrCreate(
            ['email' => 'member@gymapp.com'],
            [
                'name' => 'Test Member',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        $testPlan = $plans->first();
        setPermissionsTeamId($testPlan->team_id);
        $testMember->assignRole('member');

        Membership::create([
            'user_id' => $testMember->id,
            'team_id' => $testPlan->team_id,
            'membership_plan_id' => $testPlan->id,
            'email' => $testMember->email,
            'customer_name' => $testMember->name,
            'access_code' => strtoupper(Str::random(24)),
            'status' => MembershipStatus::Active,
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->addDays(335),
        ]);

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
                'access_code' => strtoupper(Str::random(24)),
                'status' => MembershipStatus::Active,
                'starts_at' => now()->subDays(rand(1, 90)),
                'ends_at' => now()->addDays(rand(30, 365)),
            ]);
        });

        $coreFitTeam = Team::query()
            ->where('slug', 'core-fit')
            ->first();

        if (! $coreFitTeam) {
            return;
        }

        $coreFitPlans = MembershipPlan::query()
            ->where('team_id', $coreFitTeam->id)
            ->orderBy('sort_order')
            ->get();

        if ($coreFitPlans->isEmpty()) {
            return;
        }

        $coreFitCustomers = [
            ['name' => 'Camila Sorensen', 'email' => 'camila.sorensen+corefit@gymapp.com', 'phone' => '555-3001', 'plan' => 'Core', 'started_days_ago' => 120, 'remaining_days' => 245],
            ['name' => 'Marcus Hale', 'email' => 'marcus.hale+corefit@gymapp.com', 'phone' => '555-3002', 'plan' => 'Core Velvære', 'started_days_ago' => 90, 'remaining_days' => 275],
            ['name' => 'Jenny Park', 'email' => 'jenny.park+corefit@gymapp.com', 'phone' => '555-3003', 'plan' => 'Core Velvære', 'started_days_ago' => 70, 'remaining_days' => 295],
            ['name' => 'Omar Reed', 'email' => 'omar.reed+corefit@gymapp.com', 'phone' => '555-3004', 'plan' => 'Core', 'started_days_ago' => 45, 'remaining_days' => 320],
            ['name' => 'Leah Stone', 'email' => 'leah.stone+corefit@gymapp.com', 'phone' => '555-3005', 'plan' => 'Core', 'started_days_ago' => 30, 'remaining_days' => 335],
            ['name' => 'Victor Lane', 'email' => 'victor.lane+corefit@gymapp.com', 'phone' => '555-3006', 'plan' => 'Core Velvære', 'started_days_ago' => 15, 'remaining_days' => 350],
        ];

        foreach ($coreFitCustomers as $customer) {
            $user = User::query()->firstOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );

            setPermissionsTeamId($coreFitTeam->id);
            $user->assignRole('member');

            $plan = $coreFitPlans->firstWhere('name', $customer['plan']) ?? $coreFitPlans->first();

            if (! $plan) {
                continue;
            }

            $membershipData = [
                'user_id' => $user->id,
                'membership_plan_id' => $plan->id,
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'status' => MembershipStatus::Active,
                'starts_at' => now()->subDays($customer['started_days_ago']),
                'ends_at' => now()->addDays($customer['remaining_days']),
            ];

            $existingMembership = Membership::query()
                ->where('team_id', $coreFitTeam->id)
                ->where('email', $customer['email'])
                ->first();

            if ($existingMembership) {
                $existingMembership->update($membershipData);

                continue;
            }

            Membership::query()->create([
                ...$membershipData,
                'team_id' => $coreFitTeam->id,
                'email' => $customer['email'],
                'access_code' => Membership::generateAccessCode(),
            ]);
        }
    }
}
