<?php

namespace App\Actions\MembershipPlan;

use App\Models\MembershipPlan;

class DeleteMembershipPlan
{
    public function handle(MembershipPlan $plan): void
    {
        $plan->delete();
    }
}
