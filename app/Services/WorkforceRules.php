<?php

namespace App\Services;

use App\Models\Member;
use App\Models\ShiftSlot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class WorkforceRules
{
    public function policy(?int $storeId): array
    {
        $json = DB::table('workforce_policies')->where('store_id', $storeId)->value('settings');

        return $json ? json_decode($json, true) : [];
    }

    public function storeId(ShiftSlot $slot): int
    {
        return (int) ($slot->shiftSchedule->days()->whereDate('scheduled_on', $slot->starts_at)->value('store_id') ?: $slot->shiftSchedule->store_id);
    }

    /** Application-configured constraints, not a legal compliance determination. */
    public function violations(Member $member, ShiftSlot $slot, array $selectedMemberIds = []): array
    {
        $rules = array_replace($this->policy($this->storeId($slot)), array_filter($member->profiles['workforce_rules'] ?? [], fn ($value) => $value !== null && $value !== ''));
        $start = CarbonImmutable::instance($slot->starts_at);
        $end = CarbonImmutable::instance($slot->ends_at);
        $hours = max(0, $start->diffInMinutes($end) - ($slot->break_minutes ?? 0)) / 60;
        $assignments = DB::table('shift_assignments as a')->join('shift_slots as s', 's.id', '=', 'a.shift_slot_id')
            ->join('shift_schedules as c', 'c.id', '=', 's.shift_schedule_id')
            ->where('c.tenant_id', $member->tenant_id)->where('c.status', '!=', 'archived')
            ->where('a.member_id', $member->id)->where('a.status', '!=', 'cancelled')->where('s.id', '!=', $slot->id)
            ->where('s.ends_at', '>', $start->startOfMonth()->subDays(31))->where('s.starts_at', '<', $end->endOfMonth()->addDays(31))
            ->get(['s.starts_at', 's.ends_at', 's.break_minutes']);
        $warnings = [];
        $daily = $hours;
        $weekly = $hours;
        $monthly = $hours;
        $dates = [$start->toDateString() => true];
        foreach ($assignments as $assignment) {
            $from = CarbonImmutable::parse($assignment->starts_at);
            $to = CarbonImmutable::parse($assignment->ends_at);
            $duration = max(0, $from->diffInMinutes($to) - $assignment->break_minutes) / 60;
            if ($from->lt($end) && $to->gt($start)) {
                $warnings[] = '勤務時間が重複しています';
            }
            $gap = $to->lte($start) ? $to->diffInHours($start) : ($from->gte($end) ? $end->diffInHours($from) : null);
            if ($gap !== null && ! empty($rules['min_rest_hours']) && $gap < $rules['min_rest_hours']) {
                $warnings[] = '勤務間隔が設定値未満です';
            }
            if ($from->isSameDay($start)) {
                $daily += $duration;
            }
            if ($from->betweenIncluded($start->startOfWeek(), $start->endOfWeek())) {
                $weekly += $duration;
            }
            if ($from->isSameMonth($start, true)) {
                $monthly += $duration;
            }
            $dates[$from->toDateString()] = true;
        }
        foreach (['max_daily_hours' => [$daily, '1日の勤務時間'], 'max_weekly_hours' => [$weekly, '週の勤務時間'], 'max_monthly_hours' => [$monthly, '月の勤務時間']] as $key => [$actual, $label]) {
            if (! empty($rules[$key]) && $actual > $rules[$key]) {
                $warnings[] = $label . 'が設定上限を超えます';
            }
        }
        $consecutive = 1;
        foreach ([-1, 1] as $direction) {
            for ($date = $start->addDays($direction); isset($dates[$date->toDateString()]); $date = $date->addDays($direction)) {
                $consecutive++;
            }
        }
        if (! empty($rules['max_consecutive_days']) && $consecutive > $rules['max_consecutive_days']) {
            $warnings[] = '連続勤務日数が設定上限を超えます';
        }
        $workdays = count(array_filter(array_keys($dates), fn ($date) => substr($date, 0, 7) === $start->format('Y-m')));
        if (! empty($rules['min_monthly_days_off']) && $start->daysInMonth - $workdays < $rules['min_monthly_days_off']) {
            $warnings[] = '月の休日数が設定値未満です';
        }
        foreach ($rules['excluded_pairs'] ?? [] as $pair) {
            if (in_array($member->id, $pair) && count(array_intersect($pair, [...$selectedMemberIds, $member->id])) === 2) {
                $warnings[] = '同時配置を避ける組合せです';
            }
        }

        return array_values(array_unique($warnings));
    }

    public function applyBreaks(ShiftSlot $slot): array
    {
        $rules = $this->policy($this->storeId($slot));
        $minutes = (int) ($rules['break_minutes'] ?? 0);
        $duration = $slot->starts_at->diffInMinutes($slot->ends_at);
        if (! $minutes || $duration < ($rules['break_after_hours'] ?? 0) * 60) {
            return [];
        }
        $warnings = [];
        $occupied = [];
        foreach ($slot->assignments()->where('status', '!=', 'cancelled')->orderBy('id')->get() as $assignment) {
            $candidates = [];
            for ($offset = 15; $offset + $minutes <= $duration - 15; $offset += 15) {
                $start = CarbonImmutable::instance($slot->starts_at)->addMinutes($offset);
                $end = $start->addMinutes($minutes);
                if (! empty($rules['peak_start']) && ! empty($rules['peak_end'])) {
                    $peakStart = $start->startOfDay()->setTimeFromTimeString($rules['peak_start']);
                    $peakEnd = $start->startOfDay()->setTimeFromTimeString($rules['peak_end']);
                    if ($start->lt($peakEnd) && $end->gt($peakStart)) {
                        continue;
                    }
                }
                $overlap = count(array_filter($occupied, fn ($range) => $start->lt($range[1]) && $end->gt($range[0])));
                $candidates[] = ['start' => $start, 'end' => $end, 'cost' => $overlap * 10000 + abs($offset + $minutes / 2 - $duration / 2)];
            }
            usort($candidates, fn ($a, $b) => $a['cost'] <=> $b['cost']);
            if (! $candidates) {
                $warnings[] = '休憩を配置できません: ' . $assignment->member_id;

                continue;
            }
            $best = $candidates[0];
            DB::table('shift_assignments')->where('id', $assignment->id)->update(['break_starts_at' => $best['start'], 'break_ends_at' => $best['end'], 'updated_at' => now()]);
            $occupied[] = [$best['start'], $best['end']];
        }
        if (! $warnings) {
            $slot->update(['break_minutes' => $minutes]);
        }

        return $warnings;
    }
}
