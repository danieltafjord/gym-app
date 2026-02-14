<?php

namespace App\Actions\Gym;

use App\Models\Gym;

class DeleteGym
{
    public function handle(Gym $gym): void
    {
        $gym->delete();
    }
}
