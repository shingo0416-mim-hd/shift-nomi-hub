<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\ShiftSlot;
use App\Services\WorkforceMessaging;
use App\Services\WorkforceRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class LineWorkforceController extends Controller
{
    private function member(Request $request): Member
    {
        abort_unless(Schema::hasTable('workforce_messages'), 503, '運用機能は準備中です。');

        return Member::where('tenant_id', $request->attributes->get('tenant')->id)->where('line_id', $request->session()->get('line_id'))->where('status', 'active')->findOrFail($request->session()->get('line_member_id'));
    }

    public function index(Request $request)
    {
        $member = $this->member($request);
        $slots = ShiftSlot::whereHas('shiftSchedule', fn ($q) => $q->where('tenant_id', $member->tenant_id)->where('status', 'published'))->whereHas('assignments', fn ($q) => $q->where('member_id', $member->id)->where('status', '!=', 'cancelled'))->where('ends_at', '>=', now()->subDays(7))->with(['assignments' => fn ($q) => $q->where('member_id', $member->id)])->orderBy('starts_at')->limit(100)->get();
        $tasks = DB::table('work_task_assignments as a')->join('work_tasks as t', 't.id', '=', 'a.work_task_id')->where('a.member_id', $member->id)->whereIn('t.shift_slot_id', $slots->pluck('id'))->get(['t.*']);
        $messages = DB::table('workforce_messages as m')->join('workforce_message_recipients as r', 'r.workforce_message_id', '=', 'm.id')->where('m.tenant_id', $member->tenant_id)->where('r.member_id', $member->id)->latest('m.id')->limit(50)->get(['m.*', 'r.read_at']);
        $vacancies = DB::table('shift_vacancies as v')->join('shift_slots as s', 's.id', '=', 'v.shift_slot_id')->join('shift_schedules as c', 'c.id', '=', 's.shift_schedule_id')->where('c.tenant_id', $member->tenant_id)->where('c.status', 'published')->where('v.status', 'open')->where('s.starts_at', '>', now())->get(['v.*', 's.starts_at', 's.ends_at', 's.notes'])->filter(function ($v) use ($member) {
            return $v->allow_help || app(WorkforceRules::class)->storeId(ShiftSlot::find($v->shift_slot_id)) === (int) $member->store_id;
        });
        $applications = DB::table('vacancy_applications')->where('member_id', $member->id)->get()->keyBy('shift_vacancy_id');

        return view('line.workforce', compact('member', 'slots', 'tasks', 'messages', 'vacancies', 'applications'));
    }

    public function update(Request $request)
    {
        $member = $this->member($request);
        $action = $request->validate(['action' => ['required', Rule::in(['apply', 'read', 'message'])]])['action'];
        if ($action === 'read') {
            DB::table('workforce_message_recipients')->where('member_id', $member->id)->where('workforce_message_id', $request->message_id)->update(['read_at' => now()]);
        } elseif ($action === 'message') {
            $data = $request->validate(['title' => ['required', 'string', 'max:150'], 'body' => ['required', 'string', 'max:4000']]);
            app(WorkforceMessaging::class)->create($member->tenant_id, $data['title'], $data['body'], $member->store_id, null, $member->id);
        } else {
            DB::transaction(function () use ($request, $member) {
                $vacancy = DB::table('shift_vacancies')->where('id', $request->vacancy_id)->where('status', 'open')->lockForUpdate()->first();
                abort_unless($vacancy && $member->is_shift_submitter, 404);
                $slot = ShiftSlot::whereHas('shiftSchedule', fn ($q) => $q->where('tenant_id', $member->tenant_id)->where('status', 'published'))->where('starts_at', '>', now())->findOrFail($vacancy->shift_slot_id);
                abort_unless($vacancy->allow_help || app(WorkforceRules::class)->storeId($slot) === (int) $member->store_id, 404);
                $already = $slot->assignments()->where('member_id', $member->id)->where('status', '!=', 'cancelled')->exists();
                abort_if($already, 422, 'このシフトは割当済みです。');
                DB::table('vacancy_applications')->insertOrIgnore(['shift_vacancy_id' => $vacancy->id, 'member_id' => $member->id, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
            });
        }

        return back()->with('notice', '受付しました。');
    }
}
