<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MemberStoreRequest;
use App\Models\EmployeeProfile;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $members = Member::query()
            ->with(['store', 'employeeProfile'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->when($request->query('store_id'), fn ($query, $storeId) => $query->where('store_id', $storeId))
            ->orderBy('name')
            ->paginate((int) $request->query('per_page', 30));

        return response()->json($members);
    }

    public function store(MemberStoreRequest $request): JsonResponse
    {
        $member = DB::transaction(function () use ($request): Member {
            $member = Member::create([
                ...$request->validated(),
                'tenant_id' => $request->user()->tenant_id,
                'status' => $request->validated('status', 'active'),
            ]);

            EmployeeProfile::create([
                'tenant_id' => $member->tenant_id,
                'member_id' => $member->id,
                'display_name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
            ]);

            return $member;
        });

        return response()->json(['member' => $member->load(['store', 'employeeProfile'])], 201);
    }

    public function update(MemberStoreRequest $request, Member $member): JsonResponse
    {
        abort_unless($member->tenant_id === $request->user()->tenant_id, 404);

        $member->update($request->validated());
        $member->employeeProfile?->update([
            'display_name' => $member->name,
            'email' => $member->email,
            'phone' => $member->phone,
        ]);

        return response()->json(['member' => $member->refresh()->load(['store', 'employeeProfile'])]);
    }
}
