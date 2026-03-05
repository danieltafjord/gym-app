<?php

namespace App\Http\Requests\Team;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $defaultCurrency = $this->input('default_currency');
        $defaultLanguage = $this->input('default_language');

        $this->merge([
            'default_currency' => is_string($defaultCurrency) ? strtoupper($defaultCurrency) : $defaultCurrency,
            'default_language' => is_string($defaultLanguage) ? strtolower($defaultLanguage) : $defaultLanguage,
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'default_currency' => ['sometimes', 'required', 'string', 'size:3', Rule::in(['USD', 'EUR', 'GBP', 'NOK'])],
            'default_language' => ['sometimes', 'required', 'string', 'max:5', Rule::in(['en', 'nb', 'sv', 'da'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
