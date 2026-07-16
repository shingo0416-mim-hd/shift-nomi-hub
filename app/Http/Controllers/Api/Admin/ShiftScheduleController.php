<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShiftScheduleStoreRequest;
use App\Models\ShiftSchedule;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ShiftScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $schedules = ShiftSchedule::query()
            ->with(['store', 'days.store', 'shiftSlots.assignments.member'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->when($request->query('store_id'), fn ($query, $storeId) => $query->where(fn ($storeQuery) => $storeQuery
                ->where('store_id', $storeId)
                ->orWhereHas('days', fn ($dayQuery) => $dayQuery->where('store_id', $storeId))))
            ->latest('starts_on')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json($schedules);
    }

    public function store(ShiftScheduleStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $schedule = DB::transaction(function () use ($request, $validated): ShiftSchedule {
            $schedule = ShiftSchedule::create([
                ...Arr::except($validated, ['days']),
                'tenant_id' => $request->user()->tenant_id,
                'created_by' => $request->user()->id,
                'status' => $request->validated('status', 'draft'),
            ]);

            $days = ! empty($validated['days'])
                ? $this->normalizeDays($validated['days'], (int) $validated['store_id'])
                : $this->defaultDays(
                    $validated['starts_on'],
                    $validated['ends_on'],
                    (int) $validated['store_id'],
                );

            $schedule->days()->createMany($days);

            return $schedule;
        });

        return response()->json(['shift_schedule' => $schedule->load(['store', 'days.store'])], 201);
    }

    public function update(ShiftScheduleStoreRequest $request, ShiftSchedule $shiftSchedule): JsonResponse
    {
        abort_unless($shiftSchedule->tenant_id === $request->user()->tenant_id, 404);

        $validated = $request->validated();

        $schedule = DB::transaction(function () use ($shiftSchedule, $validated): ShiftSchedule {
            $shiftSchedule->update(Arr::except($validated, ['days']));

            $days = ! empty($validated['days'])
                ? $this->normalizeDays($validated['days'], (int) $validated['store_id'])
                : $this->defaultDays(
                    $validated['starts_on'],
                    $validated['ends_on'],
                    (int) $validated['store_id'],
                );

            $shiftSchedule->days()->delete();
            $shiftSchedule->days()->createMany($days);

            return $shiftSchedule;
        });

        return response()->json(['shift_schedule' => $schedule->refresh()->load(['store', 'days.store'])]);
    }

    public function updateTenant(ShiftScheduleStoreRequest $request, string $tenant, ShiftSchedule $shiftSchedule): JsonResponse
    {
        return $this->update($request, $shiftSchedule);
    }

    public function publish(Request $request, ShiftSchedule $shiftSchedule): JsonResponse
    {
        abort_unless($shiftSchedule->tenant_id === $request->user()->tenant_id, 404);

        $shiftSchedule->update([
            'status' => 'published',
            'published_by' => $request->user()->id,
            'published_at' => now(),
        ]);

        return response()->json(['shift_schedule' => $shiftSchedule->refresh()->load(['store', 'days.store'])]);
    }

    public function publishTenant(Request $request, string $tenant, ShiftSchedule $shiftSchedule): JsonResponse
    {
        return $this->publish($request, $shiftSchedule);
    }

    /**
     * @param  array<int, array<string, mixed>>  $days
     * @return array<int, array{scheduled_on: string, store_id: int, is_day_off: bool, starts_at: string|null, ends_at: string|null}>
     */
    private function normalizeDays(array $days, int $defaultStoreId): array
    {
        return collect($days)->map(function (array $day) use ($defaultStoreId): array {
            $isDayOff = filter_var($day['is_day_off'] ?? false, FILTER_VALIDATE_BOOLEAN);

            return [
                'scheduled_on' => $day['scheduled_on'],
                'store_id' => $isDayOff ? (int) ($day['store_id'] ?? $defaultStoreId) : (int) $day['store_id'],
                'is_day_off' => $isDayOff,
                'starts_at' => $isDayOff ? null : ($day['starts_at'] ?? null),
                'ends_at' => $isDayOff ? null : ($day['ends_at'] ?? null),
            ];
        })->all();
    }

    /**
     * @return array<int, array{scheduled_on: string, store_id: int, is_day_off: bool, starts_at: null, ends_at: null}>
     */
    private function defaultDays(string $startsOn, string $endsOn, int $storeId): array
    {
        return collect(CarbonPeriod::create(
            CarbonImmutable::parse($startsOn),
            CarbonImmutable::parse($endsOn),
        ))->map(fn ($date) => [
            'scheduled_on' => $date->toDateString(),
            'store_id' => $storeId,
            'is_day_off' => false,
            'starts_at' => null,
            'ends_at' => null,
        ])->all();
    }
}
