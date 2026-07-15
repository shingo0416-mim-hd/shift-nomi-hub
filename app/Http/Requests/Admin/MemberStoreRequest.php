<?php

namespace App\Http\Requests\Admin;

use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lineMember = $this->attributes->get('lineMember');

        if ($lineMember) {
            return $lineMember->canManageMembers();
        }

        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'store_id' => ['required', Rule::exists('stores', 'id')->where('tenant_id', $tenantId)],
            'name' => ['nullable', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'role' => ['nullable', Rule::in([Member::ROLE_CAST, Member::ROLE_MANAGER, Member::ROLE_ADMIN])],
            'is_shift_submitter' => ['nullable', 'boolean'],
            'is_remind_disabled' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'array'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $lastName = trim((string) $this->input('last_name', ''));
        $firstName = trim((string) $this->input('first_name', ''));
        $name = trim((string) $this->input('name', ''));
        $displayName = trim((string) $this->input('display_name', ''));

        if ($name === '' && ($lastName !== '' || $firstName !== '')) {
            $name = trim("{$lastName} {$firstName}");
        }

        if ($displayName === '' && $name !== '') {
            $displayName = $name;
        }

        $this->merge([
            'name' => $name !== '' ? $name : null,
            'display_name' => $displayName,
        ]);
    }
}
