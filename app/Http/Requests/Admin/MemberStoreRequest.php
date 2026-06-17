<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;
        $memberId = $this->route('member')?->id ?? $this->route('member');

        return [
            'store_id' => ['nullable', Rule::exists('stores', 'id')->where('tenant_id', $tenantId)],
            'name' => ['required', 'string', 'max:255'],
            'line_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('members', 'line_id')
                    ->where('tenant_id', $tenantId)
                    ->ignore($memberId),
            ],
            'line_name' => ['nullable', 'string', 'max:255'],
            'cline_id' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'is_shift_submitter' => ['nullable', 'boolean'],
            'is_remind_disabled' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'array'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
