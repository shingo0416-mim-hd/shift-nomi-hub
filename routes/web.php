<?php

use App\Http\Controllers\Liff\RegistrationController;
use App\Models\Member;
use App\Models\ShiftSchedule;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::redirect('/', '/login');
Route::redirect('/admin', '/dashboard');

Route::get('/liff/register/{registrationToken}', [RegistrationController::class, 'show'])->name('liff.register');

Route::middleware('auth')->group(function (): void {
    Route::get('/two-factor-settings', fn () => view('auth.two-factor-settings'))->name('two-factor.settings');

    $adminPage = function (string $page) {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor.settings');
        }

        $stores = Store::query()
            ->where('tenant_id', $user->tenant_id)
            ->orderBy('name')
            ->get();
        $members = Member::query()
            ->with(['store', 'employeeProfile'])
            ->where('tenant_id', $user->tenant_id)
            ->orderBy('name')
            ->limit(100)
            ->get();
        $schedules = ShiftSchedule::query()
            ->with(['store', 'shiftSlots.assignments.employeeProfile'])
            ->where('tenant_id', $user->tenant_id)
            ->latest('starts_on')
            ->limit(100)
            ->get();

        $userRelations = ['tenant'];
        if (Schema::hasTable('line_login_settings')) {
            $userRelations[] = 'tenant.lineLoginSetting';
        }
        if (Schema::hasTable('line_liff_settings')) {
            $userRelations[] = 'tenant.lineLiffSetting';
        }
        if (Schema::hasTable('line_official_accounts')) {
            $userRelations[] = 'tenant.lineOfficialAccount';
        }

        return view('admin.dashboard', [
            'page' => $page,
            'initialData' => [
                'stores' => $stores,
                'members' => $members,
                'schedules' => $schedules,
                'user' => $user->load($userRelations),
            ],
        ]);
    };

    Route::get('/dashboard', fn () => $adminPage('overview'))->name('dashboard');
    Route::get('/dashboard/schedules', fn () => $adminPage('schedules'))->name('admin.schedules');
    Route::get('/dashboard/schedules/create', fn () => $adminPage('schedule-create'))->name('admin.schedules.create');
    Route::get('/dashboard/members', fn () => $adminPage('members'))->name('admin.members');
    Route::get('/dashboard/members/{member}/edit', function (Member $member) use ($adminPage) {
        abort_unless((int) $member->getAttribute('tenant_id') === (int) auth()->user()->tenant_id, 404);

        return $adminPage('member-edit')->with('editingMember', $member->load(['store', 'employeeProfile']));
    })->name('admin.members.edit');
    Route::get('/dashboard/stores', fn () => $adminPage('stores'))->name('admin.stores');
    Route::get('/dashboard/stores/create', fn () => $adminPage('store-create'))->name('admin.stores.create');
    Route::get('/dashboard/stores/{store}/edit', function (Store $store) use ($adminPage) {
        abort_unless((int) $store->getAttribute('tenant_id') === (int) auth()->user()->tenant_id, 404);

        return $adminPage('store-edit')->with('editingStore', $store);
    })->name('admin.stores.edit');
    Route::get('/dashboard/account', fn () => $adminPage('account'))->name('admin.account');
    Route::get('/dashboard/global', function () use ($adminPage) {
        abort_unless(auth()->user()?->isSuperAdmin(), 404);

        $relations = [];
        if (Schema::hasTable('line_login_settings')) {
            $relations[] = 'lineLoginSetting';
        }
        if (Schema::hasTable('line_liff_settings')) {
            $relations[] = 'lineLiffSetting';
        }
        if (Schema::hasTable('line_official_accounts')) {
            $relations[] = 'lineOfficialAccount';
        }

        $globalTenants = Tenant::query()
            ->with($relations)
            ->withCount(['stores', 'members'])
            ->orderBy('id')
            ->get();

        return $adminPage('global-management')->with('globalTenants', $globalTenants);
    })->name('admin.global-management');
    Route::get('/dashboard/global/tenants/{tenant}/line-settings', function (Tenant $tenant) use ($adminPage) {
        abort_unless(auth()->user()?->isSuperAdmin(), 404);

        $lineSettingTablesReady = Schema::hasTable('line_login_settings')
            && Schema::hasTable('line_liff_settings')
            && Schema::hasTable('line_official_accounts');

        if ($lineSettingTablesReady) {
            $tenant->load(['lineLoginSetting', 'lineLiffSetting', 'lineOfficialAccount']);
        }

        return $adminPage('global-line-settings')
            ->with('editingTenant', $tenant)
            ->with('lineSettingTablesReady', $lineSettingTablesReady);
    })->name('admin.global-management.tenants.line-settings');
    Route::put('/dashboard/global/tenants/{tenant}/line-settings', function (Request $request, Tenant $tenant) {
        abort_unless(auth()->user()?->isSuperAdmin(), 404);

        if (! Schema::hasTable('line_login_settings') || ! Schema::hasTable('line_liff_settings') || ! Schema::hasTable('line_official_accounts')) {
            return back()->withErrors(['line_settings' => 'LINE設定テーブルが未作成です。マイグレーション実行後に保存してください。']);
        }

        $payload = $request->validate([
            'setting_type' => ['required', 'string', 'in:line_login,liff,official'],
            'line_login_channel_id' => ['nullable', 'string', 'max:255'],
            'line_login_channel_secret' => ['nullable', 'string'],
            'liff_id' => ['nullable', 'string', 'max:255'],
            'line_official_channel_id' => ['nullable', 'string', 'max:255'],
            'line_official_channel_access_token' => ['nullable', 'string'],
            'line_official_channel_secret' => ['nullable', 'string'],
            'line_official_webhook_url' => ['nullable', 'url', 'max:255'],
            'line_official_line_at_id' => ['nullable', 'string', 'max:255'],
            'line_official_line_timeline_url' => ['nullable', 'url', 'max:255'],
        ]);

        if ($payload['setting_type'] === 'line_login') {
            $lineLoginValues = [
                'channel_id' => $payload['line_login_channel_id'] ?? null,
                'is_active' => true,
            ];
            if ($request->filled('line_login_channel_secret')) {
                $lineLoginValues['channel_secret'] = $payload['line_login_channel_secret'];
            }

            $tenant->lineLoginSetting()->updateOrCreate(['tenant_id' => $tenant->id], $lineLoginValues);
        }

        if ($payload['setting_type'] === 'liff') {
            $tenant->lineLiffSetting()->updateOrCreate(['tenant_id' => $tenant->id], [
                'liff_id' => $payload['liff_id'] ?? null,
                'is_active' => true,
            ]);
        }

        if ($payload['setting_type'] === 'official') {
            $lineOfficialValues = [
                'channel_id' => $payload['line_official_channel_id'] ?? null,
                'webhook_url' => $payload['line_official_webhook_url'] ?? null,
                'line_at_id' => $payload['line_official_line_at_id'] ?? null,
                'line_timeline_url' => $payload['line_official_line_timeline_url'] ?? null,
                'is_active' => true,
            ];
            if ($request->filled('line_official_channel_access_token')) {
                $lineOfficialValues['channel_access_token'] = $payload['line_official_channel_access_token'];
            }
            if ($request->filled('line_official_channel_secret')) {
                $lineOfficialValues['channel_secret'] = $payload['line_official_channel_secret'];
            }

            $tenant->lineOfficialAccount()->updateOrCreate(['tenant_id' => $tenant->id], $lineOfficialValues);
        }

        return redirect()
            ->route('admin.global-management.tenants.line-settings', $tenant)
            ->with('notice', 'LINE設定を保存しました。');
    })->name('admin.global-management.tenants.line-settings.update');
    Route::redirect('/admin/dashboard', '/dashboard')->name('admin.dashboard');
});
