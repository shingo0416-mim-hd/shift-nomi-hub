<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\ShiftSchedule;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ShiftScheduleOperations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Fortify;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AvailabilityCollectionTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $this->travelTo(now()->setDate(2026, 9, 28)->setTime(12, 0));
        $tenant = Tenant::factory()->create(['data' => ['path' => 'collection-test']]);
        $store = Store::create(['tenant_id' => $tenant->id, 'name' => '本店']);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_MEMBER]);
        $member = Member::create(['tenant_id' => $tenant->id, 'store_id' => $store->id, 'user_id' => $user->id, 'name' => '提出スタッフ', 'line_id' => 'line-collection', 'status' => 'active', 'is_shift_submitter' => true]);
        $schedule = ShiftSchedule::create(['tenant_id' => $tenant->id, 'store_id' => $store->id, 'created_by' => $admin->id, 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-02', 'submission_deadline_at' => '2026-09-30 18:00:00', 'auto_schedule_enabled' => false, 'status' => 'draft']);
        foreach (['2026-10-01', '2026-10-02'] as $date) {
            $schedule->days()->create(['store_id' => $store->id, 'scheduled_on' => $date]);
        }
        $this->withSession(['line_id' => $member->line_id, 'line_member_id' => $member->id]);

        return [$member, $schedule, $admin];
    }

    private function submit(ShiftSchedule $schedule, array $data = [])
    {
        return $this->post('/collection-test/line/availability/' . $schedule->id, array_replace([
            'work_date' => '2026-10-01', 'preference' => 'available', 'available_from' => '09:00', 'available_until' => '18:00', 'notes' => '夕方まで勤務できます',
        ], $data));
    }

    public function test_staff_can_submit_edit_and_complete_then_admin_can_read_details(): void
    {
        [$member, $schedule, $admin] = $this->fixture();
        $this->get('/collection-test/line/availability')->assertOk()->assertSee('未提出');
        $this->submit($schedule)->assertSessionHasNoErrors()->assertRedirect('/collection-test/line/availability');
        $this->get('/collection-test/line/availability')->assertSee('一部入力')->assertSee('夕方まで勤務できます');
        $this->submit($schedule, ['available_until' => '17:00'])->assertSessionHasNoErrors()->assertRedirect('/collection-test/line/availability');
        $this->assertSame(1, $member->availabilityRequests()->count());
        $this->assertStringStartsWith('17:00', $member->availabilityRequests()->first()->available_until);
        $this->submit($schedule, ['work_date' => '2026-10-02', 'preference' => 'unavailable', 'notes' => '休み希望です'])->assertSessionHasNoErrors();
        $this->assertNull($member->availabilityRequests()->orderByDesc('work_date')->first()->available_from);
        $this->get('/collection-test/line/availability')->assertSee('提出完了')->assertSee('休み希望です');
        $admin->forceFill(['two_factor_secret' => Fortify::currentEncrypter()->encrypt('secret'), 'two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin)->get('/dashboard/schedules/' . $schedule->id . '/edit')->assertOk()->assertSee('スタッフ別の提出状況')->assertSee('提出スタッフ')->assertSee('休み希望です')->assertSee('提出率 100%');
    }

    public function test_deadline_is_enforced_without_auto_scheduling_on_web_and_api(): void
    {
        [$member, $schedule] = $this->fixture();
        $this->submit($schedule)->assertSessionHasNoErrors();
        $this->travelTo($schedule->submission_deadline_at);
        $this->submit($schedule, ['preference' => 'unavailable'])->assertSessionHasErrors('work_date');
        $this->get('/collection-test/line/availability')->assertSee('受付終了');
        Sanctum::actingAs($member->user, ['liff']);
        $this->postJson('/api/liff/availability-requests', ['work_date' => '2026-10-01', 'preference' => 'unavailable'])->assertUnprocessable();
        $this->assertSame('available', $member->availabilityRequests()->first()->preference);
    }

    public function test_invalid_time_and_dates_are_rejected_without_writing(): void
    {
        [$member, $schedule] = $this->fixture();
        $this->submit($schedule, ['available_until' => '08:00'])->assertSessionHasErrors('available_until');
        $this->submit($schedule, ['available_from' => null])->assertSessionHasErrors('available_from');
        $this->submit($schedule, ['work_date' => '2026-10-03'])->assertSessionHasErrors('work_date');
        $this->assertSame(0, $member->availabilityRequests()->count());
    }

    public function test_other_tenant_store_and_mismatched_line_session_are_denied(): void
    {
        [$member, $schedule] = $this->fixture();
        $other = Tenant::factory()->create(['data' => ['path' => 'other']]);
        $this->get('/other/line/availability')->assertNotFound();
        $store = Store::create(['tenant_id' => $member->tenant_id, 'name' => '別店舗']);
        $member->update(['store_id' => $store->id]);
        $this->submit($schedule)->assertNotFound();
        $this->withSession(['line_id' => 'wrong-line'])->get('/collection-test/line/availability')->assertNotFound();
        $this->assertSame(0, $member->availabilityRequests()->count());
    }

    public function test_published_schedule_cannot_be_changed(): void
    {
        [$member, $schedule] = $this->fixture();
        $schedule->update(['status' => 'published']);
        $this->submit($schedule)->assertForbidden();
        Sanctum::actingAs($member->user, ['liff']);
        $this->postJson('/api/liff/availability-requests', ['work_date' => '2026-10-01', 'preference' => 'unavailable'])->assertUnprocessable();
    }

    public function test_admin_can_set_and_change_collection_period_and_deadline_without_auto_scheduling(): void
    {
        [$member, $schedule, $admin] = $this->fixture();
        Sanctum::actingAs($admin, ['admin']);
        $payload = ['store_id' => $member->store_id, 'starts_on' => '2026-11-01', 'ends_on' => '2026-11-03', 'submission_deadline_at' => '2026-10-30 18:00:00', 'auto_schedule_enabled' => false];
        $response = $this->postJson('/api/admin/shift-schedules', $payload)->assertCreated()->assertJsonCount(3, 'shift_schedule.days');
        $id = $response->json('shift_schedule.id');
        $this->putJson('/api/admin/shift-schedules/' . $id, array_replace($payload, ['ends_on' => '2026-11-02', 'submission_deadline_at' => '2026-10-29 18:00:00']))->assertOk()->assertJsonCount(2, 'shift_schedule.days');
        $this->assertSame('2026-10-29 18:00', ShiftSchedule::findOrFail($id)->submission_deadline_at->format('Y-m-d H:i'));
    }

    public function test_inactive_staff_cannot_submit_on_web_or_api(): void
    {
        [$member, $schedule] = $this->fixture();
        $member->update(['is_shift_submitter' => false]);
        $this->submit($schedule)->assertNotFound();
        Sanctum::actingAs($member->user, ['liff']);
        $this->postJson('/api/liff/availability-requests', ['work_date' => '2026-10-01', 'preference' => 'unavailable'])->assertNotFound();
    }

    public function test_progress_ignores_dates_outside_the_schedule_days(): void
    {
        [$member, $schedule] = $this->fixture();
        $schedule->days()->whereDate('scheduled_on', '2026-10-02')->delete();
        $member->availabilityRequests()->create(['tenant_id' => $member->tenant_id, 'work_date' => '2026-10-02', 'preference' => 'unavailable']);
        $schedule->load(['days', 'shiftSlots.assignments.member']);
        app(ShiftScheduleOperations::class)->enrich(collect([$schedule]));
        $this->assertSame(0, $schedule->operations['completed_members']);
        $this->assertSame('未提出', $schedule->submission_members[0]['status']);
    }
}
