<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'activated_at' => $this->activated_at,
            'entries_used' => $this->entries_used,
            'is_currently_valid' => $this->is_currently_valid,
            'cancelled_at' => $this->cancelled_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'plan' => new MembershipPlanResource($this->whenLoaded('plan')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
