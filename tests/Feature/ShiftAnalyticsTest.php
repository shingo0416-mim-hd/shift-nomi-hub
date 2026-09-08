<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\ShiftSchedule;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ShiftAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Fortify;
use Tests\TestCase;

class ShiftAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_analysis_is_tenant_scoped_and_handles_missing_times_and_cancelled_assignments(): void
    {
        $tenant = Tenant::factory()->create();
        $other = Tenant::factory()->create();
        $store = Store::create(['tenant_id' => $tenant->id, 'name' => '対象店舗']);
        $otherStore = Store::create(['tenant_id' => $other->id, 'name' => '他社の店舗']);
        foreach ([$store, $otherStore] as $currentStore) {
            $schedule = ShiftSchedule::create(['tenant_id' => $currentStore->tenant_id, 'store_id' => $currentStore->id, 'starts_on' => '2026-09-01', 'ends_on' => '2026-09-30', 'status' => 'draft']);
            $schedule->days()->create(['store_id' => $currentStore->id, 'scheduled_on' => '2026-09-01', 'starts_at' => '22:00', 'ends_at' => '02:00', 'required_headcount' => 2]);
            $schedule->days()->create(['store_id' => $currentStore->id, 'scheduled_on' => '2026-09-02']);
            $slot = $schedule->shiftSlots()->create(['title' => '夜間', 'starts_at' => '2026-09-01 22:00:00', 'ends_at' => '2026-09-02 02:00:00', 'required_headcount' => 2]);
            foreach (['assigned', 'cancelled'] as $status) {
                $member = Member::create(['tenant_id' => $currentStore->tenant_id, 'store_id' => $currentStore->id, 'name' => $status]);
                $slot->assignments()->create(['member_id' => $member->id, 'status' => $status]);
            }
        }
        $report = (new ShiftAnalytics)->forYear($tenant->id, 2026);
        $this->assertCount(1, $report['stores']);
        $this->assertCount(2, $report['rows']);
        $this->assertEquals(8, $report['rows'][0]['required']);
        $this->assertEquals(4, $report['rows'][0]['assigned']);
        $this->assertEquals(4, $report['rows'][0]['shortage']);
        $this->assertFalse($report['rows'][1]['configured']);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_ADMIN]);
        $this->actingAs($admin)->get('/dashboard/analytics')->assertRedirect(route('two-factor.settings'));
        $admin->forceFill(['two_factor_secret' => Fortify::currentEncrypter()->encrypt('secret'), 'two_factor_confirmed_at' => now()])->save();
        $this->actingAs($admin)->get('/dashboard/analytics?year=2026')->assertOk()->assertSee('シフトの人員配置を分析')->assertSee('対象店舗')->assertDontSee('他社の店舗');
    }
}
