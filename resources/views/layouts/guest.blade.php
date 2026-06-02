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
    <body class="font-sans text-slate-800 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-b from-slate-50 via-blue-50 to-blue-100">
            <div class="flex flex-col items-center space-y-3 text-center">
                <a href="/">
                    <x-application-logo class="w-20 h-auto text-blue-600" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-8 py-9 bg-white/90 border border-blue-100 shadow-lg shadow-blue-200/40 backdrop-blur-sm overflow-hidden sm:rounded-[2rem]">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
