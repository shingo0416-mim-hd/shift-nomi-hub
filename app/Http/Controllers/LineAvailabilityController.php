<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\ShiftSchedule;
use App\Services\AvailabilitySubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LineAvailabilityController extends Controller
{
    private function member(Request $request): Member
    {
        return Member::query()
            ->where('tenant_id', $request->attributes->get('tenant')->id)
            ->where('line_id', $request->session()->get('line_id'))
            ->where('status', 'active')
            ->where('is_shift_submitter', true)
            ->findOrFail($request->session()->get('line_member_id'));
    }

    public function index(Request $request): View
    {
        $member = $this->member($request);
        $schedules = ShiftSchedule::query()->with('days.store')
            ->where('tenant_id', $member->tenant_id)
            ->whereIn('status', ['draft', 'published'])
            ->where(fn ($query) => $query->where('store_id', $member->store_id)
                ->orWhereHas('days', fn ($days) => $days->where('store_id', $member->store_id)))
            ->orderByDesc('starts_on')->get();
        $availability = $member->availabilityRequests()->get()->keyBy(fn ($item) => $item->work_date->toDateString());

        return view('line.availability', compact('member', 'schedules', 'availability'));
    }

    public function store(Request $request, string $tenant, ShiftSchedule $shiftSchedule): RedirectResponse
    {
        $member = $this->member($request);
        abort_unless((int) $shiftSchedule->tenant_id === (int) $member->tenant_id, 404);
        abort_unless((int) $shiftSchedule->store_id === (int) $member->store_id
            || $shiftSchedule->days()->where('store_id', $member->store_id)->exists(), 404);
        $data = $request->validate([
            'work_date' => ['required', 'date_format:Y-m-d', Rule::exists('shift_schedule_days', 'scheduled_on')->where('shift_schedule_id', $shiftSchedule->id)],
            'preference' => ['required', Rule::in(['available', 'preferred', 'unavailable'])],
            'available_from' => ['exclude_if:preference,unavailable', 'nullable', 'required_unless:preference,unavailable', 'date_format:H:i'],
            'available_until' => ['exclude_if:preference,unavailable', 'nullable', 'required_unless:preference,unavailable', 'date_format:H:i', 'after:available_from'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        abort_unless($shiftSchedule->status === 'draft', 403, '確定済みのシフトは変更できません。');
        app(AvailabilitySubmission::class)->save($member, $data);

        return redirect()->route('line.availability', ['tenant' => $tenant])
            ->with('notice', $data['work_date'] . ' の希望を保存しました。すべての日付を入力すると提出完了になります。');
    }
}
