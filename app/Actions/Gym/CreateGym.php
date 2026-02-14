<?php

namespace App\Actions\Gym;

use App\Models\Gym;
use App\Models\Team;
use Illuminate\Support\Str;

class CreateGym
{
    /**
     * @param  array{name: string, address?: string|null, phone?: string|null, email?: string|null}  $data
     */
    public function handle(Team $team, array $data): Gym
    {
        return Gym::create([
            'team_id' => $team->id,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
    }
}
