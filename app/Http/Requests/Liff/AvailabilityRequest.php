<?php

namespace App\Http\Requests\Liff;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_MEMBER;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'work_date' => ['required', 'date_format:Y-m-d'],
            'available_from' => ['exclude_if:preference,unavailable', 'nullable', 'required_unless:preference,unavailable', 'date_format:H:i'],
            'available_until' => ['exclude_if:preference,unavailable', 'nullable', 'required_unless:preference,unavailable', 'date_format:H:i', 'after:available_from'],
            'preference' => ['required', Rule::in(['available', 'unavailable', 'preferred'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
