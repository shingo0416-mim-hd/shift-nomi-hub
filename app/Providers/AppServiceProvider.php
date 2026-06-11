<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        // Carbonの言語を日本語にセット
        \Carbon\Carbon::setLocale('ja');

        // ユーザー向けのレイアウトコンポーネントを登録
        Blade::component('layouts.user', 'user-layout');
        Blade::component('layouts.guest', 'guest-layout');
    }
}
