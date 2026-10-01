<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\ShiftSchedule;
use App\Models\ShiftSlot;
use App\Models\Store;
use App\Services\WorkforceExchange;
use App\Services\WorkforceMessaging;
use App\Services\WorkforcePlanning;
use App\Services\WorkforceRules;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkforceController extends Controller
{
    private function tenant(Request $request): int
    {
        $lineManager = $request->attributes->get('lineMember');
        abort_unless($lineManager?->canManageShiftSchedules() || ($request->user()?->isAdmin() && $request->user()->hasEnabledTwoFactorAuthentication()), 403);
        abort_unless(Schema::hasTable('workforce_policies'), 503, '運用機能は準備中です。');

        return (int) $request->user()->tenant_id;
    }

    private function slot(int $tenantId, mixed $id): ShiftSlot
    {
        return ShiftSlot::whereHas('shiftSchedule', fn ($q) => $q->where('tenant_id', $tenantId))->findOrFail($id);
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenant($request);
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d'], 'store_id' => ['nullable', 'integer']]);
        $stores = Store::where('tenant_id', $tenantId)->get();
        $members = Member::where('tenant_id', $tenantId)->orderBy('name')->get();
        $store = $request->filled('store_id') ? $stores->firstWhere('id', (int) $request->store_id) : $stores->first();
        abort_if($request->filled('store_id') && ! $store, 404);
        $schedules = ShiftSchedule::where('tenant_id', $tenantId)->where('status', '!=', 'archived')->with('shiftSlots.assignments.member')->latest('starts_on')->limit(24)->get();
        $slots = $schedules->flatMap->shiftSlots;
        $skills = DB::table('member_skills')->whereIn('member_id', $members->pluck('id'))->get();
        $tasks = DB::table('work_tasks')->whereIn('shift_slot_id', $slots->pluck('id'))->get();
        $taskAssignments = DB::table('work_task_assignments')->whereIn('work_task_id', $tasks->pluck('id'))->get();
        $vacancies = DB::table('shift_vacancies')->whereIn('shift_slot_id', $slots->pluck('id'))->get();
        $applications = DB::table('vacancy_applications')->whereIn('shift_vacancy_id', $vacancies->pluck('id'))->get();
        $messages = DB::table('workforce_messages')->where('tenant_id', $tenantId)->latest('id')->limit(50)->get();
        $receipts = DB::table('workforce_message_recipients')->whereIn('workforce_message_id', $messages->pluck('id'))->get();
        $policy = $store ? app(WorkforceRules::class)->policy($store->id) : [];
        $date = $request->input('date', now()->toDateString());
        $forecast = $store ? app(WorkforcePlanning::class)->forecast($store->id, $date) : [];
        $recommendations = $store ? app(WorkforcePlanning::class)->recommend($tenantId, $store->id) : [];

        $line = $request->attributes->has('lineMember');
        $routeParameters = $line ? ['tenant' => $request->attributes->get('tenantPath')] : [];
        $exportUrl = route($line ? 'line.admin.workforce.export' : 'admin.workforce.export', $routeParameters);
        $dashboardUrl = route($line ? 'line.admin.dashboard' : 'dashboard', $routeParameters);

        return view('admin.workforce', compact('exportUrl', 'dashboardUrl', 'stores', 'members', 'store', 'schedules', 'slots', 'skills', 'tasks', 'taskAssignments', 'vacancies', 'applications', 'messages', 'receipts', 'policy', 'date', 'forecast', 'recommendations'));
    }

    public function update(Request $request)
    {
        $tenantId = $this->tenant($request);
        $storeRule = Rule::exists('stores', 'id')->where('tenant_id', $tenantId);
        $memberRule = Rule::exists('members', 'id')->where('tenant_id', $tenantId)->whereNull('deleted_at');
        $action = $request->validate(['action' => ['required', Rule::in(['member_rules', 'policy', 'skill', 'task', 'allocate', 'breaks', 'diagnose', 'vacancy', 'accept', 'close', 'message', 'import', 'recommend'])]])['action'];
        $notice = '保存しました。';
        if ($action === 'member_rules') {
            $data = $request->validate(['member_id' => ['required', $memberRule], 'max_daily_hours' => ['nullable', 'numeric', 'between:1,24'], 'max_weekly_hours' => ['nullable', 'numeric', 'between:1,168'], 'max_monthly_hours' => ['nullable', 'numeric', 'between:1,744'], 'min_monthly_days_off' => ['nullable', 'integer', 'between:0,31']]);
            $member = Member::where('tenant_id', $tenantId)->findOrFail($data['member_id']);
            unset($data['member_id']);
            $member->update(['profiles' => array_replace($member->profiles ?? [], ['workforce_rules' => $data])]);
        } elseif ($action === 'policy') {
            $data = $request->validate([
                'store_id' => ['required', $storeRule], 'max_daily_hours' => ['nullable', 'numeric', 'between:1,24'], 'max_weekly_hours' => ['nullable', 'numeric', 'between:1,168'], 'max_monthly_hours' => ['nullable', 'numeric', 'between:1,744'], 'min_rest_hours' => ['nullable', 'numeric', 'between:0,48'], 'max_consecutive_days' => ['nullable', 'integer', 'between:1,31'], 'min_monthly_days_off' => ['nullable', 'integer', 'between:0,31'], 'break_minutes' => ['nullable', 'integer', 'between:0,180'], 'break_after_hours' => ['nullable', 'numeric', 'between:0,24'], 'peak_start' => ['nullable', 'required_with:peak_end', 'date_format:H:i'], 'peak_end' => ['nullable', 'required_with:peak_start', 'date_format:H:i', 'after:peak_start'], 'customers_per_register' => ['nullable', 'integer', 'between:1,1000'], 'help_member_ids' => ['nullable', 'array'], 'help_member_ids.*' => ['integer', 'distinct', $memberRule], 'excluded_pairs_text' => ['nullable', 'string', 'max:4000'],
            ]);
            $pairs = [];
            foreach (preg_split('/\R/', trim($data['excluded_pairs_text'] ?? '')) as $line) {
                if ($line === '') {
                    continue;
                }
                $ids = array_map('trim', explode(',', $line));
                if (count($ids) !== 2 || count(array_unique($ids)) !== 2 || ! ctype_digit($ids[0]) || ! ctype_digit($ids[1]) || Member::where('tenant_id', $tenantId)->whereIn('id', $ids)->count() !== 2) {
                    throw ValidationException::withMessages(['excluded_pairs_text' => '組合せは同じ組織のスタッフIDを2つ、カンマ区切りで指定してください。']);
                }
                $pairs[] = array_map('intval', $ids);
            }
            $storeId = $data['store_id'];
            unset($data['store_id'], $data['excluded_pairs_text']);
            $data['excluded_pairs'] = $pairs;
            DB::table('workforce_policies')->updateOrInsert(['store_id' => $storeId], ['settings' => json_encode($data), 'created_at' => now(), 'updated_at' => now()]);
        } elseif ($action === 'skill') {
            $data = $request->validate(['member_id' => ['required', $memberRule], 'skill' => ['required', 'string', 'max:100'], 'level' => ['required', 'integer', 'between:0,5'], 'expires_on' => ['nullable', 'date_format:Y-m-d']]);
            DB::table('member_skills')->updateOrInsert(['member_id' => $data['member_id'], 'skill' => $data['skill']], $data + ['created_at' => now(), 'updated_at' => now()]);
        } elseif (in_array($action, ['task', 'allocate', 'breaks', 'vacancy'])) {
            $request->validate(['slot_id' => ['required', 'integer']]);
            $slot = $this->slot($tenantId, $request->slot_id);
            if ($action === 'task') {
                $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'skill' => ['nullable', 'string', 'max:100'], 'minimum_level' => ['required', 'integer', 'between:1,5'], 'headcount' => ['required', 'integer', 'between:1,50'], 'starts_at' => ['required', 'date', 'after_or_equal:'.$slot->starts_at->toIso8601String()], 'ends_at' => ['required', 'date', 'after:starts_at', 'before_or_equal:'.$slot->ends_at->toIso8601String()]]);
                $data['starts_at'] = CarbonImmutable::parse($data['starts_at'])->format('Y-m-d H:i:s');
                $data['ends_at'] = CarbonImmutable::parse($data['ends_at'])->format('Y-m-d H:i:s');
                DB::table('work_tasks')->insert($data + ['shift_slot_id' => $slot->id, 'created_at' => now(), 'updated_at' => now()]);
            } elseif ($action === 'allocate' || $action === 'breaks') {
                $warnings = $action === 'allocate' ? app(WorkforcePlanning::class)->allocateTasks($slot) : app(WorkforceRules::class)->applyBreaks($slot);
                if ($action === 'breaks') {
                    $warnings = [...$warnings, ...app(WorkforcePlanning::class)->allocateTasks($slot)];
                }
                $notice = $warnings ? implode(' / ', $warnings) : '割当を保存しました。';
            } else {
                $data = $request->validate(['message' => ['required', 'string', 'max:2000'], 'allow_help' => ['nullable', 'boolean']]);
                abort_if($slot->starts_at->isPast() || $slot->shiftSchedule->status !== 'published', 422, '公開済みの将来のシフトを選んでください。');
                if ($slot->assignments()->where('status', '!=', 'cancelled')->count() >= $slot->required_headcount) {
                    throw ValidationException::withMessages(['slot_id' => '必要人数を満たしているため募集できません。']);
                }
                $allowHelp = $request->boolean('allow_help');
                DB::transaction(function () use ($slot, $data, $allowHelp, $tenantId, $request) {
                    ShiftSlot::whereKey($slot->id)->lockForUpdate()->firstOrFail();
                    if (DB::table('shift_vacancies')->where('shift_slot_id', $slot->id)->where('status', 'open')->exists()) {
                        throw ValidationException::withMessages(['slot_id' => 'このシフトは募集済みです。']);
                    }
                    DB::table('shift_vacancies')->updateOrInsert(['shift_slot_id' => $slot->id], ['status' => 'open', 'message' => $data['message'], 'allow_help' => $allowHelp, 'created_at' => now(), 'updated_at' => now()]);
                    app(WorkforceMessaging::class)->create($tenantId, '欠員募集 '.$slot->starts_at->format('n/j H:i'), $data['message'], $allowHelp ? null : app(WorkforceRules::class)->storeId($slot), $request->user()->id);
                });
                $notice = '募集を公開し、対象スタッフのお知らせに追加しました。LINE配信は通知処理で行います。';
            }
        } elseif ($action === 'accept' || $action === 'close') {
            $request->validate(['vacancy_id' => ['required', 'integer']]);
            DB::transaction(function () use ($request, $tenantId, $action) {
                $vacancy = DB::table('shift_vacancies')->where('id', $request->vacancy_id)->lockForUpdate()->first();
                abort_unless($vacancy, 404);
                $slot = $this->slot($tenantId, $vacancy->shift_slot_id);
                ShiftSlot::whereKey($slot->id)->lockForUpdate()->firstOrFail();
                if ($action === 'close') {
                    DB::table('shift_vacancies')->where('id', $vacancy->id)->update(['status' => 'closed', 'updated_at' => now()]);

                    return;
                }
                abort_unless($vacancy->status === 'open' && $slot->starts_at->isFuture(), 422);
                $application = DB::table('vacancy_applications')->where('shift_vacancy_id', $vacancy->id)->where('id', $request->application_id)->where('status', 'pending')->first();
                abort_unless($application, 404);
                $member = Member::where('tenant_id', $tenantId)->where('status', 'active')->lockForUpdate()->findOrFail($application->member_id);
                abort_unless($vacancy->allow_help || (int) $member->store_id === app(WorkforceRules::class)->storeId($slot), 422);
                $assigned = $slot->assignments()->where('status', '!=', 'cancelled')->pluck('member_id');
                if ($assigned->contains($member->id) || $assigned->count() >= $slot->required_headcount) {
                    throw ValidationException::withMessages(['application_id' => '割当済み、または募集人数に達しています。']);
                }
                $warnings = app(WorkforceRules::class)->violations($member, $slot, $assigned->all());
                if ($warnings) {
                    throw ValidationException::withMessages(['application_id' => implode(' / ', $warnings)]);
                }
                $slot->assignments()->updateOrCreate(['member_id' => $member->id], ['status' => 'assigned']);
                DB::table('vacancy_applications')->where('id', $application->id)->update(['status' => 'accepted', 'updated_at' => now()]);
                if ($assigned->count() + 1 >= $slot->required_headcount) {
                    DB::table('shift_vacancies')->where('id', $vacancy->id)->update(['status' => 'closed', 'updated_at' => now()]);
                }
            });
        } elseif ($action === 'diagnose') {
            $schedule = ShiftSchedule::where('tenant_id', $tenantId)->findOrFail($request->schedule_id);

            return back()->with('diagnosis', app(WorkforcePlanning::class)->diagnose($schedule));
        } elseif ($action === 'message') {
            $data = $request->validate(['title' => ['required', 'string', 'max:150'], 'body' => ['required', 'string', 'max:4000'], 'store_id' => ['nullable', $storeRule]]);
            app(WorkforceMessaging::class)->create($tenantId, $data['title'], $data['body'], $data['store_id'] ?? null, $request->user()->id);
            $notice = 'お知らせを保存しました。LINE配信は通知処理で行います。';
        } elseif ($action === 'import') {
            $data = $request->validate(['kind' => ['required', Rule::in(array_keys(WorkforceExchange::HEADERS))], 'file' => ['required', 'file', 'max:5120']]);
            $notice = app(WorkforceExchange::class)->import($tenantId, $data['kind'], $request->file('file')->getRealPath()).'件を取り込みました。';
        } elseif ($action === 'recommend') {
            $schedule = ShiftSchedule::where('tenant_id', $tenantId)->where('status', 'draft')->whereNull('auto_scheduled_at')->findOrFail($request->schedule_id);
            $recommendations = collect(app(WorkforcePlanning::class)->recommend($tenantId, $schedule->store_id))->keyBy('weekday');
            if ($recommendations->isEmpty()) {
                throw ValidationException::withMessages(['schedule_id' => '推定できる過去の公開シフトがありません。']);
            }
            DB::transaction(function () use ($schedule, $recommendations) {
                foreach ($schedule->days()->where('is_day_off', false)->where('store_id', $schedule->store_id)->get() as $day) {
                    $r = $recommendations->get($day->scheduled_on->dayOfWeek);
                    if ($r && ! $day->starts_at && ! $day->ends_at) {
                        $day->update(['starts_at' => $r['starts_at'], 'ends_at' => $r['ends_at'], 'required_headcount' => min(50, $r['headcount'])]);
                    }
                }
            });
            $notice = '勤務時間が未設定の日に、過去6か月の曜日別推定値を設定しました。';
        }

        return back()->with('notice', $notice);
    }

    public function export(Request $request)
    {
        $tenantId = $this->tenant($request);
        $data = $request->validate(['kind' => ['required', Rule::in(['shifts', 'skills', 'policies', 'hr', 'attendance', 'pos'])], 'format' => ['required', Rule::in(['csv', 'xlsx'])]]);
        $kind = $data['kind'];
        if (isset(WorkforceExchange::HEADERS[$kind])) {
            $rows = [WorkforceExchange::HEADERS[$kind]];
            if ($kind === 'hr') {
                foreach (Member::where('tenant_id', $tenantId)->get() as $m) {
                    $rows[] = [$m->id, $m->name, $m->store_id, $m->status];
                }
            }
        } elseif ($kind === 'shifts') {
            $request->validate(['schedule_id' => ['required', 'integer']]);
            $schedule = ShiftSchedule::where('tenant_id', $tenantId)->with('shiftSlots.assignments.member')->findOrFail($request->schedule_id);
            $rows = [['member_id', 'name', 'store', 'starts_at', 'ends_at', 'break_minutes']];
            foreach ($schedule->shiftSlots as $slot) {
                foreach ($slot->assignments->where('status', '!=', 'cancelled') as $a) {
                    if ($a->member) {
                        $rows[] = [$a->member_id, $a->member->displayName(), $slot->notes, $slot->starts_at->format('Y-m-d H:i'), $slot->ends_at->format('Y-m-d H:i'), $slot->break_minutes];
                    }
                }
            }
        } elseif ($kind === 'skills') {
            $rows = [['member_id', 'skill', 'level', 'expires_on']];
            foreach (DB::table('member_skills')->whereIn('member_id', Member::where('tenant_id', $tenantId)->select('id'))->get() as $r) {
                $rows[] = [$r->member_id, $r->skill, $r->level, $r->expires_on];
            }
        } else {
            $rows = [['store_id', 'settings']];
            foreach (DB::table('workforce_policies')->whereIn('store_id', Store::where('tenant_id', $tenantId)->select('id'))->get() as $r) {
                $rows[] = [$r->store_id, $r->settings];
            }
        }
        if ($data['format'] === 'xlsx') {
            return response(app(WorkforceExchange::class)->xlsx($rows), 200, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Content-Disposition' => 'attachment; filename="'.$kind.'.xlsx"']);
        }

        return response()->streamDownload(function () use ($rows) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($stream, array_map(fn ($v) => preg_match('/^[\s]*[=+@-]/u', (string) $v) ? "'".$v : $v, $row), ',', '"', '');
            }
            fclose($stream);
        }, $kind.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
