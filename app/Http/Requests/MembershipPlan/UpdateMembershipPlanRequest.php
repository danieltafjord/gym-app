<?php

namespace App\Http\Requests\MembershipPlan;

use App\Enums\AccessCodeStrategy;
use App\Enums\AccessDurationUnit;
use App\Enums\ActivationMode;
use App\Enums\BillingPeriod;
use App\Enums\PlanType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipPlanRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizePriceInput('price', 'price_cents');
        $this->normalizePriceInput('yearly_price', 'yearly_price_cents');

        if ($this->has('requires_account')) {
            $this->merge([
                'requires_account' => $this->boolean('requires_account'),
            ]);
        }

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

    private function normalizePriceInput(string $field, string $target): void
    {
        if (! $this->has($field)) {
            return;
        }

        $value = $this->input($field);

        if ($value === null || $value === '') {
            $this->merge([$target => null]);

            return;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            return;
        }

        $normalizedValue = str_replace(',', '.', trim((string) $value));

        if (! is_numeric($normalizedValue)) {
            return;
        }

        $this->merge([
            $target => (int) round(((float) $normalizedValue) * 100),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $plan = $this->route('plan');
        $planType = $this->input('plan_type', $plan?->plan_type?->value ?? PlanType::Recurring->value);

        return [
            'plan_type' => ['sometimes', 'required', Rule::enum(PlanType::class)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_cents' => ['sometimes', 'required', 'integer', 'min:0'],
            'yearly_price_cents' => ['nullable', 'integer', 'min:0'],
            'billing_period' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $planType === PlanType::Recurring->value && $this->has('billing_period')),
                Rule::enum(BillingPeriod::class),
            ],
            'access_duration_value' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $planType === PlanType::OneTime->value && $this->has('access_duration_unit')),
                'integer',
                'min:1',
            ],
            'access_duration_unit' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $planType === PlanType::OneTime->value && $this->has('access_duration_value')),
                Rule::enum(AccessDurationUnit::class),
            ],
            'activation_mode' => ['sometimes', 'required', Rule::enum(ActivationMode::class)],
            'requires_account' => ['sometimes', 'boolean'],
            'access_code_strategy' => ['sometimes', 'required', Rule::enum(AccessCodeStrategy::class)],
            'max_entries' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
