<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\AvailabilityRequest;
use App\Models\ShiftSchedule;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LiffRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_profiles_table_is_removed_from_current_schema(): void
    {
        $this->assertFalse(Schema::hasTable('employee_profiles'));
        $this->assertFalse(Schema::hasColumn('shift_assignments', 'employee_profile_id'));
        $this->assertFalse(Schema::hasColumn('availability_requests', 'employee_profile_id'));
        $this->assertTrue(Schema::hasColumn('shift_assignments', 'member_id'));
        $this->assertTrue(Schema::hasColumn('availability_requests', 'member_id'));
    }

    public function test_registration_token_links_line_account_to_temporary_member(): void
    {
        $tenant = Tenant::factory()->create();
        $member = Member::create([
            'tenant_id' => $tenant->id,
            'name' => '仮登録 スタッフ',
            'status' => 'active',
            'registration_token' => Str::random(48),
        ]);

        $this->postJson('/api/liff/auth/login', [
            'tenant_id' => $tenant->id,
            'registration_token' => $member->registration_token,
            'line_user_id' => 'line-user-001',
            'display_name' => 'LINE Staff',
            'picture_url' => 'https://example.com/profile.png',
        ])->assertOk()
            ->assertJsonPath('member.id', $member->id)
            ->assertJsonPath('member.line_id', 'line-user-001')
            ->assertJsonPath('member.is_linked', true);

        $member->refresh();

        $this->assertSame('line-user-001', $member->line_id);
        $this->assertTrue($member->is_linked);
        $this->assertNotNull($member->registered_at);
        $this->assertNotNull($member->user_id);
    }

    public function test_registration_token_rejects_line_account_linked_to_another_member(): void
    {
        $tenant = Tenant::factory()->create();
        $temporaryMember = Member::create([
            'tenant_id' => $tenant->id,
            'name' => '仮登録 スタッフ',
            'status' => 'active',
            'registration_token' => Str::random(48),
        ]);
        Member::create([
            'tenant_id' => $tenant->id,
            'name' => '登録済み スタッフ',
            'status' => 'active',
            'line_id' => 'line-user-001',
            'is_linked' => true,
        ]);

        $this->postJson('/api/liff/auth/login', [
            'tenant_id' => $tenant->id,
            'registration_token' => $temporaryMember->registration_token,
            'line_user_id' => 'line-user-001',
            'display_name' => 'LINE Staff',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('line_user_id');
    }

    public function test_admin_registration_qr_uses_tenant_line_login_endpoint(): void
    {
        $tenant = Tenant::factory()->create([
            'data' => ['path' => 'mim-hd'],
        ]);
        $tenant->lineLoginSetting()->create([
            'channel_id' => '2000000000',
            'channel_secret' => 'secret',
            'is_active' => true,
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_ADMIN,
        ]);
        $member = Member::create([
            'tenant_id' => $tenant->id,
            'name' => '仮登録 スタッフ',
            'status' => 'active',
            'registration_token' => Str::random(48),
        ]);

        Sanctum::actingAs($admin, ['admin']);

        $this->getJson("/api/admin/members/{$member->id}/registration-qr")
            ->assertOk()
            ->assertJsonPath(
                'registration_url',
                url("/mim-hd/line/login?registration_token={$member->registration_token}")
            )
            ->assertJsonStructure(['member', 'registration_url', 'qr_svg']);
    }

    public function test_admin_tenant_settings_autogenerates_line_timeline_url(): void
    {
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_ADMIN,
        ]);

        Sanctum::actingAs($admin, ['admin']);

        $this->putJson('/api/admin/tenant/settings', [
            'setting_type' => 'official',
            'line_official_line_at_id' => '@838emdnt',
        ])->assertOk()
            ->assertJsonPath('tenant.line_official_account.line_at_id', '@838emdnt')
            ->assertJsonPath('tenant.line_official_account.line_timeline_url', 'https://line.me/R/ti/p/@838emdnt');

        $this->assertDatabaseHas('line_official_accounts', [
            'tenant_id' => $tenant->id,
            'line_at_id' => '@838emdnt',
            'line_timeline_url' => 'https://line.me/R/ti/p/@838emdnt',
        ]);
    }

    public function test_line_login_callback_links_registration_token_member(): void
    {
        Http::fake([
            'https://api.line.me/oauth2/v2.1/token' => Http::response(['access_token' => 'line-access-token']),
            'https://api.line.me/v2/profile' => Http::response([
                'userId' => 'line-user-001',
                'displayName' => 'LINE Staff',
                'pictureUrl' => 'https://example.com/profile.png',
            ]),
        ]);

        $tenant = Tenant::factory()->create([
            'data' => ['path' => 'mim-hd'],
        ]);
        $tenant->lineLoginSetting()->create([
            'channel_id' => '2000000000',
            'channel_secret' => 'secret',
            'is_active' => true,
        ]);
        $member = Member::create([
            'tenant_id' => $tenant->id,
            'name' => '仮登録 スタッフ',
            'status' => 'active',
            'registration_token' => Str::random(48),
        ]);

        $this->withSession([
            'line_registration_token' => $member->registration_token,
            'line_intended_url' => url('/mim-hd/line/login/complete'),
        ])->get('/mim-hd/line/login/callback?code=line-code')
            ->assertRedirect(url('/mim-hd/line/login/complete'));

        $member->refresh();

        $this->assertSame('line-user-001', $member->line_id);
        $this->assertSame('LINE Staff', $member->line_name);
        $this->assertTrue($member->is_linked);
        $this->assertNotNull($member->registered_at);
    }

    public function test_line_admin_dashboard_allows_line_linked_admin_member(): void
    {
        $tenant = Tenant::factory()->create([
            'data' => ['path' => 'mim-hd'],
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_ADMIN,
        ]);
        $member = Member::create([
            'tenant_id' => $tenant->id,
            'user_id' => $admin->id,
            'name' => '管理者 スタッフ',
            'status' => 'active',
            'role' => Member::ROLE_ADMIN,
            'line_id' => 'line-admin-001',
            'is_linked' => true,
        ]);

        $this->withSession([
            'line_id' => 'line-admin-001',
            'line_member_id' => $member->id,
        ])->get('/mim-hd/line/admin')
            ->assertOk()
            ->assertSee('シフト管理');
    }

    public function test_line_admin_dashboard_allows_manager_member_with_member_user_role(): void
    {
        $tenant = Tenant::factory()->create([
            'data' => ['path' => 'mim-hd'],
        ]);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_MEMBER,
        ]);
        $member = Member::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'name' => '店長 スタッフ',
            'status' => 'active',
            'role' => Member::ROLE_MANAGER,
            'line_id' => 'line-manager-001',
            'is_linked' => true,
        ]);

        $this->withSession([
            'line_id' => 'line-manager-001',
            'line_member_id' => $member->id,
        ])->get('/mim-hd/line/admin')
            ->assertOk()
            ->assertSee('シフト管理');
    }

    public function test_line_admin_dashboard_rejects_non_admin_member(): void
    {
        $tenant = Tenant::factory()->create([
            'data' => ['path' => 'mim-hd'],
        ]);
        $member = Member::create([
            'tenant_id' => $tenant->id,
            'name' => '一般 スタッフ',
            'status' => 'active',
            'line_id' => 'line-member-001',
            'is_linked' => true,
        ]);

        $this->withSession([
            'line_id' => 'line-member-001',
            'line_member_id' => $member->id,
        ])->get('/mim-hd/line/admin')
            ->assertForbidden();
    }

    public function test_line_admin_can_create_shift_schedule(): void
    {
        $tenant = Tenant::factory()->create([
            'data' => ['path' => 'mim-hd'],
        ]);
        $store = Store::create([
            'tenant_id' => $tenant->id,
            'name' => '本店',
            'timezone' => 'Asia/Tokyo',
            'is_active' => true,
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_ADMIN,
        ]);
        $member = Member::create([
            'tenant_id' => $tenant->id,
            'user_id' => $admin->id,
            'name' => '管理者 スタッフ',
            'status' => 'active',
            'role' => Member::ROLE_ADMIN,
            'line_id' => 'line-admin-001',
            'is_linked' => true,
        ]);

        $this->withSession([
            '_token' => 'csrf-token',
            'line_id' => 'line-admin-001',
            'line_member_id' => $member->id,
        ])->withHeader('X-CSRF-TOKEN', 'csrf-token')
            ->postJson('/mim-hd/line/admin/api/shift-schedules', [
                'store_id' => $store->id,
                'starts_on' => '2026-07-01',
                'ends_on' => '2026-07-31',
                'status' => 'draft',
            ])->assertCreated()
            ->assertJsonPath('shift_schedule.store_id', $store->id);

        $this->assertDatabaseHas(ShiftSchedule::class, [
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'created_by' => $admin->id,
            'status' => 'draft',
        ]);
    }

    public function test_line_manager_can_create_shift_schedule_with_member_user_role(): void
    {
        $tenant = Tenant::factory()->create([
            'data' => ['path' => 'mim-hd'],
        ]);
        $store = Store::create([
            'tenant_id' => $tenant->id,
            'name' => '本店',
            'timezone' => 'Asia/Tokyo',
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_MEMBER,
        ]);
        $member = Member::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'name' => '店長 スタッフ',
            'status' => 'active',
            'role' => Member::ROLE_MANAGER,
            'line_id' => 'line-manager-001',
            'is_linked' => true,
        ]);

        $this->withSession([
            '_token' => 'csrf-token',
            'line_id' => 'line-manager-001',
            'line_member_id' => $member->id,
        ])->withHeader('X-CSRF-TOKEN', 'csrf-token')
            ->postJson('/mim-hd/line/admin/api/shift-schedules', [
                'store_id' => $store->id,
                'starts_on' => '2026-08-01',
                'ends_on' => '2026-08-31',
                'status' => 'draft',
            ])->assertCreated()
            ->assertJsonPath('shift_schedule.store_id', $store->id);

        $this->assertDatabaseHas(ShiftSchedule::class, [
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'created_by' => $user->id,
            'status' => 'draft',
        ]);
    }

    public function test_liff_member_can_create_availability_request_without_employee_profile(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_MEMBER,
        ]);
        $member = Member::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'display_name' => 'みらい',
            'name' => '新規 キャスト',
            'status' => 'active',
            'role' => Member::ROLE_CAST,
        ]);

        Sanctum::actingAs($user, ['liff']);

        $this->postJson('/api/liff/availability-requests', [
            'work_date' => '2026-08-10',
            'available_from' => '18:00',
            'available_until' => '23:00',
            'preference' => 'available',
        ])->assertOk()
            ->assertJsonPath('availability_request.member_id', $member->id);

        $this->assertDatabaseHas(AvailabilityRequest::class, [
            'tenant_id' => $tenant->id,
            'member_id' => $member->id,
            'work_date' => '2026-08-10 00:00:00',
        ]);
    }

    public function test_line_admin_can_create_member_when_cast_role_is_admin(): void
    {
        $tenant = Tenant::factory()->create([
            'data' => ['path' => 'mim-hd'],
        ]);
        $store = Store::create([
            'tenant_id' => $tenant->id,
            'name' => '本店',
            'timezone' => 'Asia/Tokyo',
            'is_active' => true,
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => User::ROLE_ADMIN,
        ]);
        $member = Member::create([
            'tenant_id' => $tenant->id,
            'user_id' => $admin->id,
            'name' => '管理者 スタッフ',
            'status' => 'active',
            'role' => Member::ROLE_ADMIN,
            'line_id' => 'line-admin-001',
            'is_linked' => true,
        ]);

        $this->withSession([
            '_token' => 'csrf-token',
            'line_id' => 'line-admin-001',
            'line_member_id' => $member->id,
        ])->withHeader('X-CSRF-TOKEN', 'csrf-token')
            ->postJson('/mim-hd/line/admin/api/members', [
                'store_id' => $store->id,
                'display_name' => 'みらい',
                'last_name' => '新規',
                'first_name' => 'キャスト',
                'email' => 'cast@example.com',
                'status' => 'active',
                'role' => Member::ROLE_CAST,
                'is_shift_submitter' => true,
            ])->assertCreated()
            ->assertJsonPath('member.display_name', 'みらい')
            ->assertJsonPath('member.name', '新規 キャスト');

        $this->assertDatabaseHas(Member::class, [
            'tenant_id' => $tenant->id,
            'store_id' => $store->id,
            'display_name' => 'みらい',
            'name' => '新規 キャスト',
            'role' => Member::ROLE_CAST,
        ]);
    }
}
