<?php

namespace App\Services;

use App\Models\AvailabilityRequest;
use App\Models\Member;
use App\Models\ShiftSchedule;
use Illuminate\Support\Collection;

class ShiftScheduleOperations
{
    /**
     * @param  Collection<int, ShiftSchedule>  $schedules
     * @return Collection<int, ShiftSchedule>
     */
    public function enrich(Collection $schedules): Collection
    {
        if ($schedules->isEmpty()) {
            return $schedules;
        }

        $tenantId = (int) $schedules->first()->tenant_id;
        $rangeStart = $schedules->min(fn (ShiftSchedule $schedule) => $schedule->getRawOriginal('starts_on'));
        $rangeEnd = $schedules->max(fn (ShiftSchedule $schedule) => $schedule->getRawOriginal('ends_on'));

        $members = Member::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('is_shift_submitter', true)
            ->get(['id', 'store_id', 'name', 'display_name', 'line_name']);
        $requests = AvailabilityRequest::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('work_date', '>=', $rangeStart)
            ->whereDate('work_date', '<=', $rangeEnd)
            ->get(['member_id', 'work_date', 'preference', 'available_from', 'available_until', 'notes']);

        return $schedules->each(function (ShiftSchedule $schedule) use ($members, $requests): void {
            $storeIds = $schedule->days
                ->where('is_day_off', false)
                ->pluck('store_id')
                ->push($schedule->store_id)
                ->filter()
                ->unique();
            $eligibleMembers = $members->whereIn('store_id', $storeIds);
            $expectedDates = $schedule->days->map(fn ($day) => substr((string) $day->getRawOriginal('scheduled_on'), 0, 10))->unique();
            $scheduleRequests = $requests->filter(fn (AvailabilityRequest $request) => $eligibleMembers->contains('id', $request->member_id)
                && $expectedDates->contains(substr((string) $request->getRawOriginal('work_date'), 0, 10)));
            $submittedByMember = $scheduleRequests->groupBy('member_id')->map(fn (Collection $items) => $items->map(fn (AvailabilityRequest $request) => substr((string) $request->getRawOriginal('work_date'), 0, 10))->unique()->count());
            $completed = $expectedDates->isEmpty()
                ? 0
                : $eligibleMembers->filter(fn (Member $member) => ($submittedByMember[$member->id] ?? 0) >= $expectedDates->count())->count();
            $partial = $eligibleMembers->filter(fn (Member $member) => ($submittedByMember[$member->id] ?? 0) > 0 && ($submittedByMember[$member->id] ?? 0) < $expectedDates->count())->count();

            $schedule->setAttribute('submission_members', $eligibleMembers->map(function (Member $member) use ($scheduleRequests, $expectedDates): array {
                $entries = $scheduleRequests->where('member_id', $member->id)->sortBy('work_date')->values();
                $count = $entries->count();

                return [
                    'name' => $member->displayName(),
                    'status' => $count === 0 ? '未提出' : ($count >= $expectedDates->count() ? '提出完了' : '一部入力'),
                    'submitted_days' => $count,
                    'expected_days' => $expectedDates->count(),
                    'entries' => $entries,
                ];
            })->values());

            $required = $schedule->days->where('is_day_off', false)->sum(fn ($day) => max(1, (int) $day->required_headcount));
            $assigned = $schedule->shiftSlots->sum(fn ($slot) => $slot->assignments
                ->filter(fn ($assignment) => $assignment->member && $assignment->status !== 'cancelled')
                ->count());
            $shortage = max(0, $required - $assigned);

            $schedule->setAttribute('operations', [
                'stage' => $this->stage($schedule),
                'eligible_members' => $eligibleMembers->count(),
                'completed_members' => $completed,
                'partial_members' => $partial,
                'unsubmitted_members' => max(0, $eligibleMembers->count() - $completed - $partial),
                'submission_percent' => $eligibleMembers->isEmpty() ? 0 : (int) round(($completed / $eligibleMembers->count()) * 100),
                'expected_days' => $expectedDates->count(),
                'required_headcount' => $required,
                'assigned_headcount' => $assigned,
                'shortage_headcount' => $shortage,
                'coverage_percent' => $required === 0 ? 100 : min(100, (int) round(($assigned / $required) * 100)),
            ]);
        });
    }

    private function stage(ShiftSchedule $schedule): string
    {
        if ($schedule->status === 'published') {
            return 'published';
        }

        if ($schedule->auto_scheduled_at || $schedule->shiftSlots->isNotEmpty()) {
            return 'reviewing';
        }

        if ($schedule->submission_deadline_at) {
            return $schedule->submission_deadline_at->isPast() ? 'overdue' : 'collecting';
        }

        return 'setup';
    }
}
