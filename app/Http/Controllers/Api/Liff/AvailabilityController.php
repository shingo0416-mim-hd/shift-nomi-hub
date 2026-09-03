<?php

namespace App\Http\Controllers\Api\Liff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Liff\AvailabilityRequest;
use App\Models\AvailabilityRequest as Availability;
use App\Models\Member;
use App\Models\ShiftSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AvailabilityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $member = $this->member($request);

        $availability = Availability::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('member_id', $member->id)
            ->when($request->query('from'), fn ($query, $from) => $query->whereDate('work_date', '>=', $from))
            ->when($request->query('to'), fn ($query, $to) => $query->whereDate('work_date', '<=', $to))
            ->orderBy('work_date')
            ->get();

        return response()->json(['availability_requests' => $availability]);
    }

    public function store(AvailabilityRequest $request): JsonResponse
    {
        $member = $this->member($request);
        $workDate = $request->validated('work_date');

        $submissionClosed = ShiftSchedule::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('auto_schedule_enabled', true)
            ->whereNotNull('submission_deadline_at')
            ->where('submission_deadline_at', '<=', now())
            ->whereDate('starts_on', '<=', $workDate)
            ->whereDate('ends_on', '>=', $workDate)
            ->where(fn ($query) => $query
                ->where('store_id', $member->store_id)
                ->orWhereHas('days', fn ($dayQuery) => $dayQuery
                    ->whereDate('scheduled_on', $workDate)
                    ->where('store_id', $member->store_id)))
            ->exists();

        if ($submissionClosed) {
            throw ValidationException::withMessages([
                'work_date' => ['この月のシフト提出期限を過ぎています。管理者へ連絡してください。'],
            ]);
        }

        $availability = Availability::updateOrCreate(
            [
                'member_id' => $member->id,
                'work_date' => $workDate,
            ],
            [
                'tenant_id' => $request->user()->tenant_id,
                'available_from' => $request->validated('available_from'),
                'available_until' => $request->validated('available_until'),
                'preference' => $request->validated('preference'),
                'notes' => $request->validated('notes'),
            ],
        );

        return response()->json(['availability_request' => $availability]);
    }

    private function member(Request $request): Member
    {
        return Member::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
