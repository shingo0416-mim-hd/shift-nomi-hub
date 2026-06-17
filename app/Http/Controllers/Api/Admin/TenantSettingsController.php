<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class TenantSettingsController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'line_login_channel_id' => ['nullable', 'string', 'max:255'],
            'line_login_channel_secret' => ['nullable', 'string'],
            'liff_id' => ['nullable', 'string', 'max:255'],
            'line_official_channel_id' => ['nullable', 'string', 'max:255'],
            'line_official_channel_access_token' => ['nullable', 'string'],
            'line_official_channel_secret' => ['nullable', 'string'],
            'line_official_webhook_url' => ['nullable', 'url', 'max:255'],
            'line_official_line_at_id' => ['nullable', 'string', 'max:255'],
            'line_official_line_timeline_url' => ['nullable', 'url', 'max:255'],
        ]);

        if (! Schema::hasTable('line_login_settings') || ! Schema::hasTable('line_liff_settings') || ! Schema::hasTable('line_official_accounts')) {
            throw ValidationException::withMessages([
                'line_settings' => ['LINE設定テーブルが未作成です。マイグレーション実行後に保存してください。'],
            ]);
        }

        $tenant = $request->user()->tenant;
        $lineLoginValues = [
            'channel_id' => $payload['line_login_channel_id'] ?? null,
            'is_active' => true,
        ];
        if (array_key_exists('line_login_channel_secret', $payload)) {
            $lineLoginValues['channel_secret'] = $payload['line_login_channel_secret'];
        }

        $lineOfficialValues = [
            'channel_id' => $payload['line_official_channel_id'] ?? null,
            'webhook_url' => $payload['line_official_webhook_url'] ?? null,
            'line_at_id' => $payload['line_official_line_at_id'] ?? null,
            'line_timeline_url' => $payload['line_official_line_timeline_url'] ?? null,
            'is_active' => true,
        ];
        if (array_key_exists('line_official_channel_access_token', $payload)) {
            $lineOfficialValues['channel_access_token'] = $payload['line_official_channel_access_token'];
        }
        if (array_key_exists('line_official_channel_secret', $payload)) {
            $lineOfficialValues['channel_secret'] = $payload['line_official_channel_secret'];
        }

        $tenant->lineLoginSetting()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            $lineLoginValues
        );
        $tenant->lineLiffSetting()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'liff_id' => $payload['liff_id'] ?? null,
                'is_active' => true,
            ]
        );
        $tenant->lineOfficialAccount()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            $lineOfficialValues
        );

        return response()->json([
            'tenant' => $tenant->refresh()->load(['lineLoginSetting', 'lineLiffSetting', 'lineOfficialAccount']),
        ]);
    }
}
