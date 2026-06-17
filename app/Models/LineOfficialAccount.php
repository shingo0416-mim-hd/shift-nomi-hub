<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id',
    'channel_id',
    'channel_access_token',
    'channel_secret',
    'webhook_url',
    'line_at_id',
    'line_timeline_url',
    'is_active',
])]
class LineOfficialAccount extends Model
{
    use SoftDeletes;

    protected $hidden = [
        'channel_access_token',
        'channel_secret',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel_access_token' => 'encrypted',
            'channel_secret' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
