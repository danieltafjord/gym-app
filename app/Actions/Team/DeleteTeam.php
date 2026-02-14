<?php

namespace App\Actions\Team;

use App\Models\Team;

class DeleteTeam
{
    public function handle(Team $team): void
    {
        $team->delete();
    }
}
