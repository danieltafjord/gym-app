<?php

namespace App\Actions\Team;

use App\Enums\BillingPeriod;
use App\Enums\MembershipStatus;
use App\Models\Team;

class GetTeamDashboardStats
{
    /**
     * @return array{active_members: int, mrr: float, check_ins_today: int}
     */
    public function dashboardStats(Team $team): array
    {
        return [
            'active_members' => $this->activeMembers($team),
            'mrr' => $this->calculateMrr($team),
            'check_ins_today' => $this->checkInsToday($team),
        ];
    }

    /**
     * @return array{active_members: int, mrr: float, check_ins_today: int, new_members_this_month: int, churn_rate: float}
     */
    public function handle(Team $team): array
    {
        return [
            'active_members' => $this->activeMembers($team),
            'mrr' => $this->calculateMrr($team),
            'check_ins_today' => $this->checkInsToday($team),
            'new_members_this_month' => $this->newMembersThisMonth($team),
            'churn_rate' => $this->churnRate($team),
        ];
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    public function memberGrowth(Team $team): array
    {
        $series = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = $team->memberships()
                ->where('created_at', '<=', $date->endOfMonth())
                ->where(function ($q) use ($date) {
                    $q->where('status', MembershipStatus::Active)
                        ->orWhere('cancelled_at', '>', $date->endOfMonth());
                })
                ->count();

            $series[] = [
                'label' => $date->format('M Y'),
                'value' => $count,
            ];
        }

        return $series;
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    public function checkInsDaily(Team $team): array
    {
        $series = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = $team->checkIns()
                ->whereDate('created_at', $date->toDateString())
                ->count();

            $series[] = [
                'label' => $date->format('M d'),
                'value' => $count,
            ];
        }

        return $series;
    }

    private function activeMembers(Team $team): int
    {
        return $team->memberships()->where('status', MembershipStatus::Active)->count();
    }

    private function calculateMrr(Team $team): float
    {
        $totalMonthly = 0;

        $activeMemberships = $team->memberships()
            ->where('status', MembershipStatus::Active)
            ->with('plan')
            ->get();

        foreach ($activeMemberships as $membership) {
            if (! $membership->plan) {
                continue;
            }

            $priceCents = $membership->plan->price_cents;

            $totalMonthly += match ($membership->plan->billing_period) {
                BillingPeriod::Weekly => $priceCents * 4.33,
                BillingPeriod::Monthly => $priceCents,
                BillingPeriod::Quarterly => $priceCents / 3,
                BillingPeriod::Yearly => $priceCents / 12,
            };
        }

        return round($totalMonthly / 100, 2);
    }

    private function checkInsToday(Team $team): int
    {
        return $team->checkIns()->whereDate('created_at', today())->count();
    }

    private function newMembersThisMonth(Team $team): int
    {
        return $team->memberships()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
    }

    private function churnRate(Team $team): float
    {
        $thirtyDaysAgo = now()->subDays(30);

        $activeAtStart = $team->memberships()
            ->where('created_at', '<', $thirtyDaysAgo)
            ->where(function ($q) use ($thirtyDaysAgo) {
                $q->where('status', MembershipStatus::Active)
                    ->orWhere('cancelled_at', '>', $thirtyDaysAgo);
            })
            ->count();

        if ($activeAtStart === 0) {
            return 0;
        }

        $cancelledInPeriod = $team->memberships()
            ->where('status', MembershipStatus::Cancelled)
            ->where('cancelled_at', '>=', $thirtyDaysAgo)
            ->count();

        return round(($cancelledInPeriod / $activeAtStart) * 100, 1);
    }
}
