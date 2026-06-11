<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::redirect('/admin', '/dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/two-factor-settings', fn () => view('auth.two-factor-settings'))->name('two-factor.settings');

    Route::get('/dashboard', function () {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor.settings');
        }

        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/admin/dashboard', function () {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor.settings');
        }

        return view('admin.dashboard');
    })->name('admin.dashboard');
});
