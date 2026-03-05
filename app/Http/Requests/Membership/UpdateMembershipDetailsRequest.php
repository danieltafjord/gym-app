<?php

namespace App\Http\Requests\Membership;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipDetailsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $team = $this->route('team');

        return [
            'customer_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'membership_plan_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('membership_plans', 'id')->where('team_id', $team->id),
            ],
        ];
    }
}
