<?php

namespace App\Actions\CheckIn;

use App\Models\Team;

class UpdateCheckInSettings
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public function handle(Team $team, array $settings): Team
    {
        $team->update(['check_in_settings' => $settings]);

        return $team;
    }
}
