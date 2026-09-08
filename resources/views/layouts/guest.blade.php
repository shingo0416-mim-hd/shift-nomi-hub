<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex">
        <meta name="theme-color" content="#00a3af">
        <title>{{ config('app.name', 'ShiftHub') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="hub-theme hub-auth antialiased">
        <header class="hub-auth-header">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="ShiftHub ホーム">
                <x-brand-mark />
                <span class="hub-wordmark">Shift<span>Hub</span><small>店舗のためのシフト管理</small></span>
            </a>
            <a href="{{ route('home') }}" class="hub-home-link">サービスサイトへ <span aria-hidden="true">↗</span></a>
        </header>
        <div class="hub-auth-layout">
            <aside class="hub-auth-intro" aria-label="ShiftHubのご紹介">
                <p class="hub-eyebrow">FOR YOUR TEAM</p>
                <h1>シフトづくりを、<br><span>もっと軽やかに。</span></h1>
                <p class="hub-auth-description">お店と、働くみんなをつなぐ。<br>希望の収集からシフトの共有まで、<br>毎月の管理をひとつに。</p>
                <ol class="hub-auth-steps"><li><span>01</span> 希望を集める</li><li><span>02</span> シフトをつくる</li><li><span>03</span> みんなに届ける</li></ol>
            </aside>
            <div class="hub-auth-card">
                <main class="w-full px-6 py-8 sm:px-10 sm:py-10">{{ $slot }}</main>
            </div>
        </div>
        <footer class="hub-auth-footer">© {{ date('Y') }} ShiftHub</footer>
    </body>
</html>
