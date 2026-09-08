<?php

namespace Tests\Feature;

use App\Models\AvailabilityRequest;
use App\Models\Member;
use App\Models\ShiftSchedule;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ShiftScheduleOperations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Fortify;
use Tests\TestCase;

class ShiftScheduleOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_submission_and_staffing_progress_for_a_monthly_schedule(): void
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_ADMIN]);
        $store = Store::create(['tenant_id' => $tenant->id, 'name' => '本店']);
        $submittedMember = Member::create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => '提出済み',
            'status' => 'active',
            'is_shift_submitter' => true,
        ]);
        Member::create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'name' => '未提出',
            'status' => 'active',
            'is_shift_submitter' => true,
        ]);
        $schedule = ShiftSchedule::create([
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'starts_on' => '2026-10-01',
            'ends_on' => '2026-10-02',
            'submission_deadline_at' => '2026-09-25 18:00:00',
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);
        $schedule->days()->createMany([
            ['store_id' => $store->id, 'scheduled_on' => '2026-10-01', 'starts_at' => '18:00', 'ends_at' => '23:00', 'required_headcount' => 2],
            ['store_id' => $store->id, 'scheduled_on' => '2026-10-02', 'starts_at' => '18:00', 'ends_at' => '23:00', 'required_headcount' => 1],
        ]);
        foreach (['2026-10-01', '2026-10-02'] as $workDate) {
            AvailabilityRequest::create([
                'tenant_id' => $tenant->id,
                'member_id' => $submittedMember->id,
                'work_date' => $workDate,
                'preference' => 'available',
            ]);
        }
        $slot = $schedule->shiftSlots()->create([
            'title' => 'ディナー',
            'starts_at' => '2026-10-01 18:00:00',
            'ends_at' => '2026-10-01 23:00:00',
            'required_headcount' => 2,
        ]);
        $slot->assignments()->create(['member_id' => $submittedMember->id, 'status' => 'assigned']);

        $schedule->load(['days', 'shiftSlots.assignments.member']);
        app(ShiftScheduleOperations::class)->enrich(collect([$schedule]));

        $this->assertSame(2, $schedule->operations['eligible_members']);
        $this->assertSame(1, $schedule->operations['completed_members']);
        $this->assertSame(1, $schedule->operations['unsubmitted_members']);
        $this->assertSame(50, $schedule->operations['submission_percent']);
        $this->assertSame(3, $schedule->operations['required_headcount']);
        $this->assertSame(1, $schedule->operations['assigned_headcount']);
        $this->assertSame(2, $schedule->operations['shortage_headcount']);
        $this->assertSame('reviewing', $schedule->operations['stage']);

        $admin->forceFill([
            'two_factor_secret' => Fortify::currentEncrypter()->encrypt('secret'),
            'two_factor_confirmed_at' => now(),
        ])->save();
        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('今月のシフト運用')
            ->assertSee('運用フロー');
    }
}
