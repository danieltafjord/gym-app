<?php

namespace App\Actions\Gym;

use App\Models\Gym;

class UpdateGym
{
    /**
     * @param  array{name?: string, address?: string|null, phone?: string|null, email?: string|null, is_active?: bool, max_capacity?: int|null, occupancy_tracking_enabled?: bool, show_occupancy_to_members?: bool, show_occupancy_predictions?: bool}  $data
     */
    public function handle(Gym $gym, array $data): Gym
    {
        $gym->update($data);

        return $gym->fresh();
    }
}
