@php
    $menuSections = [
        [
            'label' => 'OPERATIONS',
            'items' => [
                [
                    'label' => 'ダッシュボード',
                    'href' => '#overview',
                    'active' => true,
                    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                ],
                [
                    'label' => 'シフト管理',
                    'href' => '#schedules',
                    'active' => false,
                    'icon' => 'M8 7V3m8 4V3M5 11h14M6 5h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2z',
                ],
                [
                    'label' => 'キャスト管理',
                    'href' => '#members',
                    'active' => false,
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                ],
            ],
        ],
        [
            'label' => 'CONTROL',
            'items' => [
                [
                    'label' => '店舗管理',
                    'href' => '#stores',
                    'active' => false,
                    'icon' => 'M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9h1M9 13h1M9 17h1M14 13h1M14 17h1',
                ],
                [
                    'label' => 'アカウント',
                    'href' => '#account',
                    'active' => false,
                    'icon' => 'M5.121 17.804A8.966 8.966 0 0112 15c2.21 0 4.235.8 5.879 2.128M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 1a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex">

        <title>管理画面 - {{ config('app.name', 'ShiftHub') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
        <div id="adminApp" class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(34,211,238,0.18),transparent_28%),linear-gradient(180deg,#020617_0%,#0f172a_48%,#111827_100%)]">
            <header class="sticky top-0 z-40 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            class="inline-flex size-10 items-center justify-center rounded-xl border border-cyan-300/30 bg-white/10 text-cyan-200 transition hover:bg-cyan-400/10 lg:hidden"
                            data-action="open-mobile-sidebar"
                            aria-controls="app-sidebar-drawer"
                            aria-expanded="false"
                            aria-label="管理メニューを開く"
                        >
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="flex items-center gap-3">
                            <div class="grid size-11 place-items-center rounded-2xl bg-cyan-400 text-sm font-black text-slate-950 shadow-[0_0_34px_rgba(34,211,238,0.26)]">
                                SH
                            </div>
                            <div>
                                <p class="text-base font-black text-white">ShiftHub</p>
                                <p class="text-xs font-semibold text-cyan-200" data-bind="tenantName">ADMIN PORTAL</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm font-bold text-slate-100 transition hover:border-cyan-300/50 hover:bg-cyan-400/10 hover:text-cyan-100" data-action="reload">
                            更新
                        </button>

                        <div class="relative">
                            <button
                                type="button"
                                class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm font-bold text-slate-100 transition hover:border-cyan-300/50 hover:bg-white/15"
                                data-action="toggle-account-menu"
                                aria-expanded="false"
                                aria-haspopup="true"
                            >
                                <span class="hidden max-w-32 truncate sm:inline">{{ Auth::user()->name }}</span>
                                <span class="grid size-7 place-items-center rounded-lg bg-cyan-400 text-xs font-black text-slate-950">{{ mb_substr(Auth::user()->name ?? 'A', 0, 1) }}</span>
                            </button>

                            <div class="absolute right-0 z-50 mt-2 hidden w-56 overflow-hidden rounded-2xl border border-white/10 bg-slate-950/95 py-2 shadow-2xl shadow-black/50 backdrop-blur" data-account-menu>
                                <div class="border-b border-white/10 px-4 pb-3">
                                    <p class="truncate text-sm font-bold text-white">{{ Auth::user()->name }}</p>
                                    <p class="truncate text-xs text-slate-400">{{ Auth::user()->email }}</p>
                                </div>
                                <a href="{{ route('two-factor.settings') }}" class="block px-4 py-2 text-start text-sm font-semibold text-slate-300 transition hover:bg-cyan-400/10 hover:text-cyan-100">
                                    2段階認証設定
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-start text-sm font-semibold text-slate-300 transition hover:bg-cyan-400/10 hover:text-cyan-100">
                                        ログアウト
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="pointer-events-none fixed inset-0 z-50 hidden lg:hidden" data-sidebar-drawer role="dialog" aria-modal="true" aria-label="管理メニュー">
                <button type="button" class="absolute inset-0 bg-slate-950/80 opacity-0 transition-opacity" data-sidebar-backdrop data-action="close-mobile-sidebar" aria-label="メニューを閉じる"></button>
                <aside id="app-sidebar-drawer" class="relative h-full w-80 max-w-[86vw] -translate-x-full overflow-y-auto border-r border-white/10 bg-slate-950 shadow-2xl transition-transform duration-200 ease-out" data-sidebar-panel>
                    <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                        <p class="text-sm font-black text-cyan-200">Navigation</p>
                        <button type="button" class="rounded-lg p-2 text-slate-400 transition hover:bg-white/10 hover:text-cyan-100" data-action="close-mobile-sidebar" aria-label="メニューを閉じる">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <nav class="space-y-6 px-4 py-5" aria-label="管理メニュー">
                        @foreach ($menuSections as $section)
                            <div>
                                <p class="px-3 pb-2 text-xs font-black text-slate-500">{{ $section['label'] }}</p>
                                <div class="space-y-1">
                                    @foreach ($section['items'] as $item)
                                        <a href="{{ $item['href'] }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-bold transition {{ $item['active'] ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-950/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" @if ($item['active']) aria-current="page" @endif data-sidebar-link data-action="close-mobile-sidebar">
                                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                                            </svg>
                                            <span>{{ $item['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </aside>
            </div>

            <div class="lg:flex">
                <aside class="hidden w-64 shrink-0 border-r border-white/10 bg-slate-950/55 backdrop-blur lg:sticky lg:top-16 lg:block lg:h-[calc(100vh-4rem)] lg:overflow-y-auto">
                    <nav class="space-y-7 px-5 py-7" aria-label="管理メニュー">
                        @foreach ($menuSections as $section)
                            <div>
                                <p class="px-3 pb-3 text-xs font-black text-slate-500">{{ $section['label'] }}</p>
                                <div class="space-y-1">
                                    @foreach ($section['items'] as $item)
                                        <a href="{{ $item['href'] }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-bold transition {{ $item['active'] ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-950/30' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" @if ($item['active']) aria-current="page" @endif data-sidebar-link>
                                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                                            </svg>
                                            <span>{{ $item['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </aside>

                <main class="min-w-0 flex-1">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                        <div class="mb-5 hidden rounded-2xl border border-red-400/30 bg-red-950/60 px-4 py-3 text-sm font-semibold text-red-100" data-alert></div>
                        <div class="mb-5 hidden rounded-2xl border border-emerald-400/30 bg-emerald-950/60 px-4 py-3 text-sm font-semibold text-emerald-100" data-notice></div>

                        <section id="overview" class="relative mb-8 overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-[0_24px_90px_rgba(0,0,0,0.32)] backdrop-blur">
                            <div class="absolute inset-y-0 right-0 hidden w-1/2 bg-[radial-gradient(circle_at_center,rgba(34,211,238,0.22),transparent_58%)] lg:block"></div>
                            <div class="relative grid gap-8 p-5 sm:p-7 lg:grid-cols-[minmax(0,1fr)_420px] lg:p-9">
                                <div>
                                    <p class="inline-flex rounded-full bg-cyan-400/10 px-3 py-1 text-xs font-black text-cyan-200 ring-1 ring-cyan-300/20">Shift Operations Network</p>
                                    <h1 class="mt-4 max-w-3xl text-4xl font-black leading-tight text-white sm:text-5xl">管理コンソール</h1>
                                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">
                                        店舗、キャスト、シフト公開状況をひとつの管理ポータルで確認します。
                                    </p>
                                    <div class="mt-7 flex flex-wrap gap-3">
                                        <a href="#schedules" class="rounded-2xl bg-cyan-400 px-5 py-3 text-sm font-black text-slate-950 shadow-lg shadow-cyan-950/30 transition hover:bg-cyan-300">シフトを見る</a>
                                        <a href="#members" class="rounded-2xl border border-white/10 bg-white/10 px-5 py-3 text-sm font-black text-slate-100 transition hover:border-cyan-300/40 hover:bg-white/15">キャスト管理</a>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-2xl border border-white/10 bg-slate-950/55 p-4">
                                        <p class="text-xs font-black text-slate-500">Stores</p>
                                        <p class="mt-2 text-4xl font-black text-white" data-stat="stores">0</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-slate-950/55 p-4">
                                        <p class="text-xs font-black text-slate-500">Members</p>
                                        <p class="mt-2 text-4xl font-black text-white" data-stat="members">0</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-slate-950/55 p-4">
                                        <p class="text-xs font-black text-slate-500">Submitters</p>
                                        <p class="mt-2 text-4xl font-black text-white" data-stat="submitters">0</p>
                                    </div>
                                    <div class="rounded-2xl border border-cyan-300/35 bg-cyan-400/10 p-4">
                                        <p class="text-xs font-black text-cyan-200">Published</p>
                                        <p class="mt-2 text-4xl font-black text-cyan-100" data-stat="published">0</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_390px]">
                            <div class="space-y-6">
                                <section id="schedules" class="overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-xl shadow-black/10 backdrop-blur">
                                    <div class="border-b border-white/10 px-5 py-4">
                                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <p class="text-xs font-black text-cyan-200">Schedule Control</p>
                                                <h2 class="mt-1 text-xl font-black text-white">シフト管理</h2>
                                            </div>
                                            <label class="flex items-center gap-2 text-sm font-bold text-slate-300">
                                                店舗
                                                <select class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition focus:border-cyan-300" data-filter="scheduleStore">
                                                    <option value="">すべて</option>
                                                </select>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-[760px] w-full divide-y divide-white/10 text-sm">
                                            <thead class="bg-slate-950/65 text-left text-xs font-black text-slate-400">
                                                <tr>
                                                    <th class="px-5 py-3">店舗</th>
                                                    <th class="px-5 py-3">期間</th>
                                                    <th class="px-5 py-3">状態</th>
                                                    <th class="px-5 py-3">枠数</th>
                                                    <th class="px-5 py-3 text-right">操作</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-white/10" data-list="schedules"></tbody>
                                        </table>
                                    </div>
                                </section>

                                <section id="members" class="overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-xl shadow-black/10 backdrop-blur">
                                    <div class="border-b border-white/10 px-5 py-4">
                                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <p class="text-xs font-black text-cyan-200">Crew Directory</p>
                                                <h2 class="mt-1 text-xl font-black text-white">キャスト管理</h2>
                                            </div>
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                <label class="flex items-center gap-2 text-sm font-bold text-slate-300">
                                                    店舗
                                                    <select class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition focus:border-cyan-300" data-filter="memberStore">
                                                        <option value="">すべて</option>
                                                    </select>
                                                </label>
                                                <button type="button" class="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-black text-slate-950 transition hover:bg-cyan-300" data-action="open-member-modal">
                                                    キャストを追加
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-[900px] w-full divide-y divide-white/10 text-sm">
                                            <thead class="bg-slate-950/65 text-left text-xs font-black text-slate-400">
                                                <tr>
                                                    <th class="px-5 py-3">氏名</th>
                                                    <th class="px-5 py-3">店舗</th>
                                                    <th class="px-5 py-3">連絡先</th>
                                                    <th class="px-5 py-3">状態</th>
                                                    <th class="px-5 py-3">提出</th>
                                                    <th class="px-5 py-3">備考</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-white/10" data-list="members"></tbody>
                                        </table>
                                    </div>
                                </section>
                            </div>

                            <div class="space-y-6">
                                <section id="stores" class="rounded-3xl border border-white/10 bg-white/10 p-5 shadow-xl shadow-black/10 backdrop-blur">
                                    <p class="text-xs font-black text-cyan-200">Store Registry</p>
                                    <h2 class="mt-1 text-xl font-black text-white">店舗登録</h2>
                                    <form class="mt-5 space-y-4" data-form="store">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-300">店舗名</label>
                                            <input name="name" required class="mt-2 min-h-11 w-full rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-300">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-300">住所</label>
                                            <input name="address" class="mt-2 min-h-11 w-full rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-cyan-300">
                                        </div>
                                        <button class="w-full rounded-xl bg-cyan-400 px-4 py-3 text-sm font-black text-slate-950 transition hover:bg-cyan-300">店舗を追加</button>
                                    </form>
                                    <div class="mt-5 divide-y divide-white/10" data-list="stores"></div>
                                </section>

                                <section class="rounded-3xl border border-white/10 bg-white/10 p-5 shadow-xl shadow-black/10 backdrop-blur">
                                    <p class="text-xs font-black text-cyan-200">Create Schedule</p>
                                    <h2 class="mt-1 text-xl font-black text-white">シフト表作成</h2>
                                    <form class="mt-5 space-y-4" data-form="schedule">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-300">店舗</label>
                                            <select name="store_id" required class="mt-2 min-h-11 w-full rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-white outline-none transition focus:border-cyan-300"></select>
                                        </div>
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-bold text-slate-300">開始日</label>
                                                <input name="starts_on" type="date" required class="mt-2 min-h-11 w-full rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-white outline-none transition focus:border-cyan-300">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-300">終了日</label>
                                                <input name="ends_on" type="date" required class="mt-2 min-h-11 w-full rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-white outline-none transition focus:border-cyan-300">
                                            </div>
                                        </div>
                                        <button class="w-full rounded-xl border border-cyan-300/30 bg-cyan-400/10 px-4 py-3 text-sm font-black text-cyan-100 transition hover:bg-cyan-400 hover:text-slate-950">シフト表を作成</button>
                                    </form>
                                </section>

                                <section id="account" class="rounded-3xl border border-white/10 bg-white/10 p-5 shadow-xl shadow-black/10 backdrop-blur">
                                    <p class="text-xs font-black text-cyan-200">Account Security</p>
                                    <h2 class="mt-1 text-xl font-black text-white">アカウント</h2>
                                    <p class="mt-3 text-sm leading-6 text-slate-400">管理ログインは2段階認証で保護されています。</p>
                                    <a href="{{ route('two-factor.settings') }}" class="mt-4 inline-flex rounded-xl border border-white/10 px-4 py-2 text-sm font-black text-slate-100 transition hover:border-cyan-300/40 hover:text-cyan-100">
                                        2段階認証設定
                                    </a>
                                </section>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 px-4 py-6 backdrop-blur" data-member-modal role="dialog" aria-modal="true" aria-labelledby="member-modal-title">
            <div class="w-full max-w-xl overflow-hidden rounded-3xl border border-white/10 bg-slate-950 shadow-2xl shadow-black/50">
                <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                    <div>
                        <p class="text-xs font-black text-cyan-200">Crew Entry</p>
                        <h2 id="member-modal-title" class="mt-1 text-xl font-black text-white">キャスト登録</h2>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-slate-400 transition hover:bg-white/10 hover:text-cyan-100" data-action="close-member-modal" aria-label="閉じる">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form class="space-y-4 px-5 py-5" data-form="member">
                    <div>
                        <label class="block text-sm font-bold text-slate-300">氏名</label>
                        <input name="name" required class="mt-2 min-h-11 w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-sm text-white outline-none transition focus:border-cyan-300">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-300">店舗</label>
                        <select name="store_id" class="mt-2 min-h-11 w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-sm text-white outline-none transition focus:border-cyan-300">
                            <option value="">未割り当て</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-bold text-slate-300">電話</label>
                            <input name="phone" class="mt-2 min-h-11 w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-sm text-white outline-none transition focus:border-cyan-300">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-300">メール</label>
                            <input name="email" type="email" class="mt-2 min-h-11 w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-sm text-white outline-none transition focus:border-cyan-300">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-300">備考</label>
                        <textarea name="remarks" rows="3" class="mt-2 w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-sm text-white outline-none transition focus:border-cyan-300"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 border-t border-white/10 pt-4">
                        <button type="button" class="rounded-xl border border-white/10 bg-slate-950 px-4 py-2 text-sm font-black text-slate-200 transition hover:border-cyan-300/40" data-action="close-member-modal">
                            キャンセル
                        </button>
                        <button class="rounded-xl bg-cyan-400 px-4 py-2 text-sm font-black text-slate-950 transition hover:bg-cyan-300">
                            キャストを追加
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (() => {
                const state = {
                    stores: [],
                    members: [],
                    schedules: [],
                    user: @json(Auth::user()->load('tenant')),
                };

                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const $ = (selector) => document.querySelector(selector);
                const $$ = (selector) => Array.from(document.querySelectorAll(selector));

                const openMobileSidebar = () => {
                    const drawer = $('[data-sidebar-drawer]');
                    const panel = $('[data-sidebar-panel]');
                    const backdrop = $('[data-sidebar-backdrop]');
                    const trigger = $('[data-action="open-mobile-sidebar"]');

                    drawer?.classList.remove('hidden', 'pointer-events-none');
                    drawer?.classList.add('pointer-events-auto');
                    requestAnimationFrame(() => {
                        panel?.classList.remove('-translate-x-full');
                        backdrop?.classList.remove('opacity-0');
                    });
                    trigger?.setAttribute('aria-expanded', 'true');
                };

                const closeMobileSidebar = () => {
                    const drawer = $('[data-sidebar-drawer]');
                    const panel = $('[data-sidebar-panel]');
                    const backdrop = $('[data-sidebar-backdrop]');
                    const trigger = $('[data-action="open-mobile-sidebar"]');

                    panel?.classList.add('-translate-x-full');
                    backdrop?.classList.add('opacity-0');
                    drawer?.classList.remove('pointer-events-auto');
                    drawer?.classList.add('pointer-events-none');
                    window.setTimeout(() => drawer?.classList.add('hidden'), 180);
                    trigger?.setAttribute('aria-expanded', 'false');
                };

                const closeAccountMenu = () => {
                    $('[data-account-menu]')?.classList.add('hidden');
                    $('[data-action="toggle-account-menu"]')?.setAttribute('aria-expanded', 'false');
                };

                const toggleAccountMenu = () => {
                    const menu = $('[data-account-menu]');
                    const trigger = $('[data-action="toggle-account-menu"]');
                    const nextOpen = menu?.classList.contains('hidden') ?? false;

                    menu?.classList.toggle('hidden', !nextOpen);
                    trigger?.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
                };

                const openMemberModal = () => {
                    $('[data-member-modal]')?.classList.remove('hidden');
                    $('[data-member-modal]')?.classList.add('flex');
                    $('[data-member-modal] input[name="name"]')?.focus();
                };

                const closeMemberModal = () => {
                    $('[data-member-modal]')?.classList.add('hidden');
                    $('[data-member-modal]')?.classList.remove('flex');
                };

                const setSidebarActive = (targetHash = window.location.hash || '#overview') => {
                    const hash = targetHash || '#overview';

                    $$('[data-sidebar-link]').forEach((link) => {
                        const isActive = link.getAttribute('href') === hash;

                        link.classList.toggle('bg-cyan-400', isActive);
                        link.classList.toggle('text-slate-950', isActive);
                        link.classList.toggle('shadow-lg', isActive);
                        link.classList.toggle('shadow-cyan-950/30', isActive);
                        link.classList.toggle('text-slate-300', !isActive);
                        link.classList.toggle('hover:bg-white/10', !isActive);
                        link.classList.toggle('hover:text-white', !isActive);

                        if (isActive) {
                            link.setAttribute('aria-current', 'page');
                        } else {
                            link.removeAttribute('aria-current');
                        }
                    });
                };

                const api = async (path, options = {}) => {
                    const response = await fetch(path, {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(options.headers || {}),
                        },
                        ...options,
                    });

                    const text = await response.text();
                    const data = text ? JSON.parse(text) : {};
                    if (!response.ok) {
                        const message = data.message || Object.values(data.errors || {}).flat().join('\n') || '処理に失敗しました。';
                        throw new Error(message);
                    }

                    return data;
                };

                const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                }[char]));

                const badge = (status) => {
                    const label = status === 'published' ? '公開済み' : status === 'archived' ? 'アーカイブ' : '下書き';
                    const klass = status === 'published' ? 'border-emerald-400/30 bg-emerald-400/10 text-emerald-200' : status === 'archived' ? 'border-slate-600 bg-slate-800 text-slate-400' : 'border-cyan-300/30 bg-cyan-400/10 text-cyan-200';
                    return `<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black ${klass}">${label}</span>`;
                };

                const setMessage = (selector, message) => {
                    const element = $(selector);
                    element.textContent = message;
                    element.classList.toggle('hidden', !message);
                    if (message) {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                };

                const renderSelects = () => {
                    const options = state.stores.map((store) => `<option value="${store.id}">${escapeHtml(store.name)}</option>`).join('');
                    $$('select[name="store_id"]').forEach((select) => {
                        const first = select.querySelector('option[value=""]') ? '<option value="">未割り当て</option>' : '';
                        select.innerHTML = first + options;
                    });
                    $$('[data-filter="memberStore"], [data-filter="scheduleStore"]').forEach((select) => {
                        const current = select.value;
                        select.innerHTML = `<option value="">すべて</option>${options}`;
                        select.value = current;
                    });
                };

                const renderStores = () => {
                    $('[data-list="stores"]').innerHTML = state.stores.length
                        ? state.stores.map((store) => `
                            <div class="py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-black text-white">${escapeHtml(store.name)}</p>
                                        <p class="mt-1 text-sm text-slate-400">${escapeHtml(store.address || '住所未登録')}</p>
                                    </div>
                                    <span class="rounded-full border ${store.is_active ? 'border-emerald-400/30 bg-emerald-400/10 text-emerald-200' : 'border-slate-700 bg-slate-900 text-slate-500'} px-2.5 py-1 text-xs font-black">
                                        ${store.is_active ? '稼働中' : '停止中'}
                                    </span>
                                </div>
                            </div>
                        `).join('')
                        : '<p class="py-4 text-sm text-slate-500">店舗がまだ登録されていません。</p>';
                };

                const renderMembers = () => {
                    const storeId = $('[data-filter="memberStore"]').value;
                    const members = storeId ? state.members.filter((member) => String(member.store_id || '') === storeId) : state.members;
                    $('[data-list="members"]').innerHTML = members.length
                        ? members.map((member) => `
                            <tr class="transition hover:bg-white/10">
                                <td class="px-5 py-4 font-black text-white">${escapeHtml(member.name)}</td>
                                <td class="px-5 py-4 text-slate-300">${escapeHtml(member.store?.name || '未割り当て')}</td>
                                <td class="px-5 py-4 text-slate-300">
                                    <div>${escapeHtml(member.phone || '-')}</div>
                                    <div class="text-xs text-slate-500">${escapeHtml(member.email || '')}</div>
                                </td>
                                <td class="px-5 py-4"><span class="rounded-full border border-sky-400/30 bg-sky-400/10 px-2.5 py-1 text-xs font-black text-sky-200">${escapeHtml(member.status || 'active')}</span></td>
                                <td class="px-5 py-4 text-slate-300">${member.is_shift_submitter ? '対象' : '対象外'}</td>
                                <td class="max-w-xs truncate px-5 py-4 text-slate-500">${escapeHtml(member.remarks || '-')}</td>
                            </tr>
                        `).join('')
                        : '<tr><td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">条件に一致するキャストがいません。</td></tr>';
                };

                const renderSchedules = () => {
                    const storeId = $('[data-filter="scheduleStore"]').value;
                    const schedules = storeId ? state.schedules.filter((schedule) => String(schedule.store_id || '') === storeId) : state.schedules;
                    $('[data-list="schedules"]').innerHTML = schedules.length
                        ? schedules.map((schedule) => `
                            <tr class="transition hover:bg-white/10">
                                <td class="px-5 py-4 font-black text-white">${escapeHtml(schedule.store?.name || '-')}</td>
                                <td class="px-5 py-4 text-slate-300">${escapeHtml(schedule.starts_on)} - ${escapeHtml(schedule.ends_on)}</td>
                                <td class="px-5 py-4">${badge(schedule.status)}</td>
                                <td class="px-5 py-4 text-slate-300">${schedule.shift_slots?.length || 0}</td>
                                <td class="px-5 py-4 text-right">
                                    ${schedule.status === 'published'
                                        ? '<span class="text-xs font-black text-slate-500">公開済み</span>'
                                        : `<button type="button" class="rounded-xl border border-cyan-300/40 px-3 py-1.5 text-xs font-black text-cyan-100 transition hover:bg-cyan-400 hover:text-slate-950" data-publish="${schedule.id}">公開</button>`}
                                </td>
                            </tr>
                        `).join('')
                        : '<tr><td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">シフト表がまだありません。</td></tr>';
                };

                const renderStats = () => {
                    $('[data-bind="tenantName"]').textContent = state.user.tenant?.name || 'ADMIN PORTAL';
                    $('[data-stat="stores"]').textContent = state.stores.length;
                    $('[data-stat="members"]').textContent = state.members.length;
                    $('[data-stat="submitters"]').textContent = state.members.filter((member) => member.is_shift_submitter).length;
                    $('[data-stat="published"]').textContent = state.schedules.filter((schedule) => schedule.status === 'published').length;
                };

                const render = () => {
                    renderSelects();
                    renderStores();
                    renderMembers();
                    renderSchedules();
                    renderStats();
                };

                const load = async () => {
                    setMessage('[data-alert]', '');
                    const [me, stores, members, schedules] = await Promise.all([
                        api('/api/admin/auth/me'),
                        api('/api/admin/stores'),
                        api('/api/admin/members?per_page=100'),
                        api('/api/admin/shift-schedules?per_page=100'),
                    ]);

                    state.user = me.user;
                    state.stores = stores.stores || [];
                    state.members = members.data || [];
                    state.schedules = schedules.data || [];
                    render();
                };

                const formPayload = (form) => {
                    const payload = Object.fromEntries(new FormData(form).entries());
                    Object.keys(payload).forEach((key) => {
                        if (payload[key] === '') {
                            delete payload[key];
                        }
                    });
                    return payload;
                };

                $('[data-form="store"]').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    try {
                        await api('/api/admin/stores', { method: 'POST', body: JSON.stringify({ timezone: 'Asia/Tokyo', is_active: true, ...formPayload(event.currentTarget) }) });
                        event.currentTarget.reset();
                        await load();
                        setMessage('[data-notice]', '店舗を追加しました。');
                    } catch (error) {
                        setMessage('[data-alert]', error.message);
                    }
                });

                $('[data-form="member"]').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    try {
                        await api('/api/admin/members', { method: 'POST', body: JSON.stringify({ status: 'active', is_shift_submitter: true, ...formPayload(event.currentTarget) }) });
                        event.currentTarget.reset();
                        closeMemberModal();
                        await load();
                        setMessage('[data-notice]', 'キャストを追加しました。');
                    } catch (error) {
                        setMessage('[data-alert]', error.message);
                    }
                });

                $('[data-form="schedule"]').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    try {
                        await api('/api/admin/shift-schedules', { method: 'POST', body: JSON.stringify({ status: 'draft', ...formPayload(event.currentTarget) }) });
                        event.currentTarget.reset();
                        await load();
                        setMessage('[data-notice]', 'シフト表を作成しました。');
                    } catch (error) {
                        setMessage('[data-alert]', error.message);
                    }
                });

                document.addEventListener('click', async (event) => {
                    const publishButton = event.target.closest('[data-publish]');
                    if (publishButton) {
                        try {
                            await api(`/api/admin/shift-schedules/${publishButton.dataset.publish}/publish`, { method: 'POST', body: '{}' });
                            await load();
                            setMessage('[data-notice]', 'シフト表を公開しました。');
                        } catch (error) {
                            setMessage('[data-alert]', error.message);
                        }
                    }

                    if (event.target.closest('[data-action="reload"]')) {
                        await load();
                        setMessage('[data-notice]', '最新データに更新しました。');
                    }

                    const sidebarLink = event.target.closest('[data-sidebar-link]');
                    if (sidebarLink) {
                        setSidebarActive(sidebarLink.getAttribute('href'));
                    }

                    if (event.target.closest('[data-action="toggle-account-menu"]')) {
                        toggleAccountMenu();
                        return;
                    }

                    if (!event.target.closest('[data-account-menu]')) {
                        closeAccountMenu();
                    }

                    if (event.target.closest('[data-action="open-member-modal"]')) {
                        openMemberModal();
                    }

                    if (event.target.closest('[data-action="close-member-modal"]')) {
                        closeMemberModal();
                    }

                    if (event.target === $('[data-member-modal]')) {
                        closeMemberModal();
                    }

                    if (event.target.closest('[data-action="open-mobile-sidebar"]')) {
                        openMobileSidebar();
                    }

                    if (event.target.closest('[data-action="close-mobile-sidebar"]')) {
                        closeMobileSidebar();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeMobileSidebar();
                        closeAccountMenu();
                        closeMemberModal();
                    }
                });

                window.addEventListener('hashchange', () => setSidebarActive());

                $$('[data-filter="memberStore"], [data-filter="scheduleStore"]').forEach((select) => {
                    select.addEventListener('change', render);
                });

                setSidebarActive();
                load().catch((error) => setMessage('[data-alert]', error.message));
            })();
        </script>
    </body>
</html>
