<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\RegisterRequest;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $result = DB::transaction(function () use ($payload): array {
            $tenant = Tenant::create([
                'name' => $payload['tenant_name'],
                'data' => [
                    'product' => 'ShiftHub',
                    'created_from' => 'admin_register',
                ],
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => $payload['password'],
                'role' => User::ROLE_ADMIN,
            ]);

            $store = null;
            if (! empty($payload['store_name'])) {
                $store = Store::create([
                    'tenant_id' => $tenant->id,
                    'name' => $payload['store_name'],
                ]);
            }

            $token = $user->createToken('admin', ['admin'])->plainTextToken;

            return compact('tenant', 'user', 'store', 'token');
        });

        return response()->json($result, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $user = User::query()->where('email', $payload['email'])->first();

        if (! $user || ! Hash::check($payload['password'], (string) $user->password) || ! $user->isAdmin()) {
            throw ValidationException::withMessages([
                'email' => ['ログイン情報が正しくありません。'],
            ]);
        }

        $user->forceFill(['login_at' => now()])->save();

        return response()->json([
            'user' => $user->load('tenant'),
            'token' => $user->createToken($payload['device_name'] ?? 'admin', ['admin'])->plainTextToken,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('tenant'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
