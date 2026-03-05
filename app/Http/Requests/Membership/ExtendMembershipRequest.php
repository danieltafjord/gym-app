<?php

namespace App\Http\Requests\Membership;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExtendMembershipRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ends_at' => ['required', 'date', 'after:today'],
            'reactivate' => ['boolean'],
        ];
    }
}
