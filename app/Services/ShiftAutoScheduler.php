<?php

namespace App\Services;

use App\Models\AvailabilityRequest;
use App\Models\Member;
use App\Models\ShiftSchedule;
use App\Models\ShiftScheduleDay;
use App\Models\ShiftSlot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShiftAutoScheduler
{
    public function __construct(private readonly LineShiftNotificationService $notifications)
    {
    }

    public function processDueSchedules(): int
    {
        $scheduleIds = ShiftSchedule::query()
            ->where('status', 'draft')
            ->where('auto_schedule_enabled', true)
            ->whereNotNull('submission_deadline_at')
            ->where('submission_deadline_at', '<=', now())
            ->whereNull('auto_scheduled_at')
            ->pluck('id');

        $scheduleIds->each(fn (int $scheduleId) => $this->finalize($scheduleId));

        ShiftSchedule::query()
            ->whereNotNull('auto_scheduled_at')
            ->whereNull('notification_sent_at')
            ->with(['tenant.lineOfficialAccount', 'shiftSlots.assignments.member'])
            ->get()
            ->each(fn (ShiftSchedule $schedule) => $this->notifications->send($schedule));

        return $scheduleIds->count();
    }

    public function finalize(int $scheduleId): ?ShiftSchedule
    {
        $schedule = DB::transaction(function () use ($scheduleId): ?ShiftSchedule {
            $schedule = ShiftSchedule::query()->lockForUpdate()->find($scheduleId);
            if (! $schedule || $schedule->auto_scheduled_at) {
                return null;
            }

            $schedule->load(['days.store']);
            $assignmentCounts = $this->monthlyAssignmentCounts($schedule);

            foreach ($schedule->days as $day) {
                if ($day->is_day_off || ! $day->starts_at || ! $day->ends_at) {
                    continue;
                }

                $slot = $this->createSlot($schedule, $day);
                $candidates = $this->candidates($schedule, $day, $slot, $assignmentCounts);
                $selectedMembers = collect();
                foreach ($candidates as $candidate) {
                    if (app(WorkforceRules::class)->violations($candidate, $slot, $selectedMembers->pluck('id')->all())) {
                        continue;
                    }
                    $selectedMembers->push($candidate);
                    if ($selectedMembers->count() >= ($day->required_headcount ?: 1)) {
                        break;
                    }
                }

                $slot->assignments()->delete();
                foreach ($selectedMembers as $member) {
                    $slot->assignments()->create(['member_id' => $member->id, 'status' => 'assigned']);
                    $assignmentCounts[$member->id] = ($assignmentCounts[$member->id] ?? 0) + 1;
                }

                app(WorkforceRules::class)->applyBreaks($slot);
                $slot->update([
                    'status' => $selectedMembers->count() >= $slot->required_headcount ? 'filled' : 'understaffed',
                ]);
            }

            $schedule->update([
                'status' => 'published',
                'published_at' => now(),
                'auto_scheduled_at' => now(),
            ]);

            return $schedule;
        });

        if ($schedule) {
            $schedule->load(['tenant.lineOfficialAccount', 'shiftSlots.assignments.member']);
            $this->notifications->send($schedule);
        }

        return $schedule;
    }

    /** @return array<int, int> */
    private function monthlyAssignmentCounts(ShiftSchedule $schedule): array
    {
        return DB::table('shift_assignments')
            ->join('shift_slots', 'shift_slots.id', '=', 'shift_assignments.shift_slot_id')
            ->join('shift_schedules', 'shift_schedules.id', '=', 'shift_slots.shift_schedule_id')
            ->where('shift_schedules.tenant_id', $schedule->tenant_id)
            ->whereBetween('shift_slots.starts_at', [$schedule->starts_on->startOfDay(), $schedule->ends_on->endOfDay()])
            ->where('shift_schedules.id', '!=', $schedule->id)
            ->where('shift_assignments.status', '!=', 'cancelled')
            ->where('shift_schedules.status', '!=', 'archived')
            ->select('shift_assignments.member_id', DB::raw('count(*) as assignment_count'))
            ->groupBy('shift_assignments.member_id')
            ->pluck('assignment_count', 'shift_assignments.member_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    private function createSlot(ShiftSchedule $schedule, ShiftScheduleDay $day): ShiftSlot
    {
        $startsAt = CarbonImmutable::parse("{$day->scheduled_on->toDateString()} {$day->starts_at}");
        $endsAt = CarbonImmutable::parse("{$day->scheduled_on->toDateString()} {$day->ends_at}");
        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            $endsAt = $endsAt->addDay();
        }

        return $schedule->shiftSlots()->updateOrCreate(
            ['title' => '自動編成', 'starts_at' => $startsAt],
            [
                'ends_at' => $endsAt,
                'required_headcount' => $day->required_headcount ?: 1,
                'status' => 'open',
                'notes' => $day->store?->name,
            ],
        );
    }

    /** @param array<int, int> $assignmentCounts
     * @return Collection<int, Member>
     */
    private function candidates(ShiftSchedule $schedule, ShiftScheduleDay $day, ShiftSlot $slot, array $assignmentCounts): Collection
    {
        $help = app(WorkforceRules::class)->policy($day->store_id)['help_member_ids'] ?? [];
        $requests = AvailabilityRequest::query()
            ->with(['member.schedulingProfile'])
            ->where('tenant_id', $schedule->tenant_id)
            ->whereDate('work_date', $day->scheduled_on)
            ->whereIn('preference', ['available', 'preferred'])
            ->whereHas('member', fn ($query) => $query
                ->where('status', 'active')
                ->where('is_shift_submitter', true)
                ->where(fn ($stores) => $stores->where('store_id', $day->store_id)->orWhereIn('id', $help)))
            ->get()
            ->filter(fn (AvailabilityRequest $request) => $this->coversShift($request, $day))
            ->reject(fn (AvailabilityRequest $request) => $this->hasOverlap($request->member_id, $slot));

        return $requests
            ->sortByDesc(fn (AvailabilityRequest $request) => $this->score($request, $day, $assignmentCounts))
            ->map(fn (AvailabilityRequest $request) => $request->member)
            ->values();
    }

    private function coversShift(AvailabilityRequest $request, ShiftScheduleDay $day): bool
    {
        if (! $request->available_from || ! $request->available_until) {
            return true;
        }

        $date = $day->scheduled_on->toDateString();
        $availableStart = CarbonImmutable::parse($date . ' ' . $request->available_from);
        $availableEnd = CarbonImmutable::parse($date . ' ' . $request->available_until);
        if ($availableEnd->lte($availableStart)) {
            $availableEnd = $availableEnd->addDay();
        }
        return $availableStart->lte($slotStart = CarbonImmutable::parse($date . ' ' . $day->starts_at))
            && $availableEnd->gte(($slotEnd = CarbonImmutable::parse($date . ' ' . $day->ends_at))->lte($slotStart) ? $slotEnd->addDay() : $slotEnd);
    }

    private function hasOverlap(int $memberId, ShiftSlot $slot): bool
    {
        return DB::table('shift_assignments')
            ->join('shift_slots', 'shift_slots.id', '=', 'shift_assignments.shift_slot_id')
            ->where('shift_assignments.member_id', $memberId)
            ->where('shift_assignments.status', '!=', 'cancelled')
            ->where('shift_slots.id', '!=', $slot->id)
            ->where('shift_slots.starts_at', '<', $slot->ends_at)
            ->where('shift_slots.ends_at', '>', $slot->starts_at)
            ->exists();
    }

    /** @param array<int, int> $assignmentCounts */
    private function score(AvailabilityRequest $request, ShiftScheduleDay $day, array $assignmentCounts): float
    {
        $profile = $request->member->schedulingProfile;
        $newcomerBonus = $request->member->isNewcomerOn($day->scheduled_on) ? 25 : 0;
        $fairnessBonus = max(0, 20 - (($assignmentCounts[$request->member_id] ?? 0) * 2));

        return ($request->preference === 'preferred' ? 30 : 15)
            + (($profile?->attendance_score ?? 50) * 0.2)
            + (($profile?->popularity_score ?? 50) * 0.1)
            + max(-30, min(30, $profile?->priority_points ?? 0))
            + $newcomerBonus
            + $fairnessBonus;
    }
}
