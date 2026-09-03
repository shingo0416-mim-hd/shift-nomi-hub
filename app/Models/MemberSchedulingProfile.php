<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'member_id',
    'attendance_score',
    'popularity_score',
    'priority_points',
    'newcomer_priority_until',
    'admin_notes',
])]
class MemberSchedulingProfile extends Model
{
    protected function casts(): array
    {
        return [
            'attendance_score' => 'integer',
            'popularity_score' => 'integer',
            'priority_points' => 'integer',
            'newcomer_priority_until' => 'date:Y-m-d',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
