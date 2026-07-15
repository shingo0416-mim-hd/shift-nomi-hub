<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MemberStoreRequest;
use App\Models\Member;
use App\Services\TenantPathService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $members = Member::query()
            ->with(['store'])
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
                'role' => $request->validated('role', Member::ROLE_CAST),
                'registration_token' => Str::random(48),
            ]);

            return $member;
        });

        return response()->json(['member' => $member->load(['store'])], 201);
    }

    public function update(MemberStoreRequest $request, Member $member): JsonResponse
    {
        abort_unless($member->tenant_id === $request->user()->tenant_id, 404);

        $member->update($request->validated());

        return response()->json(['member' => $member->refresh()->load(['store'])]);
    }

    public function registrationQr(Request $request, Member $member): JsonResponse
    {
        abort_unless($member->tenant_id === $request->user()->tenant_id, 404);

        if (! Schema::hasTable('line_login_settings') || ! $request->user()->tenant?->lineLoginSetting?->channel_id) {
            throw ValidationException::withMessages([
                'line_login' => ['LINEログインのチャネルIDが未設定のため、登録QRは表示できません。'],
            ]);
        }

        if (! $member->registration_token) {
            $member->forceFill(['registration_token' => Str::random(48)])->save();
        }

        $url = $this->registrationUrl($member);
        $renderer = new ImageRenderer(new RendererStyle(320, 2), new SvgImageBackEnd());
        $qrSvg = (new Writer($renderer))->writeString($url);

        return response()->json([
            'member' => [
                ...$member->only(['id', 'name', 'display_name', 'line_id', 'is_linked', 'registered_at']),
                'display_name' => $member->displayName(),
            ],
            'registration_url' => $url,
            'qr_svg' => $qrSvg,
        ]);
    }

    private function registrationUrl(Member $member): string
    {
        $tenant = $member->tenant;
        $tenantPath = $tenant ? app(TenantPathService::class)->pathFor($tenant) : null;

        if (! $tenantPath) {
            return route('liff.register', ['registrationToken' => $member->registration_token]);
        }

        return route('line.login', [
            'tenant' => $tenantPath,
            'registration_token' => $member->registration_token,
        ]);
    }
}
