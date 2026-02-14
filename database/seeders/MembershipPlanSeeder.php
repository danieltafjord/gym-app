<?php

namespace Database\Seeders;

use App\Enums\BillingPeriod;
use App\Models\MembershipPlan;
use App\Models\Team;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'description' => 'Access to gym floor and basic equipment.',
                'price_cents' => 2999,
                'billing_period' => BillingPeriod::Monthly,
                'features' => ['Gym floor access', 'Locker room', 'Free WiFi'],
                'sort_order' => 0,
            ],
            [
                'name' => 'Premium',
                'description' => 'Full access including group classes and pool.',
                'price_cents' => 5999,
                'billing_period' => BillingPeriod::Monthly,
                'features' => ['All Basic features', 'Group classes', 'Pool access', 'Sauna'],
                'sort_order' => 1,
            ],
            [
                'name' => 'VIP',
                'description' => 'Everything included plus personal training sessions.',
                'price_cents' => 9999,
                'billing_period' => BillingPeriod::Monthly,
                'features' => ['All Premium features', 'Personal trainer', 'Nutrition plan', 'Priority booking'],
                'sort_order' => 2,
            ],
        ];

        Team::all()->each(function (Team $team) use ($plans) {
            foreach ($plans as $plan) {
                MembershipPlan::create([
                    'team_id' => $team->id,
                    ...$plan,
                ]);
            }
        });
    }
}
