<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'data'])]
class Tenant extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function lineLoginSetting(): HasOne
    {
        return $this->hasOne(LineLoginSetting::class);
    }

    public function lineLiffSetting(): HasOne
    {
        return $this->hasOne(LineLiffSetting::class);
    }

    public function lineOfficialAccount(): HasOne
    {
        return $this->hasOne(LineOfficialAccount::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function shiftSchedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class);
    }

    public function availabilityRequests(): HasMany
    {
        return $this->hasMany(AvailabilityRequest::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TenantInvitation::class);
    }
}
