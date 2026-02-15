<?php

namespace App\Actions\Gym;

use App\Models\Gym;

class UpdateWidgetSettings
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public function handle(Gym $gym, array $settings): Gym
    {
        $gym->update(['widget_settings' => $settings]);

        return $gym;
    }
}
