<?php

namespace App\Services;

use App\Models\Tenant;
use Exception;
use Illuminate\Support\Facades\Http;

class LineLoginService
{
    public function authorizationUrl(Tenant $tenant, string $tenantPath, bool $disableAutoLogin = false): string
    {
        $setting = $tenant->lineLoginSetting;

        if (! $setting?->channel_id || ! $setting?->channel_secret) {
            throw new Exception('LINEログイン設定が未設定です。');
        }

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $setting->channel_id,
            'redirect_uri' => $this->callbackUrl($tenantPath),
            'state' => csrf_token(),
            'scope' => 'profile',
            ...($disableAutoLogin ? ['disable_auto_login' => true] : []),
        ]);

        return 'https://access.line.me/oauth2/v2.1/authorize?'.$query;
    }

    public function accessToken(string $code, Tenant $tenant, string $tenantPath): string
    {
        $setting = $tenant->lineLoginSetting;

        if (! $setting?->channel_id || ! $setting?->channel_secret) {
            throw new Exception('LINEログイン設定が未設定です。');
        }

        $response = Http::asForm()->post('https://api.line.me/oauth2/v2.1/token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->callbackUrl($tenantPath),
            'client_id' => $setting->channel_id,
            'client_secret' => $setting->channel_secret,
        ]);

        if (! $response->successful() || ! $response->json('access_token')) {
            throw new Exception('LINEアクセストークンの取得に失敗しました。');
        }

        return (string) $response->json('access_token');
    }

    /**
     * @return array{userId: string, displayName?: string, pictureUrl?: string}
     */
    public function profile(string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get('https://api.line.me/v2/profile');

        if (! $response->successful() || ! $response->json('userId')) {
            throw new Exception('LINEプロフィールの取得に失敗しました。');
        }

        return $response->json();
    }

    private function callbackUrl(string $tenantPath): string
    {
        return url('/'.$tenantPath.'/line/login/callback');
    }
}
