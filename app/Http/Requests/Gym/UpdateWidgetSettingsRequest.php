<?php

namespace App\Http\Requests\Gym;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWidgetSettingsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hexColor = ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];
        $borderRadius = ['required', 'integer', 'min:0', 'max:32'];

        return [
            'primary_color' => $hexColor,
            'background_color' => $hexColor,
            'text_color' => $hexColor,
            'secondary_text_color' => $hexColor,
            'card_border_color' => $hexColor,
            'button_text_color' => $hexColor,
            'input_border_color' => $hexColor,
            'input_background_color' => $hexColor,
            'font_family' => ['required', 'string', 'max:255'],
            'card_border_radius' => $borderRadius,
            'button_border_radius' => $borderRadius,
            'input_border_radius' => $borderRadius,
            'padding' => ['required', 'integer', 'min:0', 'max:48'],
            'columns' => ['required', 'integer', 'min:1', 'max:4'],
            'show_features' => ['required', 'boolean'],
            'show_description' => ['required', 'boolean'],
            'button_text' => ['required', 'string', 'max:50'],
            'show_access_code' => ['required', 'boolean'],
            'show_success_details' => ['required', 'boolean'],
            'show_cta_card' => ['required', 'boolean'],
            'success_heading' => ['required', 'string', 'max:100'],
            'success_message' => ['required', 'string', 'max:255'],
        ];
    }
}
