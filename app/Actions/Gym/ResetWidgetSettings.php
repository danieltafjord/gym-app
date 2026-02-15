<?php

namespace App\Actions\Gym;

use App\Models\Gym;

class ResetWidgetSettings
{
    public function handle(Gym $gym): Gym
    {
        $gym->update(['widget_settings' => null]);

        return $gym;
    }
}
