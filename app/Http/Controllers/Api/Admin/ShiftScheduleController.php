<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShiftScheduleStoreRequest;
use App\Models\ShiftSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $schedules = ShiftSchedule::query()
            ->with(['store', 'shiftSlots.assignments.member'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->when($request->query('store_id'), fn ($query, $storeId) => $query->where('store_id', $storeId))
            ->latest('starts_on')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($schedules);
    }

    public function store(ShiftScheduleStoreRequest $request): JsonResponse
    {
        $schedule = ShiftSchedule::create([
            ...$request->validated(),
            'tenant_id' => $request->user()->tenant_id,
            'created_by' => $request->user()->id,
            'status' => $request->validated('status', 'draft'),
        ]);

        return response()->json(['shift_schedule' => $schedule->load('store')], 201);
    }

    public function publish(Request $request, ShiftSchedule $shiftSchedule): JsonResponse
    {
        abort_unless($shiftSchedule->tenant_id === $request->user()->tenant_id, 404);

        $shiftSchedule->update([
            'status' => 'published',
            'published_by' => $request->user()->id,
            'published_at' => now(),
        ]);

        return response()->json(['shift_schedule' => $shiftSchedule->refresh()]);
    }
}
