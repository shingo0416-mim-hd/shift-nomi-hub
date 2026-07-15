<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'tenant_id',
    'name',
    'last_name',
    'first_name',
    'icon_url',
    'email',
    'email_verified_at',
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'two_factor_confirmed_at',
    'remember_token',
    'phone',
    'company',
    'employees',
    'company_type',
    'role',
    'ips',
    'login_at',
    'created_by',
    'updated_by',
    'deleted_by',
])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    public const ROLE_MEMBER = 0;

    public const ROLE_ADMIN = 1;

    public const ROLE_SUPER_ADMIN = 2;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'integer',
            'ips' => 'array',
            'login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function setLastNameAttribute(?string $value): void
    {
        $this->attributes['last_name'] = $value;
        $firstName = trim((string) ($this->attributes['first_name'] ?? $this->first_name ?? ''));
        $this->attributes['name'] = trim((string) $value.' '.$firstName);
    }

    public function setFirstNameAttribute(?string $value): void
    {
        $this->attributes['first_name'] = $value;
        $lastName = trim((string) ($this->attributes['last_name'] ?? $this->last_name ?? ''));
        $this->attributes['name'] = trim($lastName.' '.(string) $value);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function createdShiftSchedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class, 'created_by');
    }

    public function publishedShiftSchedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class, 'published_by');
    }

    public function sentTenantInvitations(): HasMany
    {
        return $this->hasMany(TenantInvitation::class, 'invited_by');
    }

    public function isAdmin(): bool
    {
        return $this->role >= self::ROLE_ADMIN;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user): void {
            if (auth()->check()) {
                $user->created_by = auth()->id();
                $user->updated_by = auth()->id();
            }
        });

        static::updating(function (User $user): void {
            if (auth()->check()) {
                $user->updated_by = auth()->id();
            }
        });

        static::deleting(function (User $user): void {
            if (auth()->check()) {
                $user->deleted_by = auth()->id();
                $user->save();
            }
        });
    }
}
