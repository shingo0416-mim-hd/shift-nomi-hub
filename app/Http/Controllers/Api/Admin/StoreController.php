<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStoreRequest;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $stores = Store::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return response()->json(['stores' => $stores]);
    }

    public function store(StoreStoreRequest $request): JsonResponse
    {
        $store = Store::create([
            ...$request->validated(),
            'tenant_id' => $request->user()->tenant_id,
            'timezone' => $request->validated('timezone', 'Asia/Tokyo'),
        ]);

        return response()->json(['store' => $store], 201);
    }

    public function update(StoreStoreRequest $request, Store $store): JsonResponse
    {
        abort_unless($store->tenant_id === $request->user()->tenant_id, 404);

        $store->update($request->validated());

        return response()->json(['store' => $store->refresh()]);
    }
}
