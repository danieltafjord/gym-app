<?php

namespace App\Actions\Team;

use App\Models\Team;

class UpdateTeam
{
    /**
     * @param  array{name?: string, description?: string|null, default_currency?: string, default_language?: string, logo_path?: string|null, is_active?: bool}  $data
     */
    public function handle(Team $team, array $data): Team
    {
        $team->update($data);

        return $team->fresh();
    }
}
