<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::redirect('/admin', '/dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');
    Route::get('/admin/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');
});
