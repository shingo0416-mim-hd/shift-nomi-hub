<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LiffRegistrationTest extends TestCase
{
    use RefreshDatabase;

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
}
