<?php

namespace App\Services;

use App\Models\AvailabilityRequest;
use App\Models\Member;
use App\Models\ShiftSchedule;
use Illuminate\Validation\ValidationException;

class AvailabilitySubmission
{
    public function save(Member $member, array $data): AvailabilityRequest
    {
        $closed = ShiftSchedule::query()
            ->where('tenant_id', $member->tenant_id)
            ->whereIn('status', ['draft', 'published'])
            ->whereDate('starts_on', '<=', $data['work_date'])
            ->whereDate('ends_on', '>=', $data['work_date'])
            ->where(fn ($query) => $query->where('store_id', $member->store_id)
                ->orWhereHas('days', fn ($days) => $days->where('store_id', $member->store_id)))
            ->where(fn ($query) => $query->where('status', 'published')
                ->orWhere('submission_deadline_at', '<=', now()))->exists();
        if ($closed) {
            throw ValidationException::withMessages(['work_date' => '提出期限を過ぎているか、確定済みのため変更できません。管理者へ連絡してください。']);
        }

        return AvailabilityRequest::query()->whereDate('work_date', $data['work_date'])->updateOrCreate(
            ['member_id' => $member->id],
            [
                'tenant_id' => $member->tenant_id,
                'work_date' => $data['work_date'],
                'preference' => $data['preference'],
                'available_from' => $data['preference'] === 'unavailable' ? null : ($data['available_from'] ?? null),
                'available_until' => $data['preference'] === 'unavailable' ? null : ($data['available_until'] ?? null),
                'notes' => $data['notes'] ?? null,
            ],
        );
    }
}
