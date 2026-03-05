<?php

namespace App\Http\Requests\MembershipPlan;

use App\Enums\BillingPeriod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMembershipPlanRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $features = $this->input('features');

        if (! is_string($features)) {
            return;
        }

        $normalizedFeatures = array_values(array_filter(array_map(
            static fn (string $feature): string => trim($feature),
            explode(',', $features)
        ), static fn (string $feature): bool => $feature !== ''));

        $this->merge([
            'features' => $normalizedFeatures === [] ? null : $normalizedFeatures,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'yearly_price_cents' => ['nullable', 'integer', 'min:0'],
            'billing_period' => ['required', 'string', Rule::in(BillingPeriod::cases())],
            'features' => ['nullable', 'array'],
            'features.*' => ['string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
