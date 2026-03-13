<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipPlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'price_formatted' => $this->price_formatted,
            'yearly_price_cents' => $this->yearly_price_cents,
            'yearly_price_formatted' => $this->yearly_price_formatted,
            'access_duration_value' => $this->access_duration_value,
            'access_duration_unit' => $this->access_duration_unit?->value,
            'access_duration_label' => $this->access_duration_label,
            'activation_mode' => $this->activation_mode?->value,
            'requires_account' => $this->requires_account,
            'access_code_strategy' => $this->access_code_strategy?->value,
            'max_entries' => $this->max_entries,
            'billing_period' => $this->billing_period?->value,
            'plan_type' => $this->plan_type?->value,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'team_id' => $this->team_id,
            'stripe_product_id' => $this->stripe_product_id,
            'stripe_price_id' => $this->stripe_price_id,
            'stripe_yearly_price_id' => $this->stripe_yearly_price_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
