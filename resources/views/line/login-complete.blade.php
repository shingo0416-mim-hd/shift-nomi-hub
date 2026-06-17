<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title>LINEログイン - {{ config('app.name', 'ShiftHub') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-5 py-8">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200">
                <p class="text-xs font-black text-teal-700">LINE Login</p>
                <h1 class="mt-1 text-2xl font-black text-slate-950">LINEログイン</h1>
                @if (session('line_login_status'))
                    <p class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">{{ session('line_login_status') }}</p>
                @elseif ($errors->any())
                    <p class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">{{ $errors->first() }}</p>
                @else
                    <p class="mt-4 text-sm leading-6 text-slate-600">LINEログイン処理が完了しました。</p>
                @endif
                @if (($canOpenLineAdmin ?? false) && ($lineAdminUrl ?? null))
                    <a href="{{ $lineAdminUrl }}" class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                        LINE管理画面へ
                    </a>
                @endif
            </section>
        </main>
    </body>
</html>
