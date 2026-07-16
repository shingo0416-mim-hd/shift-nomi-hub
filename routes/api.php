<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Api\Admin\ShiftScheduleController as AdminShiftScheduleController;
use App\Http\Controllers\Api\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Api\Admin\TenantSettingsController as AdminTenantSettingsController;
use App\Http\Controllers\Api\DeployController;
use App\Http\Controllers\Api\Liff\AuthController as LiffAuthController;
use App\Http\Controllers\Api\Liff\AvailabilityController as LiffAvailabilityController;
use App\Http\Controllers\Api\Liff\ShiftScheduleController as LiffShiftScheduleController;
use Illuminate\Support\Facades\Route;

// 自動デプロイ
Route::post('/deploy', [DeployController::class, 'deploy'])->name('deploy');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::post('/auth/login', [AdminAuthController::class, 'login'])->name('auth.login');

    Route::middleware(['auth:sanctum', 'ability:admin'])->group(function (): void {
        Route::get('/auth/me', [AdminAuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AdminAuthController::class, 'logout'])->name('auth.logout');
        Route::put('/tenant/settings', [AdminTenantSettingsController::class, 'update'])->name('tenant.settings.update');

        Route::apiResource('stores', AdminStoreController::class)->only(['index', 'store', 'update']);
        Route::apiResource('members', AdminMemberController::class)->only(['index', 'store', 'update']);
        Route::get('/members/{member}/registration-qr', [AdminMemberController::class, 'registrationQr'])
            ->name('members.registration-qr');
        Route::apiResource('shift-schedules', AdminShiftScheduleController::class)->only(['index', 'store', 'update']);
        Route::post('/shift-schedules/{shiftSchedule}/publish', [AdminShiftScheduleController::class, 'publish'])
            ->name('shift-schedules.publish');
    });
});

Route::prefix('liff')->name('liff.')->group(function (): void {
    Route::post('/auth/login', [LiffAuthController::class, 'login'])->name('auth.login');

    Route::middleware(['auth:sanctum', 'ability:liff'])->group(function (): void {
        Route::get('/availability-requests', [LiffAvailabilityController::class, 'index'])
            ->name('availability-requests.index');
        Route::post('/availability-requests', [LiffAvailabilityController::class, 'store'])
            ->name('availability-requests.store');
        Route::get('/shift-schedules', [LiffShiftScheduleController::class, 'index'])
            ->name('shift-schedules.index');
    });
});
