<?php

use App\Http\Controllers\Liff\RegistrationController;
use App\Models\Member;
use App\Models\ShiftSchedule;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Route;

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

        return view('admin.dashboard', [
            'page' => $page,
            'initialData' => [
                'stores' => $stores,
                'members' => $members,
                'schedules' => $schedules,
                'user' => $user->load('tenant'),
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
    Route::redirect('/admin/dashboard', '/dashboard')->name('admin.dashboard');
});
