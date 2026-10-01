<?php

namespace App\Services;

use App\Models\ShiftSchedule;
use App\Models\ShiftSlot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class WorkforcePlanning
{
    public function allocateTasks(ShiftSlot $slot): array
    {
        return DB::transaction(function () use ($slot) {
            ShiftSlot::whereKey($slot->id)->lockForUpdate()->firstOrFail();
            $tasks = DB::table('work_tasks')->where('shift_slot_id', $slot->id)->orderBy('starts_at')->get();
            DB::table('work_task_assignments')->whereIn('work_task_id', $tasks->pluck('id'))->delete();
            $members = $slot->assignments()->with('member')->where('status', '!=', 'cancelled')->get();
            $missing = [];
            foreach ($tasks as $task) {
                $candidates = $members->filter(function ($assignment) use ($task) {
                    if (! $assignment->member || $assignment->member->status !== 'active') {
                        return false;
                    }
                    if ($assignment->break_starts_at && $assignment->break_starts_at < $task->ends_at && $assignment->break_ends_at > $task->starts_at) {
                        return false;
                    }
                    if ($task->skill && ! DB::table('member_skills')->where('member_id', $assignment->member_id)->where('skill', $task->skill)->where('level', '>=', $task->minimum_level)->where(fn ($q) => $q->whereNull('expires_on')->orWhere('expires_on', '>=', substr($task->ends_at, 0, 10)))->exists()) {
                        return false;
                    }

                    return ! DB::table('work_task_assignments as a')->join('work_tasks as t', 't.id', '=', 'a.work_task_id')->where('a.member_id', $assignment->member_id)->where('t.starts_at', '<', $task->ends_at)->where('t.ends_at', '>', $task->starts_at)->exists();
                })->sortBy(fn ($assignment) => DB::table('work_task_assignments as a')->join('work_tasks as t', 't.id', '=', 'a.work_task_id')->where('a.member_id', $assignment->member_id)->where('t.name', $task->name)->where('t.starts_at', '<', $task->ends_at)->count())->take($task->headcount);
                foreach ($candidates as $assignment) {
                    DB::table('work_task_assignments')->insert(['work_task_id' => $task->id, 'member_id' => $assignment->member_id, 'created_at' => now(), 'updated_at' => now()]);
                }
                if ($candidates->count() < $task->headcount) {
                    $missing[] = $task->name . '：' . ($task->headcount - $candidates->count()) . '名不足';
                }
            }

            return $missing;
        });
    }

    public function diagnose(ShiftSchedule $schedule): array
    {
        $rules = app(WorkforceRules::class);
        $warnings = [];
        foreach ($schedule->shiftSlots()->with('assignments.member')->get() as $slot) {
            $active = $slot->assignments->where('status', '!=', 'cancelled')->filter(fn ($a) => $a->member && $a->member->status === 'active');
            foreach ($active as $assignment) {
                foreach ($rules->violations($assignment->member, $slot, $active->pluck('member_id')->all()) as $warning) {
                    $warnings[] = $assignment->member->displayName() . ' ' . $slot->starts_at->format('n/j') . ' ' . $warning;
                }
            }
        }
        $rows = collect(app(ShiftAnalytics::class)->forYear($schedule->tenant_id, (int) $schedule->starts_on->format('Y'))['rows'])->where('schedule_id', $schedule->id);
        $configured = $rows->where('configured', true);
        $required = $configured->sum('required');
        $shortage = $configured->sum('shortage');
        $excess = $configured->sum('excess');
        $score = $required > 0 && $rows->every(fn ($r) => $r['configured']) ? max(0, (int) round(100 * (1 - ($shortage + $excess) / $required) - min(30, count($warnings) * 5))) : null;

        return compact('score', 'required', 'shortage', 'excess', 'warnings');
    }

    /** Transparent historical estimate; never presented as the vendor's AI. */
    public function recommend(int $tenantId, int $storeId): array
    {
        $slots = ShiftSlot::whereHas('shiftSchedule', fn ($q) => $q->where('tenant_id', $tenantId)->where('store_id', $storeId)->where('status', 'published'))
            ->where('starts_at', '>=', now()->subMonths(6))->where('starts_at', '<', now())->withCount(['assignments' => fn ($q) => $q->where('status', '!=', 'cancelled')])->get();

        return $slots->groupBy(fn ($s) => $s->starts_at->dayOfWeek)->map(function ($items, $weekday) {
            $start = $items->groupBy(fn ($s) => $s->starts_at->format('H:i'))->sortByDesc->count()->keys()->first();
            $end = $items->groupBy(fn ($s) => $s->ends_at->format('H:i'))->sortByDesc->count()->keys()->first();

            return ['weekday' => $weekday, 'starts_at' => $start, 'ends_at' => $end, 'headcount' => max(1, (int) ceil($items->avg('assignments_count'))), 'samples' => $items->count()];
        })->values()->all();
    }

    public function forecast(int $storeId, string $date): array
    {
        $target = CarbonImmutable::parse($date);
        $rows = DB::table('pos_records')->where('store_id', $storeId)->where('interval_start', '>=', $target->subWeeks(8))->where('interval_start', '<', $target)->get()
            ->filter(fn ($r) => CarbonImmutable::parse($r->interval_start)->dayOfWeek === $target->dayOfWeek);
        $capacity = app(WorkforceRules::class)->policy($storeId)['customers_per_register'] ?? null;

        return $rows->groupBy(fn ($r) => CarbonImmutable::parse($r->interval_start)->format('H:i'))->sortKeys()->map(fn ($items, $time) => ['time' => $time, 'customers' => round($items->avg('customers'), 1), 'registers' => $capacity ? (int) ceil($items->avg('customers') / $capacity) : null, 'samples' => $items->count()])->values()->all();
    }
}
