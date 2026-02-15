<?php

namespace App\Http\Requests\Widget;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WidgetCheckoutConfirmRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subscription_id' => ['nullable', 'string'],
            'payment_intent_id' => ['nullable', 'string'],
            'membership_plan' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subscription_id.required_without' => 'A subscription ID or payment intent ID is required.',
            'payment_intent_id.required_without' => 'A subscription ID or payment intent ID is required.',
        ];
    }

    public function after(): array
    {
        return [
            function (\Illuminate\Validation\Validator $validator): void {
                if (! $this->input('subscription_id') && ! $this->input('payment_intent_id')) {
                    $validator->errors()->add(
                        'subscription_id',
                        'A subscription ID or payment intent ID is required.'
                    );
                }
            },
        ];
    }
}
