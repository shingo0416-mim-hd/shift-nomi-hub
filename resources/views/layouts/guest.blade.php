<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ShiftHub') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased">
        <main class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_480px]">
            <section class="hidden bg-[radial-gradient(circle_at_20%_20%,rgba(20,184,166,0.22),transparent_28%),linear-gradient(135deg,#09090b_0%,#18181b_52%,#111827_100%)] px-12 py-10 lg:flex lg:flex-col lg:justify-between">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-lg bg-teal-400 text-sm font-bold text-zinc-950">SH</div>
                    <div>
                        <p class="text-sm font-semibold text-white">ShiftHub</p>
                        <p class="text-xs text-zinc-400">nomihub.jp</p>
                    </div>
                </div>

                <div class="max-w-xl">
                    <p class="mb-5 text-sm font-medium text-teal-200">AI shift operations</p>
                    <h1 class="text-5xl font-semibold leading-tight text-white">夜職店舗のシフト作成を、速く正確に。</h1>
                    <p class="mt-6 max-w-lg text-base leading-8 text-zinc-300">
                        キャスト希望、店舗条件、配置バランスを整理し、管理者が調整しやすいシフト運用に集約します。
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-3 text-sm text-zinc-300">
                    <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                        <p class="text-xl font-semibold text-white">01</p>
                        <p class="mt-2">希望提出</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                        <p class="text-xl font-semibold text-white">02</p>
                        <p class="mt-2">AIドラフト</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                        <p class="text-xl font-semibold text-white">03</p>
                        <p class="mt-2">LINE通知</p>
                    </div>
                </div>
            </section>

            <section class="flex min-h-screen items-center justify-center bg-zinc-50 px-6 py-10 text-zinc-950">
                {{ $slot }}
            </section>
        </main>
    </body>
</html>
