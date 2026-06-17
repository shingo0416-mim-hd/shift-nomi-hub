<?php

namespace App\Http\Requests\Liff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'store_id' => ['nullable', Rule::exists('stores', 'id')->where('tenant_id', $this->input('tenant_id'))],
            'registration_token' => ['nullable', 'string', 'max:80'],
            'line_user_id' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'picture_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
