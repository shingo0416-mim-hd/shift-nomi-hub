<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id',
    'store_id',
    'starts_on',
    'ends_on',
    'submission_deadline_at',
    'auto_schedule_enabled',
    'auto_scheduled_at',
    'notification_sent_at',
    'status',
    'created_by',
    'published_by',
    'published_at',
])]
class ShiftSchedule extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date:Y-m-d',
            'ends_on' => 'date:Y-m-d',
            'submission_deadline_at' => 'datetime',
            'auto_schedule_enabled' => 'boolean',
            'auto_scheduled_at' => 'datetime',
            'notification_sent_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function shiftSlots(): HasMany
    {
        return $this->hasMany(ShiftSlot::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(ShiftScheduleDay::class)->orderBy('scheduled_on');
    }
}
