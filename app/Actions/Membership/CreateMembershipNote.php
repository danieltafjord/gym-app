<?php

namespace App\Actions\Membership;

use App\Models\Membership;
use App\Models\MembershipNote;
use App\Models\User;

class CreateMembershipNote
{
    public function handle(Membership $membership, User $author, string $content): MembershipNote
    {
        return $membership->notes()->create([
            'team_id' => $membership->team_id,
            'user_id' => $author->id,
            'content' => $content,
        ]);
    }
}
