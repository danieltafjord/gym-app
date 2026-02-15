<?php

namespace App\Http\Requests\CheckIn;

use App\Enums\CheckInMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessCheckInRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'access_code' => ['required', 'string', 'size:24'],
            'gym_id' => ['nullable', 'integer', 'exists:gyms,id'],
            'method' => ['required', 'string', Rule::in(array_column(CheckInMethod::cases(), 'value'))],
        ];
    }
}
