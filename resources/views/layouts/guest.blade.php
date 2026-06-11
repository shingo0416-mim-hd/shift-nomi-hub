<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta name="robots" content="noindex">

        <title>{{ config('app.name', 'ShiftHub') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen bg-slate-100 px-4 py-6">
            <div class="mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-xl flex-col justify-center">
                <div class="mb-6 flex items-center justify-between">
                    <a href="/" class="flex items-center gap-3">
                        <span class="grid size-11 place-items-center rounded-xl bg-slate-950 text-sm font-black text-white shadow-lg shadow-slate-300">SH</span>
                        <span>
                            <span class="block text-base font-black text-slate-950">ShiftHub</span>
                            <span class="block text-xs font-bold text-teal-700">Admin Portal</span>
                        </span>
                    </a>
                    <div class="hidden rounded-full border border-teal-200 bg-white px-3 py-2 text-xs font-black text-teal-700 shadow-sm sm:block">
                        Secure Access
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl bg-white shadow-2xl shadow-slate-300/70 ring-1 ring-slate-200">
                    <main class="w-full bg-white px-6 py-8 sm:px-10 sm:py-10">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
