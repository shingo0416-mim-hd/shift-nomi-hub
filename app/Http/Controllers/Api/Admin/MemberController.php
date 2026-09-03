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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $members = Member::query()
            ->with(['store', 'schedulingProfile'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->when($request->query('store_id'), fn ($query, $storeId) => $query->where('store_id', $storeId))
            ->orderBy('name')
            ->paginate((int) $request->query('per_page', 30));

        return response()->json($members);
    }

    public function store(MemberStoreRequest $request): JsonResponse
    {
        $member = DB::transaction(function () use ($request): Member {
            $validated = $request->validated();
            $member = Member::create([
                ...Arr::except($validated, $this->schedulingProfileFields()),
                'tenant_id' => $request->user()->tenant_id,
                'status' => $request->validated('status', 'active'),
                'role' => $request->validated('role', Member::ROLE_CAST),
                'registration_token' => Str::random(48),
            ]);
            $this->saveSchedulingProfile($member, $validated, (int) $request->user()->tenant_id);

            return $member;
        });

        return response()->json(['member' => $member->load(['store', 'schedulingProfile'])], 201);
    }

    public function update(MemberStoreRequest $request, Member $member): JsonResponse
    {
        abort_unless($member->tenant_id === $request->user()->tenant_id, 404);

        DB::transaction(function () use ($request, $member): void {
            $validated = $request->validated();
            $member->update(Arr::except($validated, $this->schedulingProfileFields()));
            $this->saveSchedulingProfile($member, $validated, (int) $request->user()->tenant_id);
        });

        return response()->json(['member' => $member->refresh()->load(['store', 'schedulingProfile'])]);
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
        $renderer = new ImageRenderer(new RendererStyle(320, 2), new SvgImageBackEnd);
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

    /** @return array<int, string> */
    private function schedulingProfileFields(): array
    {
        return ['attendance_score', 'popularity_score', 'priority_points', 'newcomer_priority_until', 'scheduling_admin_notes'];
    }

    /** @param array<string, mixed> $validated */
    private function saveSchedulingProfile(Member $member, array $validated, int $tenantId): void
    {
        if (! collect($this->schedulingProfileFields())->contains(fn (string $field) => array_key_exists($field, $validated))) {
            return;
        }

        $member->schedulingProfile()->updateOrCreate([], [
            'tenant_id' => $tenantId,
            'attendance_score' => $validated['attendance_score'] ?? 50,
            'popularity_score' => $validated['popularity_score'] ?? 50,
            'priority_points' => $validated['priority_points'] ?? 0,
            'newcomer_priority_until' => $validated['newcomer_priority_until'] ?? null,
            'admin_notes' => $validated['scheduling_admin_notes'] ?? null,
        ]);
    }
}
