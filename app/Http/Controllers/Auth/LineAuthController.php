<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LineLoginService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class LineAuthController extends Controller
{
    public function __construct(private readonly LineLoginService $lineLoginService)
    {
    }

    public function login(Request $request): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');
        $tenant->loadMissing('lineLoginSetting');

        if ($request->filled('registration_token')) {
            Session::put('line_registration_token', $request->string('registration_token')->toString());
        }

        Session::put('line_intended_url', Session::get('line_intended_url', url('/' . $request->attributes->get('tenantPath') . '/line/login/complete')));

        return redirect()->away(
            $this->lineLoginService->authorizationUrl($tenant, (string) $request->attributes->get('tenantPath'))
        );
    }

    public function callback(Request $request): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');
        $tenantPath = (string) $request->attributes->get('tenantPath');
        $tenant->loadMissing('lineLoginSetting');

        try {
            if (! $request->filled('code')) {
                throw new Exception('LINE認証コードが取得できませんでした。');
            }

            $accessToken = $this->lineLoginService->accessToken($request->string('code')->toString(), $tenant, $tenantPath);
            $profile = $this->lineLoginService->profile($accessToken);

            $member = $this->linkMember($tenant, $profile);

            $request->session()->regenerate();
            Session::put('line_id', $profile['userId']);
            Session::put('line_member_id', $member->id);
            Session::forget(['line_registration_token', 'line_login_error']);

            $completeUrl = route('line.login.complete', ['tenant' => $tenantPath]);
            $allowedDestinations = [$completeUrl, route('line.availability', ['tenant' => $tenantPath])];
            if ($member->canManageShiftSchedules()) {
                $allowedDestinations[] = route('line.admin.dashboard', ['tenant' => $tenantPath]);
            }
            $intended = Session::pull('line_intended_url');

            return redirect(in_array($intended, $allowedDestinations, true) ? $intended : $completeUrl)
                ->with('line_login_status', 'LINEログインが完了しました。');
        } catch (Exception $exception) {
            Session::forget(['line_id', 'line_member_id', 'line_intended_url']);
            Log::error('LINEログイン callback error', [
                'tenant_id' => $tenant->id,
                'message' => $exception->getMessage(),
            ]);

            Session::put('line_login_error', 'LINEログインに失敗しました。登録用QRコードから再度ログインしてください。解決しない場合は管理者へ連絡してください。');

            return redirect()->route('line.login.complete', ['tenant' => $tenantPath]);
        }
    }

    public function complete(Request $request): \Illuminate\Contracts\View\View|RedirectResponse
    {
        $tenantPath = $request->attributes->get('tenantPath');
        $member = Session::has('line_id') && Session::has('line_member_id')
            ? Member::query()->with('user')
                ->where('tenant_id', $request->attributes->get('tenant')->id)
                ->where('line_id', Session::get('line_id'))
                ->find(Session::get('line_member_id'))
            : null;
        $canSubmit = $member && $member->status === 'active' && $member->is_shift_submitter && $member->store_id;
        $canManage = $member && $member->status === 'active' && $member->canManageShiftSchedules() && $member->user;
        if ($canSubmit && ! $canManage) {
            return redirect()->route('line.availability', ['tenant' => $tenantPath]);
        }
        $message = ! $member ? 'LINEログインを行ってください。'
            : ($member->status !== 'active' ? '現在このスタッフアカウントは利用できません。管理者へ確認してください。'
                : (! $member->is_shift_submitter ? 'シフト提出対象に設定されていません。管理者へ確認してください。'
                    : (! $member->store_id ? '所属店舗が未設定です。管理者へ店舗の設定を依頼してください。' : null)));

        return view('line.login-complete', [
            'canOpenLineAdmin' => $canManage,
            'lineAdminUrl' => route('line.admin.dashboard', ['tenant' => $tenantPath]),
            'canSubmit' => $canSubmit,
            'loginMessage' => $message,
            'loginError' => $request->session()->pull('line_login_error'),
            'isLineLoggedIn' => $member !== null,
        ]);
    }

    /**
     * @param array{userId: string, displayName?: string, pictureUrl?: string} $profile
     */
    private function linkMember(Tenant $tenant, array $profile): Member
    {
        return DB::transaction(function () use ($tenant, $profile): Member {
            $registrationToken = Session::get('line_registration_token');
            $registeredMember = null;

            if ($registrationToken) {
                $registeredMember = Member::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('registration_token', $registrationToken)
                    ->lockForUpdate()
                    ->first();

                if (! $registeredMember) {
                    throw ValidationException::withMessages([
                        'registration_token' => '登録用QRコードが無効です。',
                    ]);
                }
            }

            $lineLinkedMember = Member::query()
                ->where('tenant_id', $tenant->id)
                ->where('line_id', $profile['userId'])
                ->first();

            if ($registeredMember && $lineLinkedMember && $lineLinkedMember->id !== $registeredMember->id) {
                throw ValidationException::withMessages([
                    'line_user_id' => 'このLINEアカウントは別のスタッフに登録済みです。',
                ]);
            }

            $member = $registeredMember ?: $lineLinkedMember;
            if ($member && $member->status !== 'active') {
                throw ValidationException::withMessages(['member' => 'このスタッフアカウントは利用できません。']);
            }
            if ($registeredMember?->line_id && $registeredMember->line_id !== $profile['userId']) {
                throw ValidationException::withMessages(['registration_token' => 'このスタッフは別のLINEアカウントに登録済みです。']);
            }
            $displayName = $profile['displayName'] ?? 'LINE User';
            $pictureUrl = $profile['pictureUrl'] ?? null;

            if (! $member) {
                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $displayName,
                    'icon_url' => $pictureUrl,
                    'role' => User::ROLE_MEMBER,
                ]);

                $member = Member::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'name' => $displayName,
                    'display_name' => $displayName,
                    'line_id' => $profile['userId'],
                    'line_name' => $displayName,
                    'icon_url' => $pictureUrl,
                    'status' => 'active',
                    'is_linked' => true,
                    'login_at' => now(),
                    'registered_at' => now(),
                ]);
            } else {
                $user = $member->user ?: User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $member->displayName(),
                    'icon_url' => $pictureUrl ?: $member->icon_url,
                    'role' => User::ROLE_MEMBER,
                ]);

                $member->update([
                    'user_id' => $user->id,
                    'line_id' => $profile['userId'],
                    'line_name' => $displayName,
                    'icon_url' => $pictureUrl ?: $member->icon_url,
                    'is_linked' => true,
                    'login_at' => now(),
                    'registered_at' => $member->registered_at ?? now(),
                ]);
            }
            return $member->refresh();
        });
    }
}
