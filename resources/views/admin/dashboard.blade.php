<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Dashboard - {{ config('app.name', 'ShiftHub') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-950 antialiased">
        <main class="mx-auto flex min-h-screen max-w-5xl flex-col px-6 py-8">
            <header class="flex items-center justify-between border-b border-zinc-200 pb-5">
                <div>
                    <p class="text-sm font-semibold text-teal-700">ShiftHub</p>
                    <h1 class="mt-1 text-2xl font-semibold">管理画面</h1>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">
                        ログアウト
                    </button>
                </form>
            </header>

            <section class="grid flex-1 place-items-center">
                <div class="text-center">
                    <p class="text-lg font-semibold">ログイン済みです。</p>
                    <p class="mt-2 text-sm text-zinc-500">次に店舗・キャスト・シフト管理の画面をここへ追加できます。</p>
                </div>
            </section>
        </main>
    </body>
</html>
