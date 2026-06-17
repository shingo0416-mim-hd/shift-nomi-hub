<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
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

        Session::put('line_intended_url', $request->query('redirect_to', url('/'.$request->attributes->get('tenantPath').'/line/login/complete')));

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

            Session::put('line_id', $profile['userId']);
            Session::put('line_member_id', $member->id);
            Session::forget('line_registration_token');

            return redirect(Session::pull('line_intended_url', url('/'.$tenantPath.'/line/login/complete')))
                ->with('line_login_status', 'LINEログインが完了しました。');
        } catch (Exception $exception) {
            Log::error('LINEログイン callback error', [
                'tenant_id' => $tenant->id,
                'message' => $exception->getMessage(),
            ]);

            return redirect(url('/'.$tenantPath.'/line/login/complete'))
                ->withErrors(['line_login' => 'LINEログインに失敗しました。'.$exception->getMessage()]);
        }
    }

    public function complete(Request $request): \Illuminate\Contracts\View\View
    {
        $member = Member::query()
            ->with('user')
            ->find(Session::get('line_member_id'));
        $tenantPath = $request->attributes->get('tenantPath');

        return view('line.login-complete', [
            'canOpenLineAdmin' => $member?->isCastAdmin() === true && $member?->user?->isAdmin() === true,
            'lineAdminUrl' => is_string($tenantPath) ? route('line.admin.dashboard', ['tenant' => $tenantPath]) : null,
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
                    'name' => $member->name ?: $displayName,
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

            $member->employeeProfile()->updateOrCreate(
                ['tenant_id' => $member->tenant_id],
                [
                    'user_id' => $member->user_id,
                    'display_name' => $member->name ?: $displayName,
                    'email' => $member->email,
                    'phone' => $member->phone,
                ],
            );

            return $member->refresh();
        });
    }
}
