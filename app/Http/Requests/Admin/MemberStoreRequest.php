<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()?->isAdmin() !== true) {
            return false;
        }

        $lineMember = $this->attributes->get('lineMember');

        return ! $lineMember || $lineMember->isCastAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'store_id' => ['nullable', Rule::exists('stores', 'id')->where('tenant_id', $tenantId)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'role' => ['nullable', Rule::in(['cast', 'admin'])],
            'is_shift_submitter' => ['nullable', 'boolean'],
            'is_remind_disabled' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'array'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
