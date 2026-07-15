<?php

namespace App\Http\Controllers\Api\Liff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Liff\LoginRequest;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $result = DB::transaction(function () use ($payload): array {
            $registeredMember = null;

            if (! empty($payload['registration_token'])) {
                $registeredMember = Member::query()
                    ->where('tenant_id', $payload['tenant_id'])
                    ->where('registration_token', $payload['registration_token'])
                    ->lockForUpdate()
                    ->first();

                if (! $registeredMember) {
                    throw ValidationException::withMessages([
                        'registration_token' => '登録用QRコードが無効です。',
                    ]);
                }
            }

            $lineLinkedMember = Member::query()
                ->where('tenant_id', $payload['tenant_id'])
                ->where('line_id', $payload['line_user_id'])
                ->first();

            if ($registeredMember && $lineLinkedMember && $lineLinkedMember->id !== $registeredMember->id) {
                throw ValidationException::withMessages([
                    'line_user_id' => 'このLINEアカウントは別のスタッフに登録済みです。',
                ]);
            }

            $member = $registeredMember ?: $lineLinkedMember;

            if (! $member) {
                $user = User::create([
                    'tenant_id' => $payload['tenant_id'],
                    'name' => $payload['display_name'] ?? 'LINE User',
                    'icon_url' => $payload['picture_url'] ?? null,
                    'role' => User::ROLE_MEMBER,
                ]);

                $member = Member::create([
                    'tenant_id' => $payload['tenant_id'],
                    'store_id' => $payload['store_id'] ?? null,
                    'user_id' => $user->id,
                    'name' => $payload['display_name'] ?? 'LINE User',
                    'display_name' => $payload['display_name'] ?? 'LINE User',
                    'line_id' => $payload['line_user_id'],
                    'line_name' => $payload['display_name'] ?? null,
                    'icon_url' => $payload['picture_url'] ?? null,
                    'status' => 'active',
                    'is_linked' => true,
                    'login_at' => now(),
                    'registered_at' => now(),
                ]);
            } else {
                $user = $member->user ?: User::create([
                    'tenant_id' => $member->tenant_id,
                    'name' => $member->displayName(),
                    'icon_url' => $payload['picture_url'] ?? $member->icon_url,
                    'role' => User::ROLE_MEMBER,
                ]);

                $member->update([
                    'user_id' => $user->id,
                    'line_id' => $payload['line_user_id'],
                    'line_name' => $payload['display_name'] ?? $member->line_name,
                    'icon_url' => $payload['picture_url'] ?? $member->icon_url,
                    'is_linked' => true,
                    'login_at' => now(),
                    'registered_at' => $member->registered_at ?? now(),
                ]);
            }

            $user->forceFill(['login_at' => now()])->save();

            return [
                'member' => $member->refresh()->load(['store']),
                'user' => $user->refresh(),
                'token' => $user->createToken('liff', ['liff'])->plainTextToken,
            ];
        });

        return response()->json($result);
    }
}
