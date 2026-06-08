@php
    $menuSections = [
        [
            'label' => '業務',
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
            'label' => '管理',
            'items' => [
                [
                    'label' => '店舗管理',
                    'href' => '#stores',
                    'active' => false,
                    'icon' => 'M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9h1M9 13h1M9 17h1M14 13h1M14 17h1',
                ],
                [
                    'label' => 'アカウント',
                    'href' => '#overview',
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
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
        <div id="adminApp" class="min-h-screen">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <div class="grid size-10 place-items-center rounded-lg bg-teal-600 text-sm font-bold text-white lg:hidden">
                            SH
                        </div>
                        <div class="hidden size-10 place-items-center rounded-lg bg-teal-600 text-sm font-bold text-white lg:grid">SH</div>
                        <div>
                            <p class="text-sm font-semibold text-slate-950">ShiftHub</p>
                            <p class="text-xs text-slate-500" data-bind="tenantName">管理画面</p>
                        </div>
                    </div>

                    <div class="flex min-w-0 items-center gap-3">
                        <div class="hidden min-w-0 items-center sm:flex">
                            <p class="truncate text-base text-gray-800">
                                {{ Auth::user()->name }}
                            </p>

                            <svg width="30" height="30" viewBox="0 0 29.5 29.5" class="ml-2.5 mr-1 shrink-0" aria-hidden="true">
                                <path d="M14.749,0A14.75,14.75,0,1,0,29.5,14.75,14.755,14.755,0,0,0,14.749,0Zm0,4.425A4.425,4.425,0,1,1,10.324,8.85a4.419,4.419,0,0,1,4.424-4.425Zm0,20.945A10.621,10.621,0,0,1,5.9,20.62c.044-2.936,5.9-4.543,8.849-4.543s8.805,1.607,8.849,4.543a10.62,10.62,0,0,1-8.849,4.75Z" style="fill: #2997e7; fill-rule: evenodd;"/>
                            </svg>
                        </div>
                        <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50" data-action="reload">
                            更新
                        </button>
                        <div class="relative">
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                data-action="toggle-account-menu"
                                aria-expanded="false"
                                aria-haspopup="true"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 19.454 20" aria-hidden="true">
                                    <path d="M19.43,12.98A7.793,7.793,0,0,0,19.5,12a7.793,7.793,0,0,0-.07-.98l2.11-1.65a.5.5,0,0,0,.12-.64l-2-3.46a.5.5,0,0,0-.61-.22l-2.49,1a7.306,7.306,0,0,0-1.69-.98l-.38-2.65A.488.488,0,0,0,14,2H10a.488.488,0,0,0-.49.42L9.13,5.07a7.683,7.683,0,0,0-1.69.98l-2.49-1a.488.488,0,0,0-.61.22l-2,3.46a.493.493,0,0,0,.12.64l2.11,1.65A7.931,7.931,0,0,0,4.5,12a7.931,7.931,0,0,0,.07.98L2.46,14.63a.5.5,0,0,0-.12.64l2,3.46a.5.5,0,0,0,.61.22l2.49-1a7.306,7.306,0,0,0,1.69.98l.38,2.65A.488.488,0,0,0,10,22h4a.488.488,0,0,0,.49-.42l.38-2.65a7.683,7.683,0,0,0,1.69-.98l2.49,1a.488.488,0,0,0,.61-.22l2-3.46a.5.5,0,0,0-.12-.64ZM12,15.5A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" transform="translate(-2.271 -2)" fill="currentColor"/>
                                </svg>
                                <span class="sr-only">アカウントメニュー</span>
                            </button>

                            <div
                                class="absolute right-0 z-50 mt-2 hidden w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5"
                                data-account-menu
                            >
                                <div class="px-4 py-3 sm:hidden">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none">
                                        ログアウト
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="sticky top-16 z-20 border-b border-gray-200 bg-white px-4 py-3 shadow-sm lg:hidden">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    data-action="open-mobile-sidebar"
                    aria-controls="app-sidebar-drawer"
                    aria-expanded="false"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>管理メニュー</span>
                </button>
            </div>

            <div
                class="pointer-events-none fixed inset-0 z-40 hidden lg:hidden"
                data-sidebar-drawer
                role="dialog"
                aria-modal="true"
                aria-label="管理メニュー"
            >
                <button
                    type="button"
                    class="absolute inset-0 bg-gray-900/40 opacity-0 transition-opacity"
                    data-sidebar-backdrop
                    data-action="close-mobile-sidebar"
                    aria-label="メニューを閉じる"
                ></button>

                <aside
                    id="app-sidebar-drawer"
                    class="relative h-full w-80 max-w-[86vw] -translate-x-full overflow-y-auto border-r border-gray-200 bg-white shadow-2xl transition-transform duration-200 ease-out"
                    data-sidebar-panel
                >
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                        <p class="text-sm font-semibold text-gray-700">管理メニュー</p>
                        <button
                            type="button"
                            class="rounded-md p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                            data-action="close-mobile-sidebar"
                            aria-label="メニューを閉じる"
                            title="閉じる"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <nav class="space-y-6 px-4 py-5 sm:px-6" aria-label="管理メニュー">
                        @foreach ($menuSections as $section)
                            <div>
                                <p class="px-4 pb-2 text-xs font-bold uppercase tracking-wider text-gray-400">{{ $section['label'] }}</p>
                                <div class="space-y-2">
                                    @foreach ($section['items'] as $item)
                                        <a
                                            href="{{ $item['href'] }}"
                                            class="flex items-center gap-4 rounded-lg px-4 py-4 text-base font-semibold transition {{ $item['active'] ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
                                            @if ($item['active']) aria-current="page" @endif
                                            data-sidebar-link
                                            data-action="close-mobile-sidebar"
                                        >
                                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
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
                <aside
                    id="app-sidebar-desktop-collapsed"
                    class="admin-desktop-sidebar-collapsed is-hidden bg-white lg:sticky lg:top-16 lg:z-20 lg:h-[calc(100vh-4rem)] lg:w-12 lg:shrink-0 lg:flex-col lg:items-center lg:overflow-y-auto lg:border-r lg:border-gray-300 lg:shadow-[6px_0_18px_rgba(31,41,55,0.04)]"
                    data-desktop-sidebar-collapsed
                    aria-label="管理メニュー"
                >
                    <div class="flex w-full justify-center px-1 pt-2">
                        <button
                            type="button"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                            data-action="expand-desktop-sidebar"
                            aria-controls="app-sidebar-desktop"
                            aria-expanded="false"
                            aria-label="サイドメニューを開く"
                            title="開く"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <nav class="mt-8 flex w-full flex-col items-center gap-3 px-1" aria-label="管理メニュー">
                        @foreach ($menuSections as $sectionIndex => $section)
                            @if ($sectionIndex > 0)
                                <span class="my-1 h-px w-8 bg-gray-200" aria-hidden="true"></span>
                            @endif
                            @foreach ($section['items'] as $item)
                                <a
                                    href="{{ $item['href'] }}"
                                    class="flex h-10 w-10 items-center justify-center rounded-lg transition {{ $item['active'] ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
                                    @if ($item['active']) aria-current="page" @endif
                                    aria-label="{{ $item['label'] }}"
                                    title="{{ $section['label'] }}: {{ $item['label'] }}"
                                    data-sidebar-link
                                >
                                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                                    </svg>
                                </a>
                            @endforeach
                        @endforeach
                    </nav>
                </aside>

                <aside
                    id="app-sidebar-desktop"
                    class="admin-desktop-sidebar-expanded bg-white lg:sticky lg:top-16 lg:z-20 lg:h-[calc(100vh-4rem)] lg:w-60 lg:shrink-0 lg:overflow-y-auto lg:border-r lg:border-gray-300 lg:shadow-[6px_0_18px_rgba(31,41,55,0.04)]"
                    data-desktop-sidebar
                    aria-label="管理メニュー"
                >
                    <div class="flex justify-end px-3 pt-2">
                        <button
                            type="button"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                            data-action="collapse-desktop-sidebar"
                            aria-label="サイドメニューを閉じる"
                            title="閉じる"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>

                    <nav class="space-y-6 px-6 pb-8 pt-3" aria-label="管理メニュー">
                        @foreach ($menuSections as $section)
                            <div>
                                <p class="px-3 pb-2 text-xs font-bold uppercase tracking-wider text-gray-400">{{ $section['label'] }}</p>
                                <div class="space-y-2">
                                    @foreach ($section['items'] as $item)
                                        <a
                                            href="{{ $item['href'] }}"
                                            class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-semibold leading-tight transition {{ $item['active'] ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}"
                                            @if ($item['active']) aria-current="page" @endif
                                            data-sidebar-link
                                        >
                                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                                            </svg>
                                            <span class="min-w-0 whitespace-nowrap">{{ $item['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>
                </aside>

                <main class="min-w-0 flex-1">
                    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                        <div class="mb-6 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" data-alert></div>
                        <div class="mb-6 hidden rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" data-notice></div>

                        <section id="overview" class="mb-8">
                            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-teal-700">Shift operations</p>
                                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">管理ダッシュボード</h1>
                                </div>
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                        <p class="text-xs font-semibold text-slate-500">店舗</p>
                                        <p class="mt-1 text-2xl font-semibold" data-stat="stores">0</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                        <p class="text-xs font-semibold text-slate-500">キャスト</p>
                                        <p class="mt-1 text-2xl font-semibold" data-stat="members">0</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                        <p class="text-xs font-semibold text-slate-500">提出対象</p>
                                        <p class="mt-1 text-2xl font-semibold" data-stat="submitters">0</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                        <p class="text-xs font-semibold text-slate-500">公開済み</p>
                                        <p class="mt-1 text-2xl font-semibold" data-stat="published">0</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
                            <div class="space-y-6">
                                <section id="schedules" class="rounded-lg border border-slate-200 bg-white shadow-sm">
                                    <div class="border-b border-slate-200 px-5 py-4">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <h2 class="text-lg font-semibold text-slate-950">シフト管理</h2>
                                                <p class="mt-1 text-sm text-slate-500">期間単位のシフト表を作成・公開します。</p>
                                            </div>
                                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                                店舗
                                                <select class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100" data-filter="scheduleStore">
                                                    <option value="">すべて</option>
                                                </select>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-[760px] w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500">
                                                <tr>
                                                    <th class="px-5 py-3">店舗</th>
                                                    <th class="px-5 py-3">期間</th>
                                                    <th class="px-5 py-3">状態</th>
                                                    <th class="px-5 py-3">枠数</th>
                                                    <th class="px-5 py-3 text-right">操作</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100" data-list="schedules"></tbody>
                                        </table>
                                    </div>
                                </section>

                                <section id="members" class="rounded-lg border border-slate-200 bg-white shadow-sm">
                                    <div class="border-b border-slate-200 px-5 py-4">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <h2 class="text-lg font-semibold text-slate-950">キャスト管理</h2>
                                                <p class="mt-1 text-sm text-slate-500">シフト提出対象者と連絡先を管理します。</p>
                                            </div>
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                <label class="flex items-center gap-2 text-sm text-slate-600">
                                                    店舗
                                                    <select class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100" data-filter="memberStore">
                                                        <option value="">すべて</option>
                                                    </select>
                                                </label>
                                                <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700" data-action="open-member-modal">
                                                    キャストを追加
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-[900px] w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500">
                                                <tr>
                                                    <th class="px-5 py-3">氏名</th>
                                                    <th class="px-5 py-3">店舗</th>
                                                    <th class="px-5 py-3">連絡先</th>
                                                    <th class="px-5 py-3">状態</th>
                                                    <th class="px-5 py-3">提出</th>
                                                    <th class="px-5 py-3">備考</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100" data-list="members"></tbody>
                                        </table>
                                    </div>
                                </section>
                            </div>

                            <div class="space-y-6">
                                <section id="stores" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                                    <h2 class="text-lg font-semibold text-slate-950">店舗登録</h2>
                                    <form class="mt-4 space-y-4" data-form="store">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">店舗名</label>
                                            <input name="name" required class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">住所</label>
                                            <input name="address" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
                                        </div>
                                        <button class="w-full rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-700">店舗を追加</button>
                                    </form>
                                    <div class="mt-5 divide-y divide-slate-100" data-list="stores"></div>
                                </section>

                                <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                                    <h2 class="text-lg font-semibold text-slate-950">シフト表作成</h2>
                                    <form class="mt-4 space-y-4" data-form="schedule">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">店舗</label>
                                            <select name="store_id" required class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100"></select>
                                        </div>
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">開始日</label>
                                                <input name="starts_on" type="date" required class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">終了日</label>
                                                <input name="ends_on" type="date" required class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
                                            </div>
                                        </div>
                                        <button class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">シフト表を作成</button>
                                    </form>
                                </section>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4 py-6" data-member-modal role="dialog" aria-modal="true" aria-labelledby="member-modal-title">
            <div class="w-full max-w-xl rounded-lg bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 id="member-modal-title" class="text-lg font-semibold text-slate-950">キャスト登録</h2>
                        <p class="mt-1 text-sm text-slate-500">シフト提出対象者を追加します。</p>
                    </div>
                    <button type="button" class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" data-action="close-member-modal" aria-label="閉じる">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form class="space-y-4 px-5 py-5" data-form="member">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">氏名</label>
                        <input name="name" required class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">店舗</label>
                        <select name="store_id" class="mt-2 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
                            <option value="">未割り当て</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">電話</label>
                            <input name="phone" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">メール</label>
                            <input name="email" type="email" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">備考</label>
                        <textarea name="remarks" rows="3" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                        <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-action="close-member-modal">
                            キャンセル
                        </button>
                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
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

                const collapseDesktopSidebar = () => {
                    $('[data-desktop-sidebar]')?.classList.add('is-hidden');
                    $('[data-desktop-sidebar-collapsed]')?.classList.remove('is-hidden');
                    $('[data-action="expand-desktop-sidebar"]')?.setAttribute('aria-expanded', 'false');
                };

                const expandDesktopSidebar = () => {
                    $('[data-desktop-sidebar-collapsed]')?.classList.add('is-hidden');
                    $('[data-desktop-sidebar]')?.classList.remove('is-hidden');
                    $('[data-action="expand-desktop-sidebar"]')?.setAttribute('aria-expanded', 'true');
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

                        link.classList.toggle('bg-green-50', isActive);
                        link.classList.toggle('text-green-700', isActive);
                        link.classList.toggle('text-gray-700', !isActive);
                        link.classList.toggle('hover:bg-gray-50', !isActive);
                        link.classList.toggle('hover:text-gray-900', !isActive);

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
                    const klass = status === 'published' ? 'bg-emerald-50 text-emerald-700' : status === 'archived' ? 'bg-slate-100 text-slate-500' : 'bg-amber-50 text-amber-700';
                    return `<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${klass}">${label}</span>`;
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
                                        <p class="font-semibold text-slate-900">${escapeHtml(store.name)}</p>
                                        <p class="mt-1 text-sm text-slate-500">${escapeHtml(store.address || '住所未登録')}</p>
                                    </div>
                                    <span class="rounded-full ${store.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'} px-2.5 py-1 text-xs font-semibold">
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
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900">${escapeHtml(member.name)}</td>
                                <td class="px-5 py-4 text-slate-600">${escapeHtml(member.store?.name || '未割り当て')}</td>
                                <td class="px-5 py-4 text-slate-600">
                                    <div>${escapeHtml(member.phone || '-')}</div>
                                    <div class="text-xs text-slate-500">${escapeHtml(member.email || '')}</div>
                                </td>
                                <td class="px-5 py-4"><span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">${escapeHtml(member.status || 'active')}</span></td>
                                <td class="px-5 py-4 text-slate-600">${member.is_shift_submitter ? '対象' : '対象外'}</td>
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
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900">${escapeHtml(schedule.store?.name || '-')}</td>
                                <td class="px-5 py-4 text-slate-600">${escapeHtml(schedule.starts_on)} - ${escapeHtml(schedule.ends_on)}</td>
                                <td class="px-5 py-4">${badge(schedule.status)}</td>
                                <td class="px-5 py-4 text-slate-600">${schedule.shift_slots?.length || 0}</td>
                                <td class="px-5 py-4 text-right">
                                    ${schedule.status === 'published'
                                        ? '<span class="text-xs font-semibold text-slate-400">公開済み</span>'
                                        : `<button type="button" class="rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50" data-publish="${schedule.id}">公開</button>`}
                                </td>
                            </tr>
                        `).join('')
                        : '<tr><td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">シフト表がまだありません。</td></tr>';
                };

                const renderStats = () => {
                    $('[data-bind="tenantName"]').textContent = state.user.tenant?.name || '管理画面';
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

                    if (event.target.closest('[data-action="collapse-desktop-sidebar"]')) {
                        collapseDesktopSidebar();
                    }

                    if (event.target.closest('[data-action="expand-desktop-sidebar"]')) {
                        expandDesktopSidebar();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeMobileSidebar();
                        closeAccountMenu();
                        closeMemberModal();
                    }
                });

                window.addEventListener('hashchange', () => {
                    setSidebarActive();
                });

                $$('[data-filter="memberStore"], [data-filter="scheduleStore"]').forEach((select) => {
                    select.addEventListener('change', render);
                });

                setSidebarActive();
                load().catch((error) => setMessage('[data-alert]', error.message));
            })();
        </script>
    </body>
</html>
