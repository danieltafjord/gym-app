<?php

namespace App\Actions\CheckIn;

use App\Enums\CheckInMethod;
use App\Enums\MembershipStatus;
use App\Models\CheckIn;
use App\Models\Membership;
use App\Models\Team;

class ProcessCheckIn
{
    /**
     * @param  array{access_code: string, gym_id: int|null, method: string}  $data
     * @return array{success: bool, check_in: CheckIn|null, message: string, membership: Membership|null}
     */
    public function handle(Team $team, array $data, ?int $staffUserId = null): array
    {
        $membership = Membership::query()
            ->where('team_id', $team->id)
            ->where('access_code', $data['access_code'])
            ->with(['user', 'plan'])
            ->first();

        if (! $membership) {
            return [
                'success' => false,
                'check_in' => null,
                'message' => 'No membership found with this access code.',
                'membership' => null,
            ];
        }

        if ($membership->status !== MembershipStatus::Active) {
            return [
                'success' => false,
                'check_in' => null,
                'message' => "This membership is {$membership->status->value}.",
                'membership' => $membership,
            ];
        }

        if ($membership->plan?->activatesOnFirstCheckIn() && $membership->activated_at === null) {
            $membership->activate(now());
            $membership->refresh()->loadMissing(['user', 'plan']);
        }

        $membership->syncExpiredStatus();
        $membership->refresh()->loadMissing(['user', 'plan']);

        if (! $membership->canAccessGym()) {
            return [
                'success' => false,
                'check_in' => null,
                'message' => $this->resolveInvalidMembershipMessage($membership),
                'membership' => $membership,
            ];
        }

        $settings = $team->check_in_settings_with_defaults;
        $duplicateMinutes = $settings['prevent_duplicate_minutes'] ?? 5;

        if ($duplicateMinutes > 0) {
            $recentCheckIn = CheckIn::query()
                ->where('membership_id', $membership->id)
                ->where('team_id', $team->id)
                ->where('created_at', '>=', now()->subMinutes($duplicateMinutes))
                ->exists();

            if ($recentCheckIn) {
                return [
                    'success' => false,
                    'check_in' => null,
                    'message' => "Already checked in within the last {$duplicateMinutes} minutes.",
                    'membership' => $membership,
                ];
            }
        }

        $gymId = $data['gym_id'] ?? null;

        if ($gymId === null) {
            $activeGyms = $team->gyms()
                ->active()
                ->select('id')
                ->orderBy('id')
                ->limit(2)
                ->get();

            if ($activeGyms->count() === 1) {
                $gymId = $activeGyms->first()->id;
            }
        }

        $checkIn = CheckIn::create([
            'membership_id' => $membership->id,
            'team_id' => $team->id,
            'gym_id' => $gymId,
            'checked_in_by' => $staffUserId,
            'method' => CheckInMethod::from($data['method']),
        ]);

        $membership->increment('entries_used');
        $membership->refresh()->loadMissing('plan');
        $membership->syncExpiredStatus();

        if ($membership->shouldRotateAccessCodeOnCheckIn()) {
            $membership->regenerateAccessCode();
        }

        return [
            'success' => true,
            'check_in' => $checkIn->load(['membership.user', 'membership.plan', 'gym']),
            'message' => "Welcome, {$membership->customer_name}!",
            'membership' => $membership,
        ];
    }

    private function resolveInvalidMembershipMessage(Membership $membership): string
    {
        if ($membership->status !== MembershipStatus::Active) {
            return "This membership is {$membership->status->value}.";
        }

        if ($membership->starts_at?->isFuture()) {
            return 'This membership is not active yet.';
        }

        if ($membership->hasReachedEntryLimit()) {
            return 'This membership has no remaining entries.';
        }

        if ($membership->hasExpiredAccessWindow()) {
            return 'This membership is expired.';
        }

        return 'This membership is not valid for check-in.';
    }
}
