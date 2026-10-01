<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\ShiftSchedule;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ShiftAnalytics;
use App\Services\ShiftAutoScheduler;
use App\Services\WorkforceExchange;
use App\Services\WorkforceMessaging;
use App\Services\WorkforcePlanning;
use App\Services\WorkforceRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Tests\TestCase;

class WorkforceOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function setupWorkforce(): array
    {
        $this->travelTo(now()->setDate(2026, 9, 29)->setTime(12, 0));
        $tenant = Tenant::factory()->create(['data' => ['path' => 'workforce-test']]);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_ADMIN]);
        $admin->forceFill(['two_factor_secret' => Fortify::currentEncrypter()->encrypt('secret'), 'two_factor_confirmed_at' => now()])->save();
        $store = Store::create(['tenant_id' => $tenant->id, 'name' => '本店']);
        $member = Member::create(['tenant_id' => $tenant->id, 'store_id' => $store->id, 'name' => '担当スタッフ', 'status' => 'active', 'line_id' => 'line-workforce', 'is_shift_submitter' => true]);
        $schedule = ShiftSchedule::create(['tenant_id' => $tenant->id, 'store_id' => $store->id, 'created_by' => $admin->id, 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-01', 'status' => 'published']);
        $schedule->days()->create(['store_id' => $store->id, 'scheduled_on' => '2026-10-01', 'starts_at' => '09:00', 'ends_at' => '18:00', 'required_headcount' => 1]);
        $slot = $schedule->shiftSlots()->create(['starts_at' => '2026-10-01 09:00', 'ends_at' => '2026-10-01 18:00', 'required_headcount' => 1, 'title' => '日勤']);
        $this->actingAs($admin);

        return [$tenant, $store, $member, $schedule, $slot];
    }

    private function policy(Store $store, array $data): void
    {
        DB::table('workforce_policies')->updateOrInsert(['store_id' => $store->id], ['settings' => json_encode($data), 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_admin_can_configure_skills_rules_and_render_operations(): void
    {
        [$tenant, $store, $member] = $this->setupWorkforce();
        $this->post('/dashboard/workforce', ['action' => 'policy', 'store_id' => $store->id, 'max_daily_hours' => 10, 'break_minutes' => 60, 'break_after_hours' => 6])->assertRedirect()->assertSessionHasNoErrors();
        $this->post('/dashboard/workforce', ['action' => 'skill', 'member_id' => $member->id, 'skill' => 'レジ', 'level' => 3, 'expires_on' => '2026-12-31'])->assertRedirect()->assertSessionHasNoErrors();
        $this->get('/dashboard/workforce')->assertOk()->assertSee('スキル・資格登録')->assertSee('担当スタッフ')->assertSee('レジ');
        $this->assertSame(10, app(WorkforceRules::class)->policy($store->id)['max_daily_hours']);
    }

    public function test_operations_are_tenant_scoped_and_require_admin(): void
    {
        [$tenant, $store, $member] = $this->setupWorkforce();
        $otherTenant = Tenant::factory()->create();
        $otherStore = Store::create(['tenant_id' => $otherTenant->id, 'name' => '他社']);
        $otherMember = Member::create(['tenant_id' => $otherTenant->id, 'name' => '他社スタッフ']);
        $this->post('/dashboard/workforce', ['action' => 'policy', 'store_id' => $otherStore->id])->assertSessionHasErrors('store_id');
        $this->post('/dashboard/workforce', ['action' => 'skill', 'member_id' => $otherMember->id, 'skill' => 'レジ', 'level' => 3])->assertSessionHasErrors('member_id');
        $this->get('/dashboard/workforce?store_id='.$otherStore->id)->assertNotFound();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_MEMBER]);
        $this->actingAs($user)->get('/dashboard/workforce')->assertForbidden();
    }

    public function test_task_assignment_requires_valid_skill_and_respects_breaks(): void
    {
        [$tenant, $store, $member, $schedule, $slot] = $this->setupWorkforce();
        $slot->assignments()->create(['member_id' => $member->id, 'status' => 'assigned']);
        $this->post('/dashboard/workforce', ['action' => 'task', 'slot_id' => $slot->id, 'name' => 'レジ担当', 'skill' => 'レジ', 'minimum_level' => 2, 'headcount' => 1, 'starts_at' => '2026-10-01 10:00', 'ends_at' => '2026-10-01 11:00'])->assertRedirect()->assertSessionHasNoErrors();
        $service = app(WorkforcePlanning::class);
        $this->assertNotEmpty($service->allocateTasks($slot));
        DB::table('member_skills')->insert(['member_id' => $member->id, 'skill' => 'レジ', 'level' => 3, 'expires_on' => '2026-09-30']);
        $this->assertNotEmpty($service->allocateTasks($slot));
        DB::table('member_skills')->where('member_id', $member->id)->update(['expires_on' => '2026-12-31']);
        $this->assertSame([], $service->allocateTasks($slot));
        $this->assertDatabaseCount('work_task_assignments', 1);
        DB::table('shift_assignments')->where('shift_slot_id', $slot->id)->update(['break_starts_at' => '2026-10-01 10:30', 'break_ends_at' => '2026-10-01 11:30']);
        $this->assertNotEmpty($service->allocateTasks($slot));
        $this->assertDatabaseCount('work_task_assignments', 0);
    }

    public function test_breaks_avoid_peak_and_rules_explain_violations(): void
    {
        [$tenant, $store, $member, $schedule, $slot] = $this->setupWorkforce();
        $this->policy($store, ['max_daily_hours' => 7, 'break_minutes' => 60, 'break_after_hours' => 6, 'peak_start' => '12:00', 'peak_end' => '14:00']);
        $slot->assignments()->create(['member_id' => $member->id, 'status' => 'assigned']);
        $rules = app(WorkforceRules::class);
        $this->assertContains('1日の勤務時間が設定上限を超えます', $rules->violations($member, $slot));
        $this->assertSame([], $rules->applyBreaks($slot));
        $assignment = $slot->assignments()->first();
        $this->assertTrue($assignment->break_ends_at <= '2026-10-01 12:00:00' || $assignment->break_starts_at >= '2026-10-01 14:00:00');
        $this->assertSame(60, $slot->refresh()->break_minutes);
    }

    public function test_vacancy_can_be_applied_to_and_accepted_once(): void
    {
        [$tenant, $store, $member, $schedule, $slot] = $this->setupWorkforce();
        $this->post('/dashboard/workforce', ['action' => 'vacancy', 'slot_id' => $slot->id, 'message' => '日勤募集', 'allow_help' => false])->assertRedirect()->assertSessionHasNoErrors();
        $vacancy = DB::table('shift_vacancies')->first();
        $this->withSession(['line_id' => $member->line_id, 'line_member_id' => $member->id])->get('/workforce-test/line/workforce')->assertOk()->assertSee('日勤募集');
        $this->post('/workforce-test/line/workforce', ['action' => 'apply', 'vacancy_id' => $vacancy->id])->assertRedirect();
        $this->post('/workforce-test/line/workforce', ['action' => 'apply', 'vacancy_id' => $vacancy->id])->assertRedirect();
        $this->assertDatabaseCount('vacancy_applications', 1);
        $application = DB::table('vacancy_applications')->first();
        $this->post('/dashboard/workforce', ['action' => 'accept', 'vacancy_id' => $vacancy->id, 'application_id' => $application->id])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1, $slot->assignments()->count());
        $this->assertDatabaseHas('shift_vacancies', ['id' => $vacancy->id, 'status' => 'closed']);
        $this->get('/workforce-test/line/workforce')->assertOk()->assertSee('10/1 09:00');
    }

    public function test_messages_have_scoped_recipients_read_receipts_and_no_duplicate_delivery(): void
    {
        [$tenant, $store, $member] = $this->setupWorkforce();
        $tenant->lineOfficialAccount()->create(['is_active' => true, 'channel_access_token' => 'test-token']);
        Http::fake(['*' => Http::response([], 200)]);
        $id = app(WorkforceMessaging::class)->create($tenant->id, 'お知らせ', '本文', $store->id);
        $this->assertSame(1, app(WorkforceMessaging::class)->deliver());
        $this->assertSame(0, app(WorkforceMessaging::class)->deliver());
        Http::assertSentCount(1);
        $this->withSession(['line_id' => $member->line_id, 'line_member_id' => $member->id])->post('/workforce-test/line/workforce', ['action' => 'read', 'message_id' => $id])->assertRedirect();
        $this->assertNotNull(DB::table('workforce_message_recipients')->first()->read_at);
    }

    private function importRows(int $tenantId, string $kind, array $rows): int
    {
        $path = tempnam(sys_get_temp_dir(), 'workforce-test-');
        try {
            $stream = fopen($path, 'w');
            foreach ([WorkforceExchange::HEADERS[$kind], ...$rows] as $row) {
                fputcsv($stream, $row, ',', '"', '');
            }
            fclose($stream);

            return app(WorkforceExchange::class)->import($tenantId, $kind, $path);
        } finally {
            unlink($path);
        }
    }

    public function test_import_is_atomic_tenant_scoped_and_idempotent(): void
    {
        [$tenant, $store, $member] = $this->setupWorkforce();
        $row = ['external-1', $member->id, '2026-09-28 09:00', '2026-09-28 18:00', 60];
        $this->assertSame(1, $this->importRows($tenant->id, 'attendance', [$row]));
        $this->importRows($tenant->id, 'attendance', [$row]);
        $this->assertDatabaseCount('attendance_records', 1);
        try {
            $this->importRows($tenant->id, 'hr', [[$member->id, '変更', $store->id, 'active'], [99999, '不正', $store->id, 'active']]);
            $this->fail('Invalid row should abort the complete import');
        } catch (ValidationException) {
            $this->assertSame('担当スタッフ', $member->refresh()->name);
        }
    }

    public function test_pos_forecast_and_xlsx_export_use_real_data(): void
    {
        [$tenant, $store, $member, $schedule, $slot] = $this->setupWorkforce();
        $this->policy($store, ['customers_per_register' => 10]);
        $this->importRows($tenant->id, 'pos', [[$store->id, '2026-09-24 09:00', 21, 10000], [$store->id, '2026-09-17 09:00', 31, 15000]]);
        $forecast = app(WorkforcePlanning::class)->forecast($store->id, '2026-10-01');
        $this->assertSame(26.0, $forecast[0]['customers']);
        $this->assertSame(3, $forecast[0]['registers']);
        $slot->assignments()->create(['member_id' => $member->id, 'status' => 'assigned']);
        $response = $this->get('/dashboard/workforce/export?kind=shifts&format=xlsx&schedule_id='.$schedule->id)->assertOk();
        $path = tempnam(sys_get_temp_dir(), 'xlsx-test-');
        try {
            file_put_contents($path, $response->getContent());
            $zip = new \ZipArchive;
            $this->assertTrue($zip->open($path));
            $this->assertStringContainsString('担当スタッフ', $zip->getFromName('xl/worksheets/sheet1.xml'));
            $zip->close();
        } finally {
            unlink($path);
        }
    }

    public function test_auto_scheduler_uses_opted_in_helpers_and_rejects_disallowed_pairs(): void
    {
        [$tenant, $store, $member, $schedule, $slot] = $this->setupWorkforce();
        $helperStore = Store::create(['tenant_id' => $tenant->id, 'name' => '支店']);
        $helper = Member::create(['tenant_id' => $tenant->id, 'store_id' => $helperStore->id, 'name' => 'ヘルプ', 'status' => 'active', 'is_shift_submitter' => true]);
        $this->policy($store, ['help_member_ids' => [$helper->id], 'excluded_pairs' => [[$member->id, $helper->id]]]);
        $schedule->update(['status' => 'draft']);
        $schedule->days()->update(['required_headcount' => 2]);
        $slot->delete();
        foreach ([$member, $helper] as $m) {
            $m->availabilityRequests()->create(['tenant_id' => $tenant->id, 'work_date' => '2026-10-01', 'preference' => 'available', 'available_from' => '09:00', 'available_until' => '18:00']);
        }
        $result = app(ShiftAutoScheduler::class)->finalize($schedule->id);
        $this->assertSame(1, $result->shiftSlots->first()->assignments->count());
        $this->assertSame('understaffed', $result->shiftSlots->first()->status);
    }

    public function test_individual_limits_override_store_limits_and_cancelled_work_is_ignored(): void
    {
        [$tenant, $store, $member, $schedule, $slot] = $this->setupWorkforce();
        $this->policy($store, ['max_daily_hours' => 12]);
        $member->update(['profiles' => ['workforce_rules' => ['max_daily_hours' => 8]]]);
        $this->assertContains('1日の勤務時間が設定上限を超えます', app(WorkforceRules::class)->violations($member, $slot));
        $member->update(['profiles' => []]);
        $other = $schedule->shiftSlots()->create(['title' => '取消勤務', 'starts_at' => '2026-10-01 08:00', 'ends_at' => '2026-10-01 19:00', 'required_headcount' => 1]);
        $other->assignments()->create(['member_id' => $member->id, 'status' => 'cancelled']);
        $this->assertSame([], app(WorkforceRules::class)->violations($member, $slot));
    }

    public function test_staff_cannot_see_or_apply_for_another_tenants_vacancy(): void
    {
        [$tenant, $store, $member, $schedule, $slot] = $this->setupWorkforce();
        $this->post('/dashboard/workforce', ['action' => 'vacancy', 'slot_id' => $slot->id, 'message' => '組織内限定', 'allow_help' => true])->assertRedirect();
        $other = Tenant::factory()->create(['data' => ['path' => 'other-workforce']]);
        $outsider = Member::create(['tenant_id' => $other->id, 'name' => '他社', 'status' => 'active', 'is_shift_submitter' => true, 'line_id' => 'outsider']);
        $this->withSession(['line_id' => 'outsider', 'line_member_id' => $outsider->id])->get('/other-workforce/line/workforce')->assertOk()->assertDontSee('組織内限定');
        $this->post('/other-workforce/line/workforce', ['action' => 'apply', 'vacancy_id' => DB::table('shift_vacancies')->first()->id])->assertNotFound();
        $this->assertDatabaseCount('vacancy_applications', 0);
    }

    public function test_staffing_analysis_excludes_registered_break_windows(): void
    {
        [$tenant, $store, $member, $schedule, $slot] = $this->setupWorkforce();
        $assignment = $slot->assignments()->create(['member_id' => $member->id, 'status' => 'assigned']);
        DB::table('shift_assignments')->where('id', $assignment->id)->update(['break_starts_at' => '2026-10-01 12:00:00', 'break_ends_at' => '2026-10-01 13:00:00']);
        $row = app(ShiftAnalytics::class)->forYear($tenant->id, 2026)['rows'][0];
        $this->assertEquals(8, $row['assigned']);
        $this->assertEquals(1, $row['shortage']);
    }

    public function test_line_manager_can_use_operations_but_cast_cannot(): void
    {
        [$tenant, $store, $member] = $this->setupWorkforce();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_MEMBER]);
        $member->update(['role' => Member::ROLE_MANAGER, 'user_id' => $user->id]);
        $this->withSession(['line_id' => $member->line_id, 'line_member_id' => $member->id])->get('/workforce-test/line/admin/workforce')->assertOk()->assertSee('/workforce-test/line/admin/workforce/export');
        $this->post('/workforce-test/line/admin/workforce', ['action' => 'skill', 'member_id' => $member->id, 'skill' => '受付', 'level' => 2])->assertRedirect()->assertSessionHasNoErrors();
        $member->update(['role' => Member::ROLE_CAST]);
        $this->get('/workforce-test/line/admin/workforce')->assertForbidden();
    }

    public function test_notification_retry_reuses_key_and_stops_after_accepted_response(): void
    {
        [$tenant, $store] = $this->setupWorkforce();
        $tenant->lineOfficialAccount()->create(['is_active' => true, 'channel_access_token' => 'test-token']);
        Http::fake(['*' => Http::sequence()->push([], 500)->push([], 409, ['x-line-accepted-request-id' => 'accepted-id'])]);
        $service = app(WorkforceMessaging::class);
        $service->create($tenant->id, '再送テスト', '本文', $store->id);
        $this->assertSame(0, $service->deliver());
        $this->assertSame(0, $service->deliver());
        $this->travel(3)->minutes();
        $this->assertSame(1, $service->deliver());
        $requests = Http::recorded();
        $this->assertSame($requests[0][0]->header('X-Line-Retry-Key'), $requests[1][0]->header('X-Line-Retry-Key'));
        $this->assertSame(0, $service->deliver());
        Http::assertSentCount(2);
    }

    public function test_notifications_are_not_retried_after_retry_key_window(): void
    {
        [$tenant, $store] = $this->setupWorkforce();
        $tenant->lineOfficialAccount()->create(['is_active' => true, 'channel_access_token' => 'test-token']);
        Http::fake(['*' => Http::response([], 500)]);
        $service = app(WorkforceMessaging::class);
        $service->create($tenant->id, '通知', '本文', $store->id);
        $service->deliver();
        $this->travel(24)->hours();
        $this->assertSame(0, $service->deliver());
        $this->assertDatabaseHas('workforce_message_recipients', ['delivery_status' => 'expired']);
        Http::assertSentCount(1);
    }

    public function test_diagnosis_and_historical_recommendations_do_not_invent_missing_data(): void
    {
        [$tenant, $store, $member, $schedule, $slot] = $this->setupWorkforce();
        $service = app(WorkforcePlanning::class);
        $this->assertSame([], $service->recommend($tenant->id, $store->id));
        $this->assertSame(0, $service->diagnose($schedule)['score']);
        $slot->assignments()->create(['member_id' => $member->id, 'status' => 'assigned']);
        $this->assertSame(100, $service->diagnose($schedule)['score']);
        $this->travelTo(now()->setDate(2026, 10, 2));
        $this->assertSame(1, $service->recommend($tenant->id, $store->id)[0]['headcount']);
        $schedule->days()->update(['starts_at' => null]);
        $this->assertNull($service->diagnose($schedule)['score']);
    }
}
