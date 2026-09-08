<?php

namespace App\Services;

use App\Models\ShiftScheduleDay;
use App\Models\Store;
use Carbon\CarbonImmutable;

class ShiftAnalytics
{
    /** Aggregate planned staffing per schedule day, without inventing a diagnosis score. */
    public function forYear(int $tenantId, int $year): array
    {
        $stores = Store::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']);
        $days = ShiftScheduleDay::query()
            ->whereBetween('scheduled_on', ["{$year}-01-01", "{$year}-12-31"])
            ->whereHas('shiftSchedule', fn ($query) => $query->where('tenant_id', $tenantId)->whereIn('status', ['draft', 'published']))
            ->with(['shiftSchedule.shiftSlots.assignments.member'])
            ->orderBy('scheduled_on')->get();
        $rows = [];
        foreach ($days as $day) {
            $schedule = $day->shiftSchedule;
            $storeId = $day->store_id ?: $schedule->store_id;
            if (! $stores->contains('id', $storeId)) {
                continue;
            }
            $date = $day->scheduled_on->toDateString();
            $row = ['date' => $date, 'store_id' => $storeId, 'schedule_id' => $schedule->id, 'status' => $schedule->status];
            if (! $day->is_day_off && (! $day->starts_at || ! $day->ends_at)) {
                $rows[] = $row + ['configured' => false];
                continue;
            }
            $start = CarbonImmutable::parse("{$date} " . ($day->starts_at ?: '00:00:00'));
            $end = CarbonImmutable::parse("{$date} " . ($day->ends_at ?: '00:00:00'));
            if ($end->lessThanOrEqualTo($start)) {
                $end = $end->addDay();
            }
            $slots = $schedule->shiftSlots->filter(fn ($slot) => $slot->starts_at->toDateString() === $date && $slot->ends_at->greaterThan($slot->starts_at));
            $intervals = $slots->map(fn ($slot) => [
                'start' => $slot->starts_at->timestamp,
                'end' => $slot->ends_at->timestamp,
                'members' => $slot->assignments->filter(fn ($assignment) => $assignment->status !== 'cancelled' && $assignment->member && (int) $assignment->member->tenant_id === $tenantId)->pluck('member_id')->unique()->all(),
            ])->all();
            $metrics = $this->measure($start->timestamp, $end->timestamp, $day->is_day_off ? 0 : max(1, (int) $day->required_headcount), $intervals);
            $rows[] = $row + ['configured' => true] + $metrics;
        }

        return ['year' => $year, 'stores' => $stores->toArray(), 'rows' => $rows, 'generated_at' => now()->toIso8601String()];
    }

    /** Time intervals preserve shortages even when another interval is overstaffed. */
    public function measure(int $start, int $end, int $required, array $intervals): array
    {
        $boundaries = [$start, $end];
        foreach ($intervals as $interval) {
            $boundaries[] = $interval['start'];
            $boundaries[] = $interval['end'];
        }
        $boundaries = array_values(array_unique($boundaries));
        sort($boundaries);
        $totals = ['required' => 0, 'assigned' => 0, 'shortage' => 0, 'excess' => 0];
        for ($i = 0; $i < count($boundaries) - 1; $i++) {
            $from = $boundaries[$i];
            $hours = ($boundaries[$i + 1] - $from) / 3600;
            $needed = $from >= $start && $from < $end ? $required : 0;
            $members = [];
            foreach ($intervals as $interval) {
                if ($interval['start'] <= $from && $from < $interval['end']) {
                    $members = array_merge($members, $interval['members']);
                }
            }
            $assigned = count(array_unique($members));
            $totals['required'] += $hours * $needed;
            $totals['assigned'] += $hours * $assigned;
            $totals['shortage'] += $hours * max(0, $needed - $assigned);
            $totals['excess'] += $hours * max(0, $assigned - $needed);
        }

        return $totals;
    }
}
