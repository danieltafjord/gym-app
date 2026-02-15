<?php

namespace App\Actions\Team;

use App\Models\Team;

class UpdateTeamWidgetSettings
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public function handle(Team $team, array $settings): Team
    {
        $team->update(['widget_settings' => $settings]);

        return $team;
    }
}
