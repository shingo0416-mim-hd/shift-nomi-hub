<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id',
    'store_id',
    'user_id',
    'name',
    'last_name',
    'first_name',
    'name_kana',
    'last_name_kana',
    'first_name_kana',
    'line_id',
    'line_name',
    'icon_url',
    'cline_id',
    'cline_url',
    'company',
    'gender',
    'phone',
    'email',
    'password',
    'birth_date',
    'birth_year',
    'birth_month',
    'birth_day',
    'postal_code',
    'country',
    'address',
    'prefecture',
    'city',
    'street_address',
    'status',
    'comment',
    'remarks',
    'is_shift_submitter',
    'is_linked',
    'is_remind_disabled',
    'profiles',
    'tags',
    'ip_address',
    'login_at',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class Member extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_shift_submitter' => 'boolean',
            'is_linked' => 'boolean',
            'is_remind_disabled' => 'boolean',
            'profiles' => 'array',
            'tags' => 'array',
            'login_at' => 'datetime',
        ];
    }

    public function setClineIdAttribute(?string $value): void
    {
        $this->attributes['cline_id'] = $value;

        if (! empty($value)) {
            $this->attributes['cline_url'] = "https://cline-app.com/students/{$value}?private-talk";
        }
    }

    public function getAddressAttribute(): string
    {
        return trim("{$this->prefecture}{$this->city}{$this->street_address}");
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name} {$this->first_name}");
    }

    public function getFullNameKanaAttribute(): string
    {
        return trim("{$this->last_name_kana} {$this->first_name_kana}");
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
