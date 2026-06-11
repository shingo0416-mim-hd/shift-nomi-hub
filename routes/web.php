<?php

use App\Models\Member;
use App\Models\ShiftSchedule;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::redirect('/admin', '/dashboard');

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
    Route::get('/dashboard/stores', fn () => $adminPage('stores'))->name('admin.stores');
    Route::get('/dashboard/stores/create', fn () => $adminPage('store-create'))->name('admin.stores.create');
    Route::get('/dashboard/account', fn () => $adminPage('account'))->name('admin.account');
    Route::redirect('/admin/dashboard', '/dashboard')->name('admin.dashboard');
});
