<?php

namespace App\Http\Requests\CheckIn;

use App\Enums\CheckInMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCheckInSettingsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'allowed_methods' => ['required', 'array', 'min:1'],
            'allowed_methods.*' => ['required', 'string', Rule::in(array_column(CheckInMethod::cases(), 'value'))],
            'require_gym_selection' => ['required', 'boolean'],
            'prevent_duplicate_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'kiosk_mode' => ['required', 'string', Rule::in(['camera', 'barcode_scanner'])],
        ];
    }
}
