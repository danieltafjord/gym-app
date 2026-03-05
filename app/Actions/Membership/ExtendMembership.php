<?php

namespace App\Actions\Membership;

use App\Enums\MembershipStatus;
use App\Models\Membership;
use Carbon\Carbon;

class ExtendMembership
{
    public function handle(Membership $membership, Carbon $endsAt, bool $reactivate = false): Membership
    {
        $data = ['ends_at' => $endsAt];

        if ($reactivate && $membership->status === MembershipStatus::Expired) {
            $data['status'] = MembershipStatus::Active;
        }

        $membership->update($data);

        return $membership;
    }
}
