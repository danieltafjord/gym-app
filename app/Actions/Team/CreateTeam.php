<?php

namespace App\Actions\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;

class CreateTeam
{
    /**
     * @param  array{name: string, description?: string|null, logo_path?: string|null}  $data
     */
    public function handle(User $owner, array $data): Team
    {
        do {
            $slug = Str::slug($data['name']).'-'.Str::random(5);
        } while (
            in_array($slug, Team::RESERVED_SLUGS, true)
            || Team::where('slug', $slug)->exists()
        );

        $team = Team::create([
            'owner_id' => $owner->id,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'logo_path' => $data['logo_path'] ?? null,
        ]);

        setPermissionsTeamId($team->id);
        $owner->assignRole('team-owner');

        return $team;
    }
}
