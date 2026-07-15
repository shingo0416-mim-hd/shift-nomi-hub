<?php

namespace App\Http\Controllers\Api\Liff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Liff\AvailabilityRequest;
use App\Models\AvailabilityRequest as Availability;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $availability = Availability::updateOrCreate(
            [
                'member_id' => $member->id,
                'work_date' => $request->validated('work_date'),
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
