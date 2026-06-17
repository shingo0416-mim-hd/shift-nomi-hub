<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantSettingsController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'line_liff_id' => ['nullable', 'string', 'max:255'],
        ]);

        $tenant = $request->user()->tenant;
        $tenant->update([
            'line_liff_id' => $payload['line_liff_id'] ?? null,
        ]);

        return response()->json([
            'tenant' => $tenant->refresh(),
        ]);
    }
}
