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
                'yearly_price_cents' => 29990,
                'billing_period' => BillingPeriod::Monthly,
                'features' => ['Gym floor access', 'Locker room', 'Free WiFi'],
                'sort_order' => 0,
            ],
            [
                'name' => 'Premium',
                'description' => 'Full access including group classes and pool.',
                'price_cents' => 5999,
                'yearly_price_cents' => 59990,
                'billing_period' => BillingPeriod::Monthly,
                'features' => ['All Basic features', 'Group classes', 'Pool access', 'Sauna'],
                'sort_order' => 1,
            ],
            [
                'name' => 'VIP',
                'description' => 'Everything included plus personal training sessions.',
                'price_cents' => 9999,
                'yearly_price_cents' => 99990,
                'billing_period' => BillingPeriod::Monthly,
                'features' => ['All Premium features', 'Personal trainer', 'Nutrition plan', 'Priority booking'],
                'sort_order' => 2,
            ],
        ];

        Team::all()->each(function (Team $team) use ($plans) {
            if ($team->slug === 'core-fit') {
                return;
            }

            foreach ($plans as $plan) {
                MembershipPlan::query()->updateOrCreate(
                    [
                        'team_id' => $team->id,
                        'name' => $plan['name'],
                    ],
                    $plan,
                );
            }
        });

        $coreFitTeam = Team::query()
            ->where('slug', 'core-fit')
            ->first();

        if (! $coreFitTeam) {
            return;
        }

        $coreFitPlans = [
            [
                'name' => 'Core',
                'description' => 'Kjernetrening og styrke for en aktiv hverdag.',
                'price_cents' => 39900,
                'yearly_price_cents' => 399000,
                'billing_period' => BillingPeriod::Monthly,
                'features' => ['Styrkesone', 'Mobilitetssone', 'Månedlig kroppsanalyse'],
                'sort_order' => 0,
            ],
            [
                'name' => 'Core Velvære',
                'description' => 'Styrke og velvære med ekstra restitusjon og ro.',
                'price_cents' => 49900,
                'yearly_price_cents' => 499000,
                'billing_period' => BillingPeriod::Monthly,
                'features' => ['Alt i Core', 'Restitusjonssone', 'Prioritert booking'],
                'sort_order' => 1,
            ],
        ];

        foreach ($coreFitPlans as $coreFitPlan) {
            MembershipPlan::query()->updateOrCreate(
                [
                    'team_id' => $coreFitTeam->id,
                    'name' => $coreFitPlan['name'],
                ],
                $coreFitPlan,
            );
        }

        MembershipPlan::query()
            ->where('team_id', $coreFitTeam->id)
            ->whereNotIn('name', array_column($coreFitPlans, 'name'))
            ->delete();
    }
}
