<?php

namespace App\Http\Controllers\Api\Liff;

use App\Http\Controllers\Controller;
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
            ->where('status', 'published')
            ->when($request->query('store_id'), fn ($query, $storeId) => $query->where('store_id', $storeId))
            ->latest('starts_on')
            ->paginate((int) $request->query('per_page', 10));

        return response()->json($schedules);
    }
}
