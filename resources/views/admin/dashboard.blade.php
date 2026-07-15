@php
    $page = $page ?? 'overview';
    $menuSections = [
        [
            'label' => 'OPERATIONS',
            'items' => [
                [
                    'label' => 'ダッシュボード',
                    'href' => route('dashboard'),
                    'active' => $page === 'overview',
                    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                ],
                [
                    'label' => 'シフト管理',
                    'href' => route('admin.schedules'),
                    'active' => in_array($page, ['schedules', 'schedule-create'], true),
                    'icon' => 'M8 7V3m8 4V3M5 11h14M6 5h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2z',
                ],
                [
                    'label' => 'キャスト管理',
                    'href' => route('admin.members'),
                    'active' => in_array($page, ['members', 'member-edit'], true),
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                ],
            ],
        ],
        [
            'label' => 'CONTROL',
            'items' => [
                [
                    'label' => '店舗管理',
                    'href' => route('admin.stores'),
                    'active' => in_array($page, ['stores', 'store-create', 'store-edit'], true),
                    'icon' => 'M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9h1M9 13h1M9 17h1M14 13h1M14 17h1',
                ],
                [
                    'label' => 'アカウント',
                    'href' => route('admin.account'),
                    'active' => $page === 'account',
                    'icon' => 'M5.121 17.804A8.966 8.966 0 0112 15c2.21 0 4.235.8 5.879 2.128M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 1a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
            ],
        ],
    ];
    if (Auth::user()?->isSuperAdmin()) {
        $menuSections[] = [
            'label' => 'GLOBAL',
            'items' => [
                [
                    'label' => '全体管理',
                    'href' => route('admin.global-management'),
                    'active' => in_array($page, ['global-management', 'global-line-settings'], true),
                    'icon' => 'M4 6h16M4 10h16M4 14h10M4 18h10M17 14l3 3m0 0l-3 3m3-3h-6',
                ],
            ],
        ];
    }
    $sidebarItems = array_merge(...array_column($menuSections, 'items'));
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
    <body class="min-h-screen bg-gray-100 text-gray-900 antialiased">
        <div id="adminApp" class="min-h-screen bg-gray-100 pt-16">
            <header class="fixed inset-x-0 top-0 z-40 border-b border-gray-300 bg-white shadow-sm">
                <div class="mx-auto flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="flex h-16 items-center gap-3">
                            <span class="grid size-10 place-items-center rounded-lg bg-teal-700 text-sm font-black text-white">SH</span>
                            <span>
                                <span class="block text-base font-black leading-tight text-gray-900">ShiftHub</span>
                                <span class="block text-xs font-semibold text-gray-500" data-bind="tenantName">{{ Auth::user()->tenant?->name }}</span>
                            </span>
                        </a>
                    </div>

                    <div class="flex items-center">
                        <div class="hidden items-center gap-2 sm:flex">
                            <p class="max-w-40 truncate text-base text-gray-800">{{ Auth::user()->name }}</p>
                            <svg width="30" height="30" viewBox="0 0 29.5 29.5" aria-hidden="true">
                                <path d="M14.749,0A14.75,14.75,0,1,0,29.5,14.75,14.755,14.755,0,0,0,14.749,0Zm0,4.425A4.425,4.425,0,1,1,10.324,8.85a4.419,4.419,0,0,1,4.424-4.425Zm0,20.945A10.621,10.621,0,0,1,5.9,20.62c.044-2.936,5.9-4.543,8.849-4.543s8.805,1.607,8.849,4.543a10.62,10.62,0,0,1-8.849,4.75Z" fill="#2997e7" fill-rule="evenodd"/>
                            </svg>
                        </div>

                        <div class="relative ml-3">
                            <button type="button" class="inline-flex size-9 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-action="toggle-account-menu" aria-expanded="false" aria-haspopup="true" aria-label="アカウントメニュー" title="アカウントメニュー">
                                <svg width="20" height="20" viewBox="0 0 19.454 20" aria-hidden="true">
                                    <path d="M17.159,11.78A6.234,6.234,0,0,0,17.215,11a6.234,6.234,0,0,0-.056-.78l1.688-1.32a.4.4,0,0,0,.1-.512l-1.6-2.768a.4.4,0,0,0-.488-.176l-1.992.8a5.845,5.845,0,0,0-1.352-.784l-.3-2.12A.39.39,0,0,0,12.816,3h-3.2a.39.39,0,0,0-.392.336l-.3,2.12a6.147,6.147,0,0,0-1.352.784l-1.992-.8a.39.39,0,0,0-.488.176l-1.6,2.768a.394.394,0,0,0,.1.512l1.688,1.32A6.345,6.345,0,0,0,5.215,11a6.345,6.345,0,0,0,.056.78L3.583,13.1a.4.4,0,0,0-.1.512l1.6,2.768a.4.4,0,0,0,.488.176l1.992-.8a5.845,5.845,0,0,0,1.352.784l.3,2.12a.39.39,0,0,0,.392.336h3.2a.39.39,0,0,0,.392-.336l.3-2.12a6.147,6.147,0,0,0,1.352-.784l1.992.8a.39.39,0,0,0,.488-.176l1.6-2.768a.4.4,0,0,0-.1-.512ZM11.215,13.8A2.8,2.8,0,1,1,14.015,11,2.8,2.8,0,0,1,11.215,13.8Z" transform="translate(-1.488 -1.5)" fill="#c4c4c4"/>
                                </svg>
                            </button>

                            <div class="absolute right-0 z-50 mt-2 hidden w-44 overflow-hidden rounded-md border border-gray-200 bg-white py-1 shadow-lg" data-account-menu>
                                <form method="POST" action="{{ route('logout') }}" novalidate>
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm font-semibold text-gray-700 transition hover:bg-gray-50 hover:text-gray-900">
                                        ログアウト
                                    </button>
                                </form>
                            </div>
                        </div>

                        <button type="button" class="ml-3 inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:bg-gray-100 focus:text-gray-500 sm:hidden" data-action="open-mobile-sidebar" aria-controls="app-sidebar-drawer" aria-expanded="false" aria-label="管理メニューを開く">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <div class="border-b border-gray-200 bg-white px-4 py-3 shadow-sm lg:hidden">
                <button type="button" class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" data-action="open-mobile-sidebar" aria-controls="app-sidebar-drawer" aria-expanded="false">
                    <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>管理メニュー</span>
                </button>
            </div>

            <div class="pointer-events-none fixed inset-0 z-50 hidden lg:hidden" data-sidebar-drawer role="dialog" aria-modal="true" aria-label="管理メニュー">
                <button type="button" class="absolute inset-0 bg-gray-900/40 opacity-0 transition-opacity" data-sidebar-backdrop data-action="close-mobile-sidebar" aria-label="メニューを閉じる"></button>
                <aside id="app-sidebar-drawer" class="relative h-full w-80 max-w-[86vw] -translate-x-full overflow-y-auto border-r border-gray-200 bg-white shadow-2xl transition-transform duration-200 ease-out" data-sidebar-panel>
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                        <p class="text-sm font-semibold text-gray-700">管理メニュー</p>
                        <button type="button" class="rounded-md p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-action="close-mobile-sidebar" aria-label="メニューを閉じる" title="閉じる">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <nav class="space-y-2 px-4 py-5 sm:px-6" aria-label="管理メニュー">
                        @foreach ($sidebarItems as $item)
                            <a href="{{ $item['href'] }}" class="flex items-center gap-4 rounded-lg px-4 py-4 text-base font-semibold transition {{ $item['active'] ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}" @if ($item['active']) aria-current="page" @endif data-sidebar-link data-action="close-mobile-sidebar">
                                <svg class="size-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                                </svg>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </aside>
            </div>

            <div class="lg:flex">
                <aside id="admin-sidebar-desktop-collapsed" class="hidden bg-white lg:sticky lg:top-16 lg:z-20 lg:h-[calc(100vh-4rem)] lg:w-12 lg:shrink-0 lg:flex-col lg:items-center lg:overflow-y-auto lg:border-r lg:border-gray-300 lg:shadow-[6px_0_18px_rgba(31,41,55,0.04)]" data-sidebar-collapsed aria-label="管理メニュー">
                    <div class="flex w-full justify-center px-1 pt-2">
                        <button type="button" class="inline-flex size-7 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-action="expand-desktop-sidebar" aria-controls="admin-sidebar-desktop" aria-expanded="false" aria-label="サイドメニューを開く" title="開く">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <nav class="mt-8 flex w-full flex-col items-center gap-3 px-1" aria-label="管理メニュー">
                        @foreach ($sidebarItems as $item)
                            <a href="{{ $item['href'] }}" class="flex size-10 items-center justify-center rounded-lg transition {{ $item['active'] ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}" @if ($item['active']) aria-current="page" @endif data-sidebar-link aria-label="{{ $item['label'] }}" title="{{ $item['label'] }}">
                                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                                </svg>
                            </a>
                        @endforeach
                    </nav>
                </aside>

                <aside id="admin-sidebar-desktop" class="hidden bg-white lg:sticky lg:top-16 lg:z-20 lg:block lg:h-[calc(100vh-4rem)] lg:w-60 lg:shrink-0 lg:overflow-y-auto lg:border-r lg:border-gray-300 lg:shadow-[6px_0_18px_rgba(31,41,55,0.04)]" data-sidebar-expanded aria-label="管理メニュー">
                    <div class="flex justify-end px-3 pt-2">
                        <button type="button" class="inline-flex size-7 items-center justify-center rounded-md text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-action="collapse-desktop-sidebar" aria-label="サイドメニューを閉じる" title="閉じる">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>

                    <nav class="space-y-2 px-6 pb-8 pt-3" aria-label="管理メニュー">
                        @foreach ($sidebarItems as $item)
                            <a href="{{ $item['href'] }}" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-semibold leading-tight transition {{ $item['active'] ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}" @if ($item['active']) aria-current="page" @endif data-sidebar-link>
                                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                                </svg>
                                <span class="min-w-0 whitespace-nowrap">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </aside>

                <main class="min-w-0 flex-1">
                    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                        <div class="mb-5 hidden rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" data-alert></div>
                        <div class="mb-5 hidden rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" data-notice></div>
                        @if (session('notice'))
                            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('notice') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        @if ($page === 'overview')
                        <div class="mb-5">
                            <h2 class="text-xl font-semibold leading-tight text-gray-900">ダッシュボード</h2>
                        </div>

                        <div class="mb-4 grid gap-3 lg:grid-cols-[minmax(340px,0.72fr)_minmax(520px,1fr)] lg:items-stretch">
                            <div class="grid overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm sm:grid-cols-4">
                                <div class="flex flex-col justify-center border-b border-gray-200 px-3 py-3 last:border-b-0 sm:border-b-0 sm:border-r sm:last:border-r-0">
                                    <p class="text-[11px] font-medium leading-tight text-gray-500">店舗数</p>
                                    <p class="mt-1 text-lg font-semibold leading-none text-gray-950" data-stat="stores">0</p>
                                </div>
                                <div class="flex flex-col justify-center border-b border-gray-200 px-3 py-3 last:border-b-0 sm:border-b-0 sm:border-r sm:last:border-r-0">
                                    <p class="text-[11px] font-medium leading-tight text-gray-500">キャスト数</p>
                                    <p class="mt-1 text-lg font-semibold leading-none text-gray-950" data-stat="members">0</p>
                                </div>
                                <div class="flex flex-col justify-center border-b border-gray-200 px-3 py-3 last:border-b-0 sm:border-b-0 sm:border-r sm:last:border-r-0">
                                    <p class="text-[11px] font-medium leading-tight text-gray-500">提出対象者</p>
                                    <p class="mt-1 text-lg font-semibold leading-none text-gray-950" data-stat="submitters">0</p>
                                </div>
                                <div class="flex flex-col justify-center px-3 py-3">
                                    <p class="text-[11px] font-medium leading-tight text-gray-500">公開済みシフト</p>
                                    <p class="mt-1 text-lg font-semibold leading-none text-gray-950" data-stat="published">0</p>
                                </div>
                            </div>

                            <form class="grid rounded-lg border border-gray-200 bg-white p-3 shadow-sm gap-2 lg:grid-cols-[minmax(220px,1fr)_auto_auto] lg:items-center" data-form="dashboard-filter">
                                <label class="flex min-w-0 items-center gap-2">
                                    <span class="shrink-0 text-xs font-semibold text-gray-600">店舗</span>
                                    <select class="min-w-0 flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500" data-filter="dashboardStore">
                                        <option value="">すべて</option>
                                    </select>
                                </label>
                                <button type="submit" class="inline-flex h-9 items-center justify-center rounded-md bg-gray-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-gray-800">
                                    反映
                                </button>
                                <button type="button" class="inline-flex h-9 items-center justify-center rounded-md border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50" data-action="reset-dashboard-filter">
                                    リセット
                                </button>
                            </form>
                        </div>

                        <div class="mt-5">
                            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                                <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">シフト公開状況の推移</h3>
                                        <p class="mt-1 text-xs text-gray-500" data-dashboard-chart-caption>全店舗の公開済み・下書きシフトを表示しています。</p>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs text-gray-500">
                                        <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-green-500"></span>公開済み</span>
                                        <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-500"></span>下書き</span>
                                    </div>
                                </div>
                                <div class="relative h-64">
                                    <svg class="h-full w-full" viewBox="0 0 760 220" preserveAspectRatio="none" aria-label="シフト公開状況の推移グラフ" data-dashboard-chart>
                                        <g stroke="#e5e7eb" stroke-width="1">
                                            <path d="M40 20H740M40 80H740M40 140H740M40 200H740"/>
                                        </g>
                                        <path d="M40 20V200H740" fill="none" stroke="#cbd5e1" stroke-width="1.5" vector-effect="non-scaling-stroke"/>
                                    </svg>
                                </div>
                                <div class="relative mt-1 h-4 border-t border-gray-100 pt-1 text-xs font-medium text-gray-500" data-dashboard-axis>
                                    <span class="absolute left-1/2 top-1 -translate-x-1/2 whitespace-nowrap">データなし</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-200 px-5 py-4">
                                <h3 class="text-sm font-semibold text-gray-900">直近のシフト表</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500">
                                        <tr>
                                            <th class="px-5 py-3 text-left">店舗</th>
                                            <th class="px-5 py-3 text-left">期間</th>
                                            <th class="px-5 py-3 text-right">枠数</th>
                                            <th class="px-5 py-3 text-right">状態</th>
                                            <th class="px-5 py-3 text-right">操作</th>
                                            <th class="px-5 py-3 text-right">アクション</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white" data-list="dashboardSchedules">
                                        <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">データなし</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        @if ($page === 'schedules')
                        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                                    <div class="border-b border-slate-200 px-4 py-3">
                                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <p class="text-xs font-black text-teal-700">Schedule Control</p>
                                                <h2 class="mt-1 text-lg font-bold text-slate-950">シフト管理</h2>
                                            </div>
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                                                    店舗
                                                    <select class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500" data-filter="scheduleStore">
                                                        <option value="">すべて</option>
                                                    </select>
                                                </label>
                                                <a href="{{ route('admin.schedules.create') }}" class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                                                    シフト表作成
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-[700px] w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-50 text-left text-xs font-black text-slate-500">
                                                <tr>
                                                    <th class="px-4 py-3">店舗</th>
                                                    <th class="px-4 py-3">期間</th>
                                                    <th class="px-4 py-3">状態</th>
                                                    <th class="px-4 py-3">枠数</th>
                                                    <th class="px-4 py-3 text-right">操作</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-200" data-list="schedules"></tbody>
                                        </table>
                                    </div>
                        </section>
                        @endif

                        @if ($page === 'schedule-create')
                        <section class="max-w-xl rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-black text-teal-700">Create Schedule</p>
                                    <h2 class="mt-1 text-lg font-bold text-slate-950">シフト表作成</h2>
                                </div>
                                <a href="{{ route('admin.schedules') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                    一覧へ戻る
                                </a>
                            </div>
                            <form class="space-y-4" data-form="schedule">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">店舗</label>
                                    <select name="store_id" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white"></select>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700">開始日</label>
                                        <input name="starts_on" type="date" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700">終了日</label>
                                        <input name="ends_on" type="date" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                                    <a href="{{ route('admin.schedules') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                        キャンセル
                                    </a>
                                    <button class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                                        シフト表を作成
                                    </button>
                                </div>
                            </form>
                        </section>
                        @endif

                        @if ($page === 'members')
                        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                                    <div class="border-b border-slate-200 px-4 py-3">
                                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <p class="text-xs font-black text-teal-700">Crew Directory</p>
                                                <h2 class="mt-1 text-lg font-bold text-slate-950">キャスト管理</h2>
                                            </div>
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                                                    店舗
                                                    <select class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500" data-filter="memberStore">
                                                        <option value="">すべて</option>
                                                    </select>
                                                </label>
                                                <button type="button" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800" data-action="open-member-modal">
                                                    キャストを追加
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-[980px] w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-50 text-left text-xs font-black text-slate-500">
                                                <tr>
                                                    <th class="px-4 py-3">表示名</th>
                                                    <th class="px-4 py-3">店舗</th>
                                                    <th class="px-4 py-3">LINE</th>
                                                    <th class="px-4 py-3">権限</th>
                                                    <th class="px-4 py-3">連絡先</th>
                                                    <th class="px-4 py-3">状態</th>
                                                    <th class="px-4 py-3">提出</th>
                                                    <th class="px-4 py-3">備考</th>
                                                    <th class="px-4 py-3 text-right">操作</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-200" data-list="members"></tbody>
                                        </table>
                                    </div>
                        </section>
                        @endif

                        @if ($page === 'member-edit')
                        <section class="max-w-2xl rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-black text-teal-700">Crew Directory</p>
                                    <h2 class="mt-1 text-lg font-bold text-slate-950">キャスト編集</h2>
                                </div>
                                <a href="{{ route('admin.members') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                    一覧へ戻る
                                </a>
                            </div>
                            <form class="space-y-4" data-form="member-edit" data-member-id="{{ $editingMember->id }}" novalidate>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">表示名</label>
                                    <input name="display_name" required value="{{ old('display_name', $editingMember->display_name ?: $editingMember->name) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">本名 <span class="text-xs font-semibold text-slate-400">任意</span></label>
                                    <input name="name" value="{{ old('name', $editingMember->name) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">店舗</label>
                                    <select name="store_id" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white" data-initial-value="{{ $editingMember->store_id }}">
                                        <option value="">未割り当て</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700">電話 <span class="text-xs font-semibold text-slate-400">任意</span></label>
                                        <input name="phone" value="{{ old('phone', $editingMember->phone) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700">メール</label>
                                        <input name="email" type="email" required value="{{ old('email', $editingMember->email) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">状態</label>
                                    <select name="status" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                        <option value="active" @selected(old('status', $editingMember->status) === 'active')>active</option>
                                        <option value="inactive" @selected(old('status', $editingMember->status) === 'inactive')>inactive</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">権限</label>
                                    <select name="role" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                        <option value="cast" @selected(old('role', $editingMember->role ?? 'cast') === 'cast')>キャスト</option>
                                        <option value="manager" @selected(old('role', $editingMember->role ?? 'cast') === 'manager')>店長</option>
                                        <option value="admin" @selected(old('role', $editingMember->role ?? 'cast') === 'admin')>管理者</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <span class="flex items-start gap-3">
                                            <input type="checkbox" name="is_shift_submitter" value="1" @checked(old('is_shift_submitter', $editingMember->is_shift_submitter)) class="mt-1 rounded border-slate-300 text-teal-700 accent-teal-700 transition focus:ring-4 focus:ring-teal-100">
                                            <span>
                                                <span class="block text-sm font-bold text-slate-800">提出対象</span>
                                                <span class="mt-1 block text-xs leading-5 text-slate-500">シフト提出対象者として扱います。</span>
                                            </span>
                                        </span>
                                    </label>
                                    <label class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <span class="flex items-start gap-3">
                                            <input type="checkbox" name="is_remind_disabled" value="1" @checked(old('is_remind_disabled', $editingMember->is_remind_disabled)) class="mt-1 rounded border-slate-300 text-teal-700 accent-teal-700 transition focus:ring-4 focus:ring-teal-100">
                                            <span>
                                                <span class="block text-sm font-bold text-slate-800">リマインド停止</span>
                                                <span class="mt-1 block text-xs leading-5 text-slate-500">通知対象から外します。</span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">備考</label>
                                    <textarea name="remarks" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">{{ old('remarks', $editingMember->remarks) }}</textarea>
                                </div>
                                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                                    <a href="{{ route('admin.members') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                        キャンセル
                                    </a>
                                    <button class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                                        キャストを保存
                                    </button>
                                </div>
                            </form>
                        </section>
                        @endif

                        @if ($page === 'stores')
                        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                            <div class="border-b border-slate-200 px-4 py-3">
                                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p class="text-xs font-black text-teal-700">Store Registry</p>
                                        <h2 class="mt-1 text-lg font-bold text-slate-950">店舗管理</h2>
                                    </div>
                                    <a href="{{ route('admin.stores.create') }}" class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                                        店舗登録
                                    </a>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50 text-xs font-semibold text-gray-500">
                                        <tr>
                                            <th class="px-5 py-3 text-left">店舗名</th>
                                            <th class="px-5 py-3 text-left">住所</th>
                                            <th class="px-5 py-3 text-right">状態</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white" data-list="stores"></tbody>
                                </table>
                            </div>
                        </section>
                        @endif

                        @if (in_array($page, ['store-create', 'store-edit'], true))
                        <section class="max-w-xl rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-black text-teal-700">Store Registry</p>
                                    <h2 class="mt-1 text-lg font-bold text-slate-950">{{ $page === 'store-edit' ? '店舗編集' : '店舗登録' }}</h2>
                                </div>
                                <a href="{{ route('admin.stores') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                    一覧へ戻る
                                </a>
                            </div>
                            <form class="space-y-4" data-form="{{ $page === 'store-edit' ? 'store-edit' : 'store' }}" @if ($page === 'store-edit') data-store-id="{{ $editingStore->id }}" @endif>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">店舗名</label>
                                    <input name="name" required value="{{ old('name', $page === 'store-edit' ? $editingStore->name : '') }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">住所</label>
                                    <input name="address" value="{{ old('address', $page === 'store-edit' ? $editingStore->address : '') }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white">
                                </div>
                                @if ($page === 'store-edit')
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <label for="store_is_active" class="flex items-start gap-3">
                                            <input id="store_is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $editingStore->is_active)) class="mt-1 rounded border-slate-300 text-teal-700 accent-teal-700 transition focus:ring-4 focus:ring-teal-100">
                                            <span>
                                                <span class="block text-sm font-bold text-slate-800">稼働中</span>
                                                <span class="mt-1 block text-xs leading-5 text-slate-500">チェックを外すと停止中として扱います。</span>
                                            </span>
                                        </label>
                                    </div>
                                @endif
                                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                                    <a href="{{ route('admin.stores') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                        キャンセル
                                    </a>
                                    <button class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                                        {{ $page === 'store-edit' ? '店舗を保存' : '店舗を追加' }}
                                    </button>
                                </div>
                            </form>
                        </section>
                        @endif

                        @if ($page === 'account')
                        <section class="space-y-5 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <div>
                                <p class="text-xs font-black text-teal-700">Account Security</p>
                                <h2 class="mt-1 text-lg font-bold text-slate-950">アカウント</h2>
                                <p class="mt-3 text-sm leading-6 text-slate-600">管理ログインは2段階認証で保護されています。</p>
                                <a href="{{ route('two-factor.settings') }}" class="mt-4 inline-flex rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:border-teal-200 hover:bg-teal-50 hover:text-teal-800">
                                    2段階認証設定
                                </a>
                            </div>
                            @php
                                $tenant = Auth::user()->tenant;
                                $lineLoginSetting = $tenant?->relationLoaded('lineLoginSetting') ? $tenant->lineLoginSetting : null;
                                $lineLiffSetting = $tenant?->relationLoaded('lineLiffSetting') ? $tenant->lineLiffSetting : null;
                                $lineOfficialAccount = $tenant?->relationLoaded('lineOfficialAccount') ? $tenant->lineOfficialAccount : null;
                            @endphp
                            <div class="border-t border-slate-200 pt-5">
                                <div class="flex flex-wrap gap-2 border-b border-slate-200" data-line-tabs="account">
                                    <button type="button" class="border-b-2 border-teal-600 px-4 py-2 text-sm font-black text-teal-700" data-line-tab="account" data-line-tab-target="official">公式LINE</button>
                                    <button type="button" class="border-b-2 border-transparent px-4 py-2 text-sm font-black text-slate-500 hover:text-slate-800" data-line-tab="account" data-line-tab-target="login">LINEログイン</button>
                                    <button type="button" class="border-b-2 border-transparent px-4 py-2 text-sm font-black text-slate-500 hover:text-slate-800" data-line-tab="account" data-line-tab-target="liff">LIFF</button>
                                </div>

                                <form class="space-y-5 pt-5" data-line-tab-panel="account" data-line-tab-panel-name="official" data-form="tenant-settings">
                                    <input type="hidden" name="setting_type" value="official">
                                    <div>
                                        <p class="text-xs font-black text-teal-700">Messaging API</p>
                                        <h3 class="mt-1 text-base font-black text-slate-950">公式LINE設定</h3>
                                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">チャネルID</label>
                                                <input name="line_official_channel_id" value="{{ old('line_official_channel_id', $lineOfficialAccount?->channel_id) }}" placeholder="例: 2000000000" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">LINE公式アカウントID</label>
                                                <input name="line_official_line_at_id" value="{{ old('line_official_line_at_id', $lineOfficialAccount?->line_at_id) }}" placeholder="例: @nomihub" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">チャネルアクセストークン</label>
                                                <input name="line_official_channel_access_token" type="password" autocomplete="new-password" placeholder="{{ $lineOfficialAccount?->channel_access_token ? '保存済み' : '' }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">チャネルシークレット</label>
                                                <input name="line_official_channel_secret" type="password" autocomplete="new-password" placeholder="{{ $lineOfficialAccount?->channel_secret ? '保存済み' : '' }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">Webhook URL</label>
                                                <input name="line_official_webhook_url" type="url" value="{{ old('line_official_webhook_url', $lineOfficialAccount?->webhook_url) }}" placeholder="https://example.com/webhook/line" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">LINEタイムラインURL</label>
                                                <input name="line_official_line_timeline_url" type="url" value="{{ old('line_official_line_timeline_url', $lineOfficialAccount?->line_timeline_url) }}" placeholder="https://line.me/R/ti/p/..." class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end border-t border-slate-200 pt-4">
                                        <button class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">公式LINE設定を保存</button>
                                    </div>
                                </form>

                                <form class="hidden space-y-5 pt-5" data-line-tab-panel="account" data-line-tab-panel-name="login" data-form="tenant-settings">
                                    <input type="hidden" name="setting_type" value="line_login">
                                    <div>
                                        <p class="text-xs font-black text-teal-700">LINE Login</p>
                                        <h3 class="mt-1 text-base font-black text-slate-950">LINEログイン設定</h3>
                                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">チャネルID</label>
                                            <input name="line_login_channel_id" value="{{ old('line_login_channel_id', $lineLoginSetting?->channel_id) }}" placeholder="例: 2000000000" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">チャネルシークレット</label>
                                            <input name="line_login_channel_secret" type="password" autocomplete="new-password" placeholder="{{ $lineLoginSetting?->channel_secret ? '保存済み' : '' }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white">
                                        </div>
                                    </div>
                                </div>
                                    <div class="flex justify-end border-t border-slate-200 pt-4">
                                        <button class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">LINEログイン設定を保存</button>
                                    </div>
                                </form>

                                <form class="hidden space-y-5 pt-5" data-line-tab-panel="account" data-line-tab-panel-name="liff" data-form="tenant-settings">
                                    <input type="hidden" name="setting_type" value="liff">
                                    <div>
                                        <p class="text-xs font-black text-teal-700">Mini App</p>
                                        <h3 class="mt-1 text-base font-black text-slate-950">LINE LIFF設定</h3>
                                        <div class="mt-4">
                                            <label class="block text-sm font-bold text-slate-700">LIFF ID</label>
                                            <input name="liff_id" value="{{ old('liff_id', $lineLiffSetting?->liff_id) }}" placeholder="例: 2000000000-xxxxxxxx" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                        </div>
                                    </div>
                                    <div class="flex justify-end border-t border-slate-200 pt-4">
                                        <button class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">LIFF設定を保存</button>
                                    </div>
                                </form>
                            </div>
                        </section>
                        @endif

                        @if ($page === 'global-management')
                        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-black text-teal-700">Global Control</p>
                                    <h2 class="mt-1 text-lg font-bold text-slate-950">全体管理</h2>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">テナント単位の設定と管理画面への動線です。</p>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50 text-left text-xs font-black text-slate-500">
                                        <tr>
                                            <th class="px-5 py-3">テナント</th>
                                            <th class="px-5 py-3 text-right">店舗</th>
                                            <th class="px-5 py-3 text-right">キャスト</th>
                                            <th class="px-5 py-3">LINE設定</th>
                                            <th class="px-5 py-3 text-right">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200">
                                        @forelse (($globalTenants ?? collect()) as $tenant)
                                            @php
                                                $lineLiffSetting = $tenant->relationLoaded('lineLiffSetting') ? $tenant->lineLiffSetting : null;
                                                $lineOfficialAccount = $tenant->relationLoaded('lineOfficialAccount') ? $tenant->lineOfficialAccount : null;
                                                $lineLoginSetting = $tenant->relationLoaded('lineLoginSetting') ? $tenant->lineLoginSetting : null;
                                            @endphp
                                            <tr class="hover:bg-slate-50">
                                                <td class="min-w-[220px] px-5 py-4">
                                                    <p class="font-bold text-slate-950">{{ $tenant->name }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">ID: {{ $tenant->id }}</p>
                                                </td>
                                                <td class="whitespace-nowrap px-5 py-4 text-right text-slate-700">{{ $tenant->stores_count ?? 0 }}</td>
                                                <td class="whitespace-nowrap px-5 py-4 text-right text-slate-700">{{ $tenant->members_count ?? 0 }}</td>
                                                <td class="min-w-[220px] px-5 py-4">
                                                    <div class="flex flex-wrap gap-2">
                                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black {{ $lineLoginSetting?->channel_id ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-500' }}">ログイン</span>
                                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black {{ $lineLiffSetting?->liff_id ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-500' }}">LIFF</span>
                                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black {{ $lineOfficialAccount?->channel_id ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-500' }}">公式LINE</span>
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                                    <a href="{{ route('admin.global-management.tenants.line-settings', $tenant) }}" class="inline-flex rounded-md border border-teal-200 px-3 py-1.5 text-xs font-semibold text-teal-700 hover:bg-teal-50">
                                                        LINE設定
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">テナントがありません。</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                        @endif

                        @if ($page === 'global-line-settings')
                        @php
                            $tenant = $editingTenant ?? null;
                            $lineLoginSetting = $tenant?->relationLoaded('lineLoginSetting') ? $tenant->lineLoginSetting : null;
                            $lineLiffSetting = $tenant?->relationLoaded('lineLiffSetting') ? $tenant->lineLiffSetting : null;
                            $lineOfficialAccount = $tenant?->relationLoaded('lineOfficialAccount') ? $tenant->lineOfficialAccount : null;
                        @endphp
                        <section class="space-y-5 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-black text-teal-700">Global Control</p>
                                    <h2 class="mt-1 text-lg font-bold text-slate-950">LINE設定 - {{ $tenant?->name }}</h2>
                                </div>
                                <a href="{{ route('admin.global-management') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                    全体管理へ戻る
                                </a>
                            </div>

                            @if (! ($lineSettingTablesReady ?? false))
                                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                                    LINE設定テーブルが未作成です。マイグレーション実行後に保存できます。
                                </div>
                            @endif

                            <div>
                                <div class="flex flex-wrap gap-2 border-b border-slate-200" data-line-tabs="global">
                                    <button type="button" class="border-b-2 border-teal-600 px-4 py-2 text-sm font-black text-teal-700" data-line-tab="global" data-line-tab-target="official">公式LINE</button>
                                    <button type="button" class="border-b-2 border-transparent px-4 py-2 text-sm font-black text-slate-500 hover:text-slate-800" data-line-tab="global" data-line-tab-target="login">LINEログイン</button>
                                    <button type="button" class="border-b-2 border-transparent px-4 py-2 text-sm font-black text-slate-500 hover:text-slate-800" data-line-tab="global" data-line-tab-target="liff">LIFF</button>
                                </div>

                                <form method="POST" action="{{ route('admin.global-management.tenants.line-settings.update', $tenant) }}" class="space-y-5 pt-5" data-line-tab-panel="global" data-line-tab-panel-name="official">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="setting_type" value="official">
                                    <div>
                                        <p class="text-xs font-black text-teal-700">Messaging API</p>
                                        <h3 class="mt-1 text-base font-black text-slate-950">公式LINE設定</h3>
                                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">チャネルID</label>
                                                <input name="line_official_channel_id" value="{{ old('line_official_channel_id', $lineOfficialAccount?->channel_id) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">LINE公式アカウントID</label>
                                                <input name="line_official_line_at_id" value="{{ old('line_official_line_at_id', $lineOfficialAccount?->line_at_id) }}" placeholder="例: @nomihub" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">チャネルアクセストークン</label>
                                                <input name="line_official_channel_access_token" type="password" autocomplete="new-password" placeholder="{{ $lineOfficialAccount?->channel_access_token ? '保存済み' : '' }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">チャネルシークレット</label>
                                                <input name="line_official_channel_secret" type="password" autocomplete="new-password" placeholder="{{ $lineOfficialAccount?->channel_secret ? '保存済み' : '' }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">Webhook URL</label>
                                                <input name="line_official_webhook_url" type="url" value="{{ old('line_official_webhook_url', $lineOfficialAccount?->webhook_url) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-slate-700">LINEタイムラインURL</label>
                                                <input name="line_official_line_timeline_url" type="url" value="{{ old('line_official_line_timeline_url', $lineOfficialAccount?->line_timeline_url) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end border-t border-slate-200 pt-4">
                                        <button @disabled(! ($lineSettingTablesReady ?? false)) class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800 disabled:cursor-not-allowed disabled:bg-slate-300">公式LINE設定を保存</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('admin.global-management.tenants.line-settings.update', $tenant) }}" class="hidden space-y-5 pt-5" data-line-tab-panel="global" data-line-tab-panel-name="login">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="setting_type" value="line_login">
                                    <div>
                                        <p class="text-xs font-black text-teal-700">LINE Login</p>
                                        <h3 class="mt-1 text-base font-black text-slate-950">LINEログイン設定</h3>
                                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">チャネルID</label>
                                            <input name="line_login_channel_id" value="{{ old('line_login_channel_id', $lineLoginSetting?->channel_id) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">チャネルシークレット</label>
                                            <input name="line_login_channel_secret" type="password" autocomplete="new-password" placeholder="{{ $lineLoginSetting?->channel_secret ? '保存済み' : '' }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:bg-white">
                                        </div>
                                    </div>
                                </div>
                                    <div class="flex justify-end border-t border-slate-200 pt-4">
                                        <button @disabled(! ($lineSettingTablesReady ?? false)) class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800 disabled:cursor-not-allowed disabled:bg-slate-300">LINEログイン設定を保存</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('admin.global-management.tenants.line-settings.update', $tenant) }}" class="hidden space-y-5 pt-5" data-line-tab-panel="global" data-line-tab-panel-name="liff">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="setting_type" value="liff">
                                    <div>
                                        <p class="text-xs font-black text-teal-700">Mini App</p>
                                        <h3 class="mt-1 text-base font-black text-slate-950">LINE LIFF設定</h3>
                                        <div class="mt-4">
                                            <label class="block text-sm font-bold text-slate-700">LIFF ID</label>
                                            <input name="liff_id" value="{{ old('liff_id', $lineLiffSetting?->liff_id) }}" placeholder="例: 2000000000-xxxxxxxx" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                        </div>
                                    </div>
                                    <div class="flex justify-end border-t border-slate-200 pt-4">
                                        <button @disabled(! ($lineSettingTablesReady ?? false)) class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800 disabled:cursor-not-allowed disabled:bg-slate-300">LIFF設定を保存</button>
                                    </div>
                                </form>
                            </div>
                        </section>
                        @endif
                    </div>
                </main>
            </div>
        </div>

        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4 py-6 backdrop-blur" data-member-modal role="dialog" aria-modal="true" aria-labelledby="member-modal-title">
            <div class="w-full max-w-xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-400/50">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-black text-teal-700">Crew Entry</p>
                        <h2 id="member-modal-title" class="mt-1 text-xl font-black text-slate-950">キャスト登録</h2>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" data-action="close-member-modal" aria-label="閉じる">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form class="space-y-4 px-5 py-5" data-form="member" novalidate>
                    <div>
                        <label class="block text-sm font-bold text-slate-700">表示名</label>
                        <input name="display_name" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700">本名 <span class="text-xs font-semibold text-slate-400">任意</span></label>
                        <input name="name" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700">店舗</label>
                        <select name="store_id" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                            <option value="">未割り当て</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700">権限</label>
                        <select name="role" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                            <option value="cast">キャスト</option>
                            <option value="manager">店長</option>
                            <option value="admin">管理者</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-bold text-slate-700">電話 <span class="text-xs font-semibold text-slate-400">任意</span></label>
                            <input name="phone" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700">メール</label>
                            <input name="email" type="email" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700">備考</label>
                        <textarea name="remarks" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                        <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:border-teal-200 hover:bg-teal-50" data-action="close-member-modal">
                            キャンセル
                        </button>
                        <button class="rounded-xl bg-teal-700 px-4 py-2 text-sm font-black text-white transition hover:bg-teal-800">
                            キャストを追加
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 px-4 py-6 backdrop-blur" data-registration-qr-modal role="dialog" aria-modal="true" aria-labelledby="registration-qr-title">
            <div class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-400/50">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-black text-teal-700">Mini App Entry</p>
                        <h2 id="registration-qr-title" class="mt-1 text-xl font-black text-slate-950">登録QR</h2>
                    </div>
                    <button type="button" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900" data-action="close-registration-qr-modal" aria-label="閉じる">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-5 py-5">
                    <p class="text-sm leading-6 text-slate-600" data-registration-qr-description>スタッフ本人にこのQRを読み込んでもらうと、LINEミニアプリで登録できます。</p>
                    <div class="mt-4 grid min-h-80 place-items-center rounded-2xl border border-slate-200 bg-slate-50 p-4" data-registration-qr-code>
                        <span class="text-sm font-bold text-slate-500">QRを読み込み中です。</span>
                    </div>
                    <input type="text" readonly class="mt-4 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 outline-none" data-registration-qr-url>
                    <div class="mt-4 flex justify-end gap-3">
                        <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:border-teal-200 hover:bg-teal-50" data-action="close-registration-qr-modal">
                            閉じる
                        </button>
                        <button type="button" class="rounded-xl bg-teal-700 px-4 py-2 text-sm font-black text-white transition hover:bg-teal-800" data-action="copy-registration-qr-url">
                            URLをコピー
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            (() => {
                const state = {
                    stores: @json($initialData['stores'] ?? []),
                    members: @json($initialData['members'] ?? []),
                    schedules: @json($initialData['schedules'] ?? []),
                    user: @json($initialData['user'] ?? Auth::user()->load('tenant')),
                };
                const routes = {
                    storesBase: @json(url('/dashboard/stores')),
                    membersBase: @json(url('/dashboard/members')),
                };

                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const $ = (selector) => document.querySelector(selector);
                const $$ = (selector) => Array.from(document.querySelectorAll(selector));

                const openMobileSidebar = () => {
                    const drawer = $('[data-sidebar-drawer]');
                    const panel = $('[data-sidebar-panel]');
                    const backdrop = $('[data-sidebar-backdrop]');

                    drawer?.classList.remove('hidden', 'pointer-events-none');
                    drawer?.classList.add('pointer-events-auto');
                    requestAnimationFrame(() => {
                        panel?.classList.remove('-translate-x-full');
                        backdrop?.classList.remove('opacity-0');
                    });
                    $$('[data-action="open-mobile-sidebar"]').forEach((trigger) => trigger.setAttribute('aria-expanded', 'true'));
                };

                const closeMobileSidebar = () => {
                    const drawer = $('[data-sidebar-drawer]');
                    const panel = $('[data-sidebar-panel]');
                    const backdrop = $('[data-sidebar-backdrop]');

                    panel?.classList.add('-translate-x-full');
                    backdrop?.classList.add('opacity-0');
                    drawer?.classList.remove('pointer-events-auto');
                    drawer?.classList.add('pointer-events-none');
                    window.setTimeout(() => drawer?.classList.add('hidden'), 180);
                    $$('[data-action="open-mobile-sidebar"]').forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));
                };

                const setDesktopSidebar = (isOpen) => {
                    const expanded = $('[data-sidebar-expanded]');
                    const collapsed = $('[data-sidebar-collapsed]');

                    expanded?.classList.toggle('lg:block', isOpen);
                    expanded?.classList.toggle('hidden', !isOpen);
                    collapsed?.classList.toggle('hidden', isOpen);
                    collapsed?.classList.toggle('lg:flex', !isOpen);
                    $('[data-action="expand-desktop-sidebar"]')?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
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
                    const form = $('[data-form="member"]');
                    if (form) {
                        clearFormErrors(form);
                    }
                    $('[data-member-modal] input[name="display_name"]')?.focus();
                };

                const closeMemberModal = () => {
                    const form = $('[data-form="member"]');
                    if (form) {
                        clearFormErrors(form);
                    }
                    $('[data-member-modal]')?.classList.add('hidden');
                    $('[data-member-modal]')?.classList.remove('flex');
                };

                const openRegistrationQrModal = async (memberId) => {
                    const modal = $('[data-registration-qr-modal]');
                    const code = $('[data-registration-qr-code]');
                    const url = $('[data-registration-qr-url]');
                    const description = $('[data-registration-qr-description]');

                    modal?.classList.remove('hidden');
                    modal?.classList.add('flex');
                    code.innerHTML = '<span class="text-sm font-bold text-slate-500">QRを読み込み中です。</span>';
                    url.value = '';

                    try {
                        const data = await api(`/api/admin/members/${memberId}/registration-qr`);
                        code.innerHTML = data.qr_svg;
                        url.value = data.registration_url;
                        description.textContent = `${data.member.display_name || data.member.name || 'スタッフ'}さん本人にこのQRを読み込んでもらうと、LINEミニアプリで登録できます。`;
                    } catch (error) {
                        code.innerHTML = `<span class="text-sm font-bold text-red-700">${escapeHtml(error.message)}</span>`;
                    }
                };

                const closeRegistrationQrModal = () => {
                    $('[data-registration-qr-modal]')?.classList.add('hidden');
                    $('[data-registration-qr-modal]')?.classList.remove('flex');
                };

                const copyRegistrationQrUrl = async () => {
                    const url = $('[data-registration-qr-url]')?.value;
                    if (!url) {
                        return;
                    }

                    await navigator.clipboard.writeText(url);
                    setMessage('[data-notice]', '登録URLをコピーしました。');
                };

                const setSidebarActive = (targetUrl = window.location.href) => {
                    const activePath = new URL(targetUrl, window.location.origin).pathname;

                    $$('[data-sidebar-link]').forEach((link) => {
                        const linkPath = new URL(link.href, window.location.origin).pathname;
                        const isActive = linkPath === activePath;

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
                        const error = new Error(message);
                        error.validationErrors = data.errors || {};
                        throw error;
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
                const memberDisplayName = (member) => member.display_name || member.name || member.line_name || 'スタッフ';
                const memberRoleLabel = (role) => ({
                    admin: '管理者',
                    manager: '店長',
                    cast: 'キャスト',
                }[role] || 'キャスト');
                const memberRoleClass = (role) => ({
                    admin: 'border-amber-200 bg-amber-50 text-amber-700',
                    manager: 'border-teal-200 bg-teal-50 text-teal-700',
                    cast: 'border-slate-200 bg-slate-50 text-slate-600',
                }[role] || 'border-slate-200 bg-slate-50 text-slate-600');

                const badge = (status) => {
                    const label = status === 'published' ? '公開済み' : status === 'archived' ? 'アーカイブ' : '下書き';
                    const klass = status === 'published' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : status === 'archived' ? 'border-slate-200 bg-slate-100 text-slate-500' : 'border-teal-200 bg-teal-50 text-teal-700';
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

                const clearFormErrors = (form) => {
                    form.querySelectorAll('[data-field-error]').forEach((element) => element.remove());
                    form.querySelectorAll('[aria-invalid="true"]').forEach((field) => {
                        field.removeAttribute('aria-invalid');
                        field.classList.remove('border-red-300', 'bg-red-50', 'focus:border-red-500');
                    });
                };

                const setFormFieldError = (form, fieldName, message) => {
                    const field = form.elements[fieldName];
                    if (!field || !message) {
                        return;
                    }

                    field.setAttribute('aria-invalid', 'true');
                    field.classList.add('border-red-300', 'bg-red-50', 'focus:border-red-500');

                    const error = document.createElement('p');
                    error.dataset.fieldError = fieldName;
                    error.className = 'mt-2 text-xs font-bold leading-5 text-red-700';
                    error.textContent = message;
                    field.insertAdjacentElement('afterend', error);
                };

                const focusFirstInvalidField = (form) => {
                    form.querySelector('[aria-invalid="true"]')?.focus();
                };

                const showFormValidationErrors = (form, errors) => {
                    clearFormErrors(form);

                    Object.entries(errors).forEach(([fieldName, messages]) => {
                        setFormFieldError(form, fieldName, Array.isArray(messages) ? messages[0] : messages);
                    });

                    focusFirstInvalidField(form);
                };

                const validateMemberForm = (form) => {
                    const errors = {};
                    const displayName = form.elements.display_name?.value.trim() || '';
                    const storeId = form.elements.store_id?.value || '';
                    const phone = form.elements.phone?.value.trim() || '';
                    const email = form.elements.email?.value.trim() || '';

                    if (!displayName) {
                        errors.display_name = ['表示名を入力してください。'];
                    } else if (displayName.length > 255) {
                        errors.display_name = ['表示名は255文字以内で入力してください。'];
                    }

                    if (!storeId) {
                        errors.store_id = ['店舗を選択してください。'];
                    }

                    if ((form.elements.name?.value.trim() || '').length > 255) {
                        errors.name = ['本名は255文字以内で入力してください。'];
                    }

                    if (phone.length > 50) {
                        errors.phone = ['電話は50文字以内で入力してください。'];
                    }

                    if (!email) {
                        errors.email = ['メールを入力してください。'];
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        errors.email = ['メールアドレスの形式で入力してください。'];
                    } else if (email.length > 255) {
                        errors.email = ['メールは255文字以内で入力してください。'];
                    }

                    showFormValidationErrors(form, errors);

                    return Object.keys(errors).length === 0;
                };

                const showLineTab = (scope, target) => {
                    $$(`[data-line-tab="${scope}"]`).forEach((button) => {
                        const active = button.dataset.lineTabTarget === target;
                        button.classList.toggle('border-teal-600', active);
                        button.classList.toggle('text-teal-700', active);
                        button.classList.toggle('border-transparent', !active);
                        button.classList.toggle('text-slate-500', !active);
                    });
                    $$(`[data-line-tab-panel="${scope}"]`).forEach((panel) => {
                        panel.classList.toggle('hidden', panel.dataset.lineTabPanelName !== target);
                    });
                };

                const lineSettingLabel = (form) => ({
                    official: '公式LINE設定',
                    line_login: 'LINEログイン設定',
                    liff: 'LIFF設定',
                }[form.elements.setting_type?.value] || 'LINE設定');

                const renderSelects = () => {
                    const options = state.stores.map((store) => `<option value="${store.id}">${escapeHtml(store.name)}</option>`).join('');
                    $$('select[name="store_id"]').forEach((select) => {
                        const current = select.value || select.dataset.initialValue || '';
                        const first = select.querySelector('option[value=""]') ? '<option value="">未割り当て</option>' : '';
                        select.innerHTML = first + options;
                        select.value = current;
                    });
                    $$('[data-filter="memberStore"], [data-filter="scheduleStore"], [data-filter="dashboardStore"]').forEach((select) => {
                        const current = select.value;
                        select.innerHTML = `<option value="">すべて</option>${options}`;
                        select.value = current;
                    });
                };

                const renderStores = () => {
                    const list = $('[data-list="stores"]');
                    if (!list) {
                        return;
                    }

                    list.innerHTML = state.stores.length
                        ? state.stores.map((store) => `
                            <tr class="hover:bg-gray-50">
                                <td class="min-w-[180px] px-5 py-4 font-medium text-gray-900">${escapeHtml(store.name)}</td>
                                <td class="px-5 py-4 text-gray-600">${escapeHtml(store.address || '住所未登録')}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <span class="rounded-full ${store.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'} px-2.5 py-1 text-xs font-semibold">
                                        ${store.is_active ? '稼働中' : '停止中'}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="${routes.storesBase}/${store.id}/edit" class="inline-flex rounded-md border border-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">編集</a>
                                </td>
                            </tr>
                        `).join('')
                        : '<tr><td colspan="4" class="px-5 py-8 text-center text-sm text-gray-500">店舗がまだ登録されていません。</td></tr>';
                };

                const renderMembers = () => {
                    const list = $('[data-list="members"]');
                    const filter = $('[data-filter="memberStore"]');
                    if (!list || !filter) {
                        return;
                    }

                    const storeId = filter.value;
                    const hasLineLoginChannel = Boolean(state.user.tenant?.line_login_setting?.channel_id);
                    const members = storeId ? state.members.filter((member) => String(member.store_id || '') === storeId) : state.members;
                    const lineAvatar = (member) => {
                        if (member.icon_url) {
                            return `<img src="${escapeHtml(member.icon_url)}" alt="" class="h-9 w-9 rounded-full object-cover ring-1 ring-slate-200">`;
                        }

                        return `<span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xs font-black text-slate-500 ring-1 ring-slate-200">${escapeHtml((member.line_name || memberDisplayName(member) || '?').slice(0, 1))}</span>`;
                    };
                    list.innerHTML = members.length
                        ? members.map((member) => `
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-black text-slate-950">${escapeHtml(memberDisplayName(member))}</div>
                                    ${member.name && member.name !== memberDisplayName(member) ? `<div class="mt-1 text-xs text-slate-500">${escapeHtml(member.name)}</div>` : ''}
                                </td>
                                <td class="px-4 py-3 text-slate-700">${escapeHtml(member.store?.name || '未割り当て')}</td>
                                <td class="px-4 py-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        ${lineAvatar(member)}
                                        <div class="min-w-0">
                                            <div class="truncate font-semibold text-slate-900">${escapeHtml(member.line_name || '未連携')}</div>
                                            <div class="text-xs ${member.is_linked ? 'text-emerald-700' : 'text-slate-500'}">${member.is_linked ? 'LINE連携済み' : 'LINE未連携'}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-black ${memberRoleClass(member.role)}">${memberRoleLabel(member.role)}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    <div>${escapeHtml(member.phone || '-')}</div>
                                    <div class="text-xs text-slate-500">${escapeHtml(member.email || '')}</div>
                                </td>
                                <td class="px-4 py-3"><span class="rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-black text-sky-700">${escapeHtml(member.status || 'active')}</span></td>
                                <td class="px-4 py-3 text-slate-700">${member.is_shift_submitter ? '対象' : '対象外'}</td>
                                <td class="max-w-xs truncate px-4 py-3 text-slate-500">${escapeHtml(member.remarks || '-')}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        ${hasLineLoginChannel ? `<button type="button" class="inline-flex rounded-md border border-teal-200 px-3 py-1.5 text-xs font-semibold text-teal-700 hover:bg-teal-50" data-registration-qr="${member.id}">登録QR</button>` : ''}
                                        <a href="${routes.membersBase}/${member.id}/edit" class="inline-flex rounded-md border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">編集</a>
                                    </div>
                                </td>
                            </tr>
                        `).join('')
                        : '<tr><td colspan="9" class="px-4 py-8 text-center text-sm text-slate-500">条件に一致するキャストがいません。</td></tr>';
                };

                const renderSchedules = () => {
                    const list = $('[data-list="schedules"]');
                    const filter = $('[data-filter="scheduleStore"]');
                    if (!list || !filter) {
                        return;
                    }

                    const storeId = filter.value;
                    const schedules = storeId ? state.schedules.filter((schedule) => String(schedule.store_id || '') === storeId) : state.schedules;
                    list.innerHTML = schedules.length
                        ? schedules.map((schedule) => `
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3 font-black text-slate-950">${escapeHtml(schedule.store?.name || '-')}</td>
                                <td class="px-4 py-3 text-slate-700">${escapeHtml(schedule.starts_on)} - ${escapeHtml(schedule.ends_on)}</td>
                                <td class="px-4 py-3">${badge(schedule.status)}</td>
                                <td class="px-4 py-3 text-slate-700">${schedule.shift_slots?.length || 0}</td>
                                <td class="px-4 py-3 text-right">
                                    ${schedule.status === 'published'
                                        ? '<span class="text-xs font-black text-slate-500">公開済み</span>'
                                        : `<button type="button" class="rounded-xl border border-teal-200 px-3 py-1.5 text-xs font-black text-teal-700 transition hover:bg-teal-700 hover:text-white" data-publish="${schedule.id}">公開</button>`}
                                </td>
                            </tr>
                        `).join('')
                        : '<tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">シフト表がまだありません。</td></tr>';
                };

                const renderDashboard = () => {
                    const dashboardFilter = $('[data-filter="dashboardStore"]');
                    const dashboardTable = $('[data-list="dashboardSchedules"]');
                    const chart = $('[data-dashboard-chart]');
                    const axis = $('[data-dashboard-axis]');
                    const caption = $('[data-dashboard-chart-caption]');

                    if (!dashboardFilter || !dashboardTable || !chart || !axis) {
                        return;
                    }

                    const storeId = dashboardFilter.value;
                    const schedules = storeId
                        ? state.schedules.filter((schedule) => String(schedule.store_id || '') === storeId)
                        : state.schedules;
                    const members = storeId
                        ? state.members.filter((member) => String(member.store_id || '') === storeId)
                        : state.members;
                    const selectedStore = storeId ? state.stores.find((store) => String(store.id) === storeId) : null;

                    $('[data-stat="stores"]') && ($('[data-stat="stores"]').textContent = storeId ? 1 : state.stores.length);
                    $('[data-stat="members"]') && ($('[data-stat="members"]').textContent = members.length);
                    $('[data-stat="submitters"]') && ($('[data-stat="submitters"]').textContent = members.filter((member) => member.is_shift_submitter).length);
                    $('[data-stat="published"]') && ($('[data-stat="published"]').textContent = schedules.filter((schedule) => schedule.status === 'published').length);
                    if (caption) {
                        caption.textContent = selectedStore
                            ? `${selectedStore.name}の公開済み・下書きシフトを表示しています。`
                            : '全店舗の公開済み・下書きシフトを表示しています。';
                    }

                    const buckets = new Map();
                    schedules.forEach((schedule) => {
                        const label = schedule.starts_on || '-';
                        if (!buckets.has(label)) {
                            buckets.set(label, { label, published: 0, draft: 0 });
                        }
                        const bucket = buckets.get(label);
                        if (schedule.status === 'published') {
                            bucket.published += 1;
                        } else {
                            bucket.draft += 1;
                        }
                    });
                    const series = Array.from(buckets.values()).sort((a, b) => a.label.localeCompare(b.label)).slice(-8);
                    const maxValue = Math.max(1, ...series.flatMap((item) => [item.published, item.draft]));
                    const xFor = (index) => series.length <= 1 ? 390 : 50 + (index * (680 / (series.length - 1)));
                    const yFor = (value) => 200 - (value / maxValue) * 170;
                    const publishedPoints = series.map((item, index) => `${xFor(index)},${yFor(item.published)}`).join(' ');
                    const draftPoints = series.map((item, index) => `${xFor(index)},${yFor(item.draft)}`).join(' ');
                    const verticalLines = series.map((item, index) => `<line x1="${xFor(index)}" y1="20" x2="${xFor(index)}" y2="200"/>`).join('');
                    const publishedDots = series.map((item, index) => `<circle cx="${xFor(index)}" cy="${yFor(item.published)}" r="4" fill="#ffffff" stroke="#16a34a" stroke-width="2"/>`).join('');
                    const draftDots = series.map((item, index) => `<circle cx="${xFor(index)}" cy="${yFor(item.draft)}" r="3.5" fill="#ffffff" stroke="#f59e0b" stroke-width="2"/>`).join('');

                    chart.innerHTML = `
                        <g stroke="#e5e7eb" stroke-width="1">
                            <path d="M50 30H730M50 86H730M50 143H730M50 200H730"/>
                            ${verticalLines}
                        </g>
                        <path d="M50 20V200H730" fill="none" stroke="#cbd5e1" stroke-width="1.5" vector-effect="non-scaling-stroke"/>
                        ${series.length ? `<polyline fill="none" stroke="#16a34a" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" vector-effect="non-scaling-stroke" points="${publishedPoints}"/>` : ''}
                        ${series.length ? `<polyline fill="none" stroke="#f59e0b" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" vector-effect="non-scaling-stroke" points="${draftPoints}"/>` : ''}
                        ${publishedDots}
                        ${draftDots}
                    `;
                    axis.innerHTML = series.length
                        ? series.map((item, index) => `<span class="absolute top-1 -translate-x-1/2 whitespace-nowrap" style="left: ${((xFor(index) - 50) / 680) * 92 + 4}%;">${escapeHtml(item.label.slice(5))}</span>`).join('')
                        : '<span class="absolute left-1/2 top-1 -translate-x-1/2 whitespace-nowrap">データなし</span>';

                    dashboardTable.innerHTML = schedules.length
                        ? schedules.slice(0, 10).map((schedule) => `
                            <tr class="hover:bg-gray-50">
                                <td class="min-w-[180px] px-5 py-4 font-medium text-gray-900">${escapeHtml(schedule.store?.name || '-')}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-left text-gray-600">
                                    <span class="block">${escapeHtml(schedule.starts_on || '-')}</span>
                                    <span class="mt-1 block text-xs text-gray-400">- ${escapeHtml(schedule.ends_on || '-')}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right text-gray-600">${schedule.shift_slots?.length || 0}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">${badge(schedule.status)}</td>
                                <td class="px-5 py-4 text-right">
                                    ${schedule.status === 'published'
                                        ? '<span class="text-xs font-semibold text-gray-500">公開済み</span>'
                                        : `<button type="button" class="inline-flex rounded-md border border-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50" data-publish="${schedule.id}">公開</button>`}
                                </td>
                            </tr>
                        `).join('')
                        : '<tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">シフト表がありません。</td></tr>';
                };

                const renderStats = () => {
                    const tenantName = state.user.tenant?.name || @json(Auth::user()->tenant?->name ?? '');
                    $('[data-bind="tenantName"]').textContent = tenantName;
                    $('[data-stat="stores"]') && ($('[data-stat="stores"]').textContent = state.stores.length);
                    $('[data-stat="members"]') && ($('[data-stat="members"]').textContent = state.members.length);
                    $('[data-stat="submitters"]') && ($('[data-stat="submitters"]').textContent = state.members.filter((member) => member.is_shift_submitter).length);
                    $('[data-stat="published"]') && ($('[data-stat="published"]').textContent = state.schedules.filter((schedule) => schedule.status === 'published').length);
                };

                const render = () => {
                    renderSelects();
                    renderStores();
                    renderMembers();
                    renderSchedules();
                    renderStats();
                    renderDashboard();
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

                $('[data-form="store"]')?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    try {
                        await api('/api/admin/stores', { method: 'POST', body: JSON.stringify({ timezone: 'Asia/Tokyo', is_active: true, ...formPayload(form) }) });
                        form.reset();
                        await load();
                        setMessage('[data-notice]', '店舗を追加しました。');
                        window.location.href = @json(route('admin.stores'));
                    } catch (error) {
                        setMessage('[data-alert]', error.message);
                    }
                });

                $('[data-form="store-edit"]')?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    const payload = { timezone: 'Asia/Tokyo', is_active: false, ...formPayload(form) };
                    try {
                        await api(`/api/admin/stores/${form.dataset.storeId}`, { method: 'PUT', body: JSON.stringify(payload) });
                        await load();
                        setMessage('[data-notice]', '店舗を更新しました。');
                        window.location.href = @json(route('admin.stores'));
                    } catch (error) {
                        setMessage('[data-alert]', error.message);
                    }
                });

                $$('[data-form="tenant-settings"]').forEach((tenantSettingsForm) => {
                    tenantSettingsForm.addEventListener('submit', async (event) => {
                        event.preventDefault();
                        const form = event.currentTarget;
                        try {
                            const data = await api('/api/admin/tenant/settings', { method: 'PUT', body: JSON.stringify(formPayload(form)) });
                            state.user.tenant = data.tenant;
                            renderStats();
                            setMessage('[data-notice]', `${lineSettingLabel(form)}を保存しました。`);
                        } catch (error) {
                            setMessage('[data-alert]', error.message);
                        }
                    });
                });

                $$('[data-form="member"], [data-form="member-edit"]').forEach((memberForm) => {
                    memberForm.addEventListener('input', (event) => {
                        const field = event.target.closest('input, select, textarea');
                        if (!field?.name) {
                            return;
                        }

                        field.removeAttribute('aria-invalid');
                        field.classList.remove('border-red-300', 'bg-red-50', 'focus:border-red-500');
                        memberForm.querySelector(`[data-field-error="${CSS.escape(field.name)}"]`)?.remove();
                    });
                });

                $('[data-form="member"]')?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    if (!validateMemberForm(form)) {
                        return;
                    }

                    try {
                        await api('/api/admin/members', { method: 'POST', body: JSON.stringify({ status: 'active', is_shift_submitter: true, ...formPayload(form) }) });
                        form.reset();
                        clearFormErrors(form);
                        closeMemberModal();
                        await load();
                        setMessage('[data-notice]', 'キャストを追加しました。');
                    } catch (error) {
                        if (error.validationErrors && Object.keys(error.validationErrors).length > 0) {
                            showFormValidationErrors(form, error.validationErrors);
                        }
                        setMessage('[data-alert]', error.message);
                    }
                });

                $('[data-form="member-edit"]')?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    if (!validateMemberForm(form)) {
                        return;
                    }

                    const payload = { is_shift_submitter: false, is_remind_disabled: false, ...formPayload(form) };
                    payload.store_id = form.elements.store_id.value || null;
                    try {
                        await api(`/api/admin/members/${form.dataset.memberId}`, { method: 'PUT', body: JSON.stringify(payload) });
                        clearFormErrors(form);
                        await load();
                        setMessage('[data-notice]', 'キャストを更新しました。');
                        window.location.href = @json(route('admin.members'));
                    } catch (error) {
                        if (error.validationErrors && Object.keys(error.validationErrors).length > 0) {
                            showFormValidationErrors(form, error.validationErrors);
                        }
                        setMessage('[data-alert]', error.message);
                    }
                });

                $('[data-form="schedule"]')?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    try {
                        await api('/api/admin/shift-schedules', { method: 'POST', body: JSON.stringify({ status: 'draft', ...formPayload(form) }) });
                        form.reset();
                        await load();
                        setMessage('[data-notice]', 'シフト表を作成しました。');
                        window.location.href = @json(route('admin.schedules'));
                    } catch (error) {
                        setMessage('[data-alert]', error.message);
                    }
                });

                $('[data-form="dashboard-filter"]')?.addEventListener('submit', (event) => {
                    event.preventDefault();
                    renderDashboard();
                });

                document.addEventListener('click', async (event) => {
                    const lineTab = event.target.closest('[data-line-tab]');
                    if (lineTab) {
                        showLineTab(lineTab.dataset.lineTab, lineTab.dataset.lineTabTarget);
                    }

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

                    const sidebarLink = event.target.closest('[data-sidebar-link]');
                    if (sidebarLink) {
                        setSidebarActive(sidebarLink.href);
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

                    const registrationQrButton = event.target.closest('[data-registration-qr]');
                    if (registrationQrButton) {
                        openRegistrationQrModal(registrationQrButton.dataset.registrationQr);
                    }

                    if (event.target.closest('[data-action="close-registration-qr-modal"]')) {
                        closeRegistrationQrModal();
                    }

                    if (event.target.closest('[data-action="copy-registration-qr-url"]')) {
                        copyRegistrationQrUrl();
                    }

                    if (event.target.closest('[data-action="reset-dashboard-filter"]')) {
                        const dashboardStore = $('[data-filter="dashboardStore"]');
                        if (dashboardStore) {
                            dashboardStore.value = '';
                            renderDashboard();
                        }
                    }

                    if (event.target.closest('[data-action="close-member-modal"]')) {
                        closeMemberModal();
                    }

                    if (event.target === $('[data-member-modal]')) {
                        closeMemberModal();
                    }

                    if (event.target === $('[data-registration-qr-modal]')) {
                        closeRegistrationQrModal();
                    }

                    if (event.target.closest('[data-action="open-mobile-sidebar"]')) {
                        openMobileSidebar();
                    }

                    if (event.target.closest('[data-action="close-mobile-sidebar"]')) {
                        closeMobileSidebar();
                    }

                    if (event.target.closest('[data-action="collapse-desktop-sidebar"]')) {
                        setDesktopSidebar(false);
                    }

                    if (event.target.closest('[data-action="expand-desktop-sidebar"]')) {
                        setDesktopSidebar(true);
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeMobileSidebar();
                        closeAccountMenu();
                        closeMemberModal();
                        closeRegistrationQrModal();
                    }
                });

                window.addEventListener('hashchange', () => setSidebarActive());

                $$('[data-filter="memberStore"], [data-filter="scheduleStore"]').forEach((select) => {
                    select.addEventListener('change', render);
                });

                setSidebarActive();
                render();
                load().catch((error) => setMessage('[data-alert]', error.message));
            })();
        </script>
    </body>
</html>
