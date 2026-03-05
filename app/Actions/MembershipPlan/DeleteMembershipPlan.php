<?php

namespace App\Actions\MembershipPlan;

use App\Models\MembershipPlan;
use Illuminate\Validation\ValidationException;

class DeleteMembershipPlan
{
    public function handle(MembershipPlan $plan): void
    {
        if ($plan->memberships()->exists()) {
            throw ValidationException::withMessages([
                'delete_plan' => ['This plan cannot be deleted because it has memberships.'],
            ]);
        }

        $plan->delete();
    }
}
