<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'default_currency' => $this->default_currency,
            'default_language' => $this->default_language,
            'logo_path' => $this->logo_path,
            'is_active' => $this->is_active,
            'owner' => $this->whenLoaded('owner', fn () => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'email' => $this->owner->email,
            ]),
            'gyms_count' => $this->whenCounted('gyms'),
            'members_count' => $this->whenCounted('memberships'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
