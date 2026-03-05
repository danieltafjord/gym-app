<?php

namespace App\Actions\Membership;

use App\Models\Membership;

class UpdateMembershipDetails
{
    /**
     * @param  array{customer_name?: string, email?: string, customer_phone?: string|null, membership_plan_id?: int}  $data
     */
    public function handle(Membership $membership, array $data): Membership
    {
        $membership->update($data);

        return $membership;
    }
}
