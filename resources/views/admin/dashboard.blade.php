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
                    'iconAsset' => 'schedule.png',
                    'href' => route('admin.schedules'),
                    'active' => in_array($page, ['schedules', 'schedule-create', 'schedule-edit'], true),
                    'icon' => 'M8 7V3m8 4V3M5 11h14M6 5h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2z',
                ],
                [
                    'label' => '運用機能',
                    'href' => route('admin.workforce'),
                    'active' => false,
                    'icon' => 'M4 6h16M4 12h16M4 18h16',
                ],
                [
                    'label' => 'シフト分析',
                    'href' => route('admin.analytics'),
                    'active' => $page === 'analytics',
                    'iconAsset' => 'diagnosis.png',
                    'icon' => '',
                ],
                [
                    'label' => 'キャスト管理',
                    'iconAsset' => 'members.png',
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
        <x-favicon />
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex">

        <title>管理画面 - {{ config('app.name', 'ShiftHub') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="hub-theme hub-admin min-h-screen bg-gray-100 text-gray-900 antialiased">
        <div id="adminApp" class="min-h-screen bg-gray-100 pt-16">
            <header class="hub-app-header fixed inset-x-0 top-0 z-40 border-b border-gray-300 bg-white shadow-sm">
                <div class="mx-auto flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="flex h-16 items-center gap-3">
                            <x-brand-mark />
                            <span>
                                <span class="hub-wordmark block font-black leading-tight">Shift<span>Hub</span></span>
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
                                @if (isset($item['iconAsset']))
                                    <img src="{{ asset('images/rshift/'.$item['iconAsset']) }}" class="hub-reference-nav-icon" alt="" aria-hidden="true">
                                @else
                                    <svg class="size-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                                </svg>
                                @endif
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
                                @if (isset($item['iconAsset']))
                                    <img src="{{ asset('images/rshift/'.$item['iconAsset']) }}" class="hub-reference-nav-icon" alt="" aria-hidden="true">
                                @else
                                    <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                                </svg>
                                @endif
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
                                @if (isset($item['iconAsset']))
                                    <img src="{{ asset('images/rshift/'.$item['iconAsset']) }}" class="hub-reference-nav-icon" alt="" aria-hidden="true">
                                @else
                                    <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                                </svg>
                                @endif
                                <span class="min-w-0 whitespace-nowrap">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </aside>

                <main class="min-w-0 flex-1">
                    <div class="hub-workspace mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
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

                        @if ($page === 'analytics')
                            @include('admin.analytics')
                        @endif

                        @if ($page === 'overview')
                        <section class="hub-overview-banner overflow-hidden rounded-2xl p-5 sm:p-7">
                            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.2em] text-teal-700">Shift operations</p>
                                    <h2 class="mt-2 text-2xl font-black">今月のシフト運用</h2>
                                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">希望回収から自動編成、確認、LINEでの公開までを一つの画面で管理できます。</p>
                                </div>
                                <div class="flex flex-col gap-2 sm:flex-row">
                                    <form class="flex gap-2" data-form="dashboard-filter">
                                        <select aria-label="表示する店舗" class="min-w-44 rounded-lg border border-teal-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500" data-filter="dashboardStore">
                                            <option class="text-slate-900" value="">すべての店舗</option>
                                        </select>
                                        <button type="submit" class="rounded-full border border-teal-200 bg-white px-4 py-2 text-sm font-bold text-teal-700 hover:bg-teal-50">反映</button>
                                    </form>
                                    <a href="{{ route('admin.schedules.create') }}" class="inline-flex items-center justify-center rounded-full bg-teal-700 px-4 py-2 text-sm font-bold text-white transition hover:bg-teal-800">＋ 月間シフトを作成</a>
                                </div>
                            </div>
                        </section>

                        <section class="shift-calendar-panel" aria-label="シフトカレンダー" data-shift-calendar>
                            <script type="application/json" data-calendar-source>{}</script>
                            <div data-calendar-content></div>
                        </section>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-bold text-slate-500">提出待ちメンバー</p>
                                <div class="mt-2 flex items-end justify-between"><p class="text-3xl font-black text-slate-950" data-stat="unsubmitted">0</p><span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-bold text-amber-700">要フォロー</span></div>
                            </article>
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-bold text-slate-500">人員不足</p>
                                <div class="mt-2 flex items-end justify-between"><p class="text-3xl font-black text-slate-950"><span data-stat="shortage">0</span><span class="ml-1 text-sm">枠</span></p><span class="rounded-full bg-red-50 px-2 py-1 text-xs font-bold text-red-700">要調整</span></div>
                            </article>
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-bold text-slate-500">編成済み</p>
                                <div class="mt-2 flex items-end justify-between"><p class="text-3xl font-black text-slate-950" data-stat="scheduled">0</p><span class="text-xs font-bold text-slate-400">月間シフト</span></div>
                            </article>
                            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <p class="text-xs font-bold text-slate-500">LINE通知済み</p>
                                <div class="mt-2 flex items-end justify-between"><p class="text-3xl font-black text-slate-950" data-stat="notified">0</p><span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">配信完了</span></div>
                            </article>
                        </div>

                        <section class="mt-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div><h3 class="font-black text-slate-950">運用フロー</h3><p class="mt-1 text-xs text-slate-500">各月のシフトがどこまで進んでいるかを確認できます。</p></div>
                                <a href="{{ route('admin.schedules') }}" class="text-sm font-bold text-teal-700 hover:text-teal-900">すべてのシフトを見る →</a>
                            </div>
                            <div class="mt-5 grid gap-3 md:grid-cols-4" data-dashboard-workflow></div>
                        </section>

                        <div class="mt-5 grid gap-5 xl:grid-cols-[1fr_360px]">
                            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                <div class="border-b border-slate-200 px-5 py-4"><h3 class="font-black text-slate-950">月間シフト一覧</h3><p class="mt-1 text-xs text-slate-500">提出状況と人員充足率を優先して表示します。</p></div>
                                <div class="overflow-x-auto"><table class="min-w-[760px] w-full text-sm"><thead class="bg-slate-50 text-left text-xs font-bold text-slate-500"><tr><th class="px-5 py-3">対象月・店舗</th><th class="px-5 py-3">希望提出</th><th class="px-5 py-3">人員充足</th><th class="px-5 py-3">工程</th><th class="px-5 py-3 text-right">操作</th></tr></thead><tbody class="divide-y divide-slate-100" data-list="dashboardSchedules"></tbody></table></div>
                            </section>
                            <aside class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="flex items-center justify-between"><h3 class="font-black text-slate-950">対応が必要</h3><span class="rounded-full bg-red-50 px-2 py-1 text-xs font-black text-red-700" data-action-count>0件</span></div>
                                <div class="mt-4 space-y-3" data-dashboard-actions></div>
                            </aside>
                        </div>
                        @endif

                        @if ($page === 'schedules')
                        <div class="mb-5 rounded-xl border border-teal-200 bg-teal-50 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div><p class="text-xs font-black uppercase tracking-wider text-teal-700">Monthly planning</p><h2 class="mt-1 text-xl font-black text-slate-950">月間シフト管理</h2><p class="mt-1 text-sm text-slate-600">希望提出、必要人数、自動編成、公開状況を月単位で管理します。</p></div>
                                <a href="{{ route('admin.schedules.create') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-black text-white transition hover:bg-teal-800">＋ 月間シフトを作成</a>
                            </div>
                        </div>
                        <div class="mb-5 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-bold text-slate-500">希望回収中</p><p class="mt-2 text-2xl font-black text-slate-950" data-schedule-stat="collecting">0</p></div>
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-bold text-slate-500">人員不足</p><p class="mt-2 text-2xl font-black text-red-700"><span data-schedule-stat="shortage">0</span><span class="ml-1 text-xs">枠</span></p></div>
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-bold text-slate-500">公開済み</p><p class="mt-2 text-2xl font-black text-emerald-700" data-schedule-stat="published">0</p></div>
                        </div>
                        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                    <div class="border-b border-slate-200 px-4 py-3">
                                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <h3 class="font-black text-slate-950">シフト一覧</h3>
                                                <p class="mt-1 text-xs text-slate-500">不足があるシフトを優先して確認してください。</p>
                                            </div>
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                                                    店舗
                                                    <select class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500" data-filter="scheduleStore">
                                                        <option value="">すべて</option>
                                                    </select>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-[900px] w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-50 text-left text-xs font-black text-slate-500">
                                                <tr>
                                                    <th class="px-4 py-3">対象月・店舗</th>
                                                    <th class="px-4 py-3">希望提出</th>
                                                    <th class="px-4 py-3">人員充足</th>
                                                    <th class="px-4 py-3">担当メンバー</th>
                                                    <th class="px-4 py-3">工程</th>
                                                    <th class="px-4 py-3 text-right">操作</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-200" data-list="schedules"></tbody>
                                        </table>
                                    </div>
                        </section>
                        @endif

                        @if (in_array($page, ['schedule-create', 'schedule-edit'], true))
                        <section class="w-full rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-wider text-teal-700">Monthly planning</p>
                                    <h2 class="mt-1 text-xl font-black text-slate-950">{{ $page === 'schedule-edit' ? '月間シフトの確認・調整' : '月間シフトの作成' }}</h2>
                                </div>
                                <a href="{{ route('admin.schedules') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                    一覧へ戻る
                                </a>
                            </div>
                            <div class="mb-5 grid grid-cols-2 gap-2 md:grid-cols-4">
                                @foreach ([['1', '基本設定'], ['2', '希望回収'], ['3', '自動編成・調整'], ['4', '公開・LINE通知']] as [$stepNumber, $stepLabel])
                                    <div class="rounded-lg border px-3 py-2 {{ ($page === 'schedule-create' && $stepNumber === '1') || ($page === 'schedule-edit' && $stepNumber !== '1') ? 'border-teal-200 bg-teal-50' : 'border-slate-200 bg-slate-50' }}">
                                        <p class="text-[10px] font-black text-teal-700">STEP {{ $stepNumber }}</p><p class="mt-0.5 text-xs font-bold text-slate-700">{{ $stepLabel }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <form class="space-y-4" data-form="schedule" @if($page === 'schedule-edit') data-schedule-id="{{ $editingSchedule->id }}" @endif>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">店舗</label>
                                    <select name="store_id" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white"></select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">対象月</label>
                                    <input type="month" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white" data-schedule-month>
                                    <input name="starts_on" type="hidden">
                                    <input name="ends_on" type="hidden">
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700">シフト提出期限</label>
                                        <p class="mt-1 text-xs text-slate-500">自動編成の有無にかかわらず、締切後はスタッフの提出・変更を受け付けません。</p>
                                        <input name="submission_deadline_at" type="datetime-local" value="{{ $page === 'schedule-edit' ? $editingSchedule->submission_deadline_at?->format('Y-m-d\TH:i') : '' }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                    </div>
                                    <label class="flex items-center gap-3 self-end rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <input type="hidden" name="auto_schedule_enabled" value="0">
                                        <input type="checkbox" name="auto_schedule_enabled" value="1" @checked($page !== 'schedule-edit' || $editingSchedule->auto_schedule_enabled) class="rounded border-slate-300 text-teal-700 accent-teal-700">
                                        <span>
                                            <span class="block text-sm font-bold text-slate-800">期限後に自動編成・LINE通知</span>
                                            <span class="mt-1 block text-xs text-slate-500">希望提出と非公開評価から割り当てます。</span>
                                        </span>
                                    </label>
                                </div>
                                @if ($page === 'schedule-edit')
                                    @php
                                        $assignedMembers = $editingSchedule->shiftSlots
                                            ->flatMap->assignments
                                            ->filter(fn ($assignment) => $assignment->member && $assignment->status !== 'cancelled')
                                            ->pluck('member')
                                            ->unique('id');
                                        $unassignedSlotCount = $editingSchedule->shiftSlots->sum(fn ($slot) => max(
                                            0,
                                            $slot->required_headcount - $slot->assignments->filter(fn ($assignment) => $assignment->member && $assignment->status !== 'cancelled')->count(),
                                        ));
                                    @endphp
                                    @include('admin.submission-members')
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                        <div class="grid gap-3 border-b border-slate-200 pb-3 sm:grid-cols-3">
                                            <div><p class="text-xs font-bold text-slate-500">希望提出</p><p class="mt-1 text-lg font-black text-slate-950">{{ data_get($editingSchedule, 'operations.completed_members', 0) }} / {{ data_get($editingSchedule, 'operations.eligible_members', 0) }}名</p></div>
                                            <div><p class="text-xs font-bold text-slate-500">人員充足</p><p class="mt-1 text-lg font-black {{ data_get($editingSchedule, 'operations.shortage_headcount', 0) > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ data_get($editingSchedule, 'operations.assigned_headcount', 0) }} / {{ data_get($editingSchedule, 'operations.required_headcount', 0) }}枠</p></div>
                                            <div><p class="text-xs font-bold text-slate-500">LINE通知</p><p class="mt-1 text-lg font-black {{ $editingSchedule->notification_sent_at ? 'text-emerald-700' : 'text-slate-500' }}">{{ $editingSchedule->notification_sent_at ? '通知済み' : '未通知' }}</p></div>
                                        </div>
                                        <p class="mt-3 text-xs font-black text-slate-600">現在の担当メンバー</p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @forelse ($assignedMembers as $assignedMember)
                                                <span class="inline-flex rounded-full border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-700">{{ $assignedMember->displayName() }}</span>
                                            @empty
                                                <span class="text-sm font-bold text-amber-700">担当メンバーは未設定です</span>
                                            @endforelse
                                            @if ($unassignedSlotCount > 0)
                                                <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">人員不足 {{ $unassignedSlotCount }}名</span>
                                            @endif
                                        </div>
                                        @if ($editingSchedule->shiftSlots->isNotEmpty())
                                            <div class="mt-3 divide-y divide-slate-200 rounded-md border border-slate-200 bg-white">
                                                @foreach ($editingSchedule->shiftSlots->sortBy('starts_at') as $shiftSlot)
                                                    @php
                                                        $slotMembers = $shiftSlot->assignments
                                                            ->filter(fn ($assignment) => $assignment->member && $assignment->status !== 'cancelled')
                                                            ->pluck('member')
                                                            ->unique('id');
                                                    @endphp
                                                    <div class="p-3" data-shift-slot-assignment="{{ $shiftSlot->id }}">
                                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                            <div>
                                                            <p class="text-sm font-bold text-slate-800">{{ $shiftSlot->title ?: 'シフト' }}</p>
                                                            <p class="mt-0.5 text-xs text-slate-500">
                                                                {{ $shiftSlot->starts_at?->format('n/j H:i') ?: '--:--' }}〜{{ $shiftSlot->ends_at?->format('H:i') ?: '--:--' }}
                                                            </p>
                                                            </div>
                                                            <div class="flex flex-wrap gap-1.5">
                                                            @forelse ($slotMembers as $slotMember)
                                                                    <span class="inline-flex rounded-full border border-teal-200 bg-teal-50 px-2 py-0.5 text-xs font-bold text-teal-700">{{ $slotMember->displayName() }}</span>
                                                                @empty
                                                                    <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">未割り当て</span>
                                                            @endforelse
                                                            @if ($slotMembers->count() < $shiftSlot->required_headcount)
                                                                <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-xs font-bold text-red-700">不足 {{ $shiftSlot->required_headcount - $slotMembers->count() }}名</span>
                                                            @endif
                                                            </div>
                                                        </div>
                                                        <details class="mt-3 rounded-md border border-slate-200 bg-slate-50">
                                                            <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-teal-700">担当メンバーを選択・変更</summary>
                                                            <div class="border-t border-slate-200 p-3">
                                                                <div class="grid max-h-52 grid-cols-1 gap-2 overflow-y-auto sm:grid-cols-2">
                                                                    @foreach (($initialData['members'] ?? collect()) as $selectableMember)
                                                                        <label class="flex cursor-pointer items-center gap-2 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 hover:border-teal-300">
                                                                            <input type="checkbox" value="{{ $selectableMember->id }}" class="rounded border-slate-300 text-teal-700 accent-teal-700" data-shift-slot-member @checked($slotMembers->contains('id', $selectableMember->id))>
                                                                            <span class="min-w-0">
                                                                                <span class="block truncate font-bold">{{ $selectableMember->displayName() }}</span>
                                                                                <span class="block truncate text-xs text-slate-500">{{ $selectableMember->store?->name ?: '店舗未設定' }}</span>
                                                                            </span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                                <div class="mt-3 flex justify-end">
                                                                    <button type="button" class="rounded-md bg-teal-700 px-3 py-2 text-xs font-bold text-white transition hover:bg-teal-800 disabled:opacity-50" data-action="save-shift-slot-members">担当を保存</button>
                                                                </div>
                                                            </div>
                                                        </details>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-black text-slate-800">日別店舗</p>
                                        <span class="text-xs font-bold text-slate-500">1日〜月末</span>
                                    </div>
                                    <div class="mt-3 rounded-md border border-slate-200 bg-white p-3">
                                        <p class="text-xs font-black text-slate-600">一括設定</p>
                                        <div class="mt-2 grid grid-cols-[1fr_auto] gap-2">
                                            <select aria-label="一括設定する店舗" class="min-h-10 rounded-xl border border-slate-200 bg-white px-2 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500" data-bulk-store></select>
                                            <button type="button" class="rounded-md bg-slate-800 px-3 py-2 text-xs font-bold text-white transition hover:bg-slate-950" data-action="apply-bulk-store">全日に適用</button>
                                        </div>
                                        <div class="mt-2 grid grid-cols-[1fr_1fr_auto] gap-2">
                                            <select class="min-h-10 rounded-xl border border-slate-200 bg-white px-2 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500" data-bulk-start></select>
                                            <select class="min-h-10 rounded-xl border border-slate-200 bg-white px-2 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500" data-bulk-end></select>
                                            <button type="button" class="rounded-md bg-slate-800 px-3 py-2 text-xs font-bold text-white transition hover:bg-slate-950" data-action="apply-bulk-time">適用</button>
                                        </div>
                                        <div class="mt-3 border-t border-slate-100 pt-3">
                                            <div class="flex flex-wrap gap-2" data-bulk-weekdays>
                                                @foreach (['日', '月', '火', '水', '木', '金', '土'] as $index => $weekday)
                                                    <label class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-2 py-1 text-xs font-bold text-slate-600">
                                                        <input type="checkbox" class="rounded border-slate-300 text-teal-700 accent-teal-700" value="{{ $index }}" data-bulk-weekday>
                                                        {{ $weekday }}
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="mt-2 grid grid-cols-2 gap-2">
                                                <button type="button" class="rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50" data-action="apply-bulk-day-off">休みにする</button>
                                                <button type="button" class="rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50" data-action="clear-bulk-day-off">休み解除</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 max-h-[42rem] overflow-auto pr-1" data-list="schedule-days">
                                        <p class="py-3 text-sm text-slate-500">開始日と終了日を選択してください。</p>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
                                    <a href="{{ route('admin.schedules') }}" class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                                        キャンセル
                                    </a>
                                    <button class="inline-flex items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                                        {{ $page === 'schedule-edit' ? 'シフト表を保存' : 'シフト表を作成' }}
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
                                <div>
                                    <label class="block text-sm font-bold text-slate-700">入店日</label>
                                    <input name="joined_on" type="date" value="{{ old('joined_on', $editingMember->joined_on?->format('Y-m-d')) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                    <p class="mt-1 text-xs text-slate-500">入店日から3か月間は新人として自動編成で優先されます。</p>
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
                                <section class="rounded-lg border border-amber-200 bg-amber-50/60 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-black text-slate-800">自動シフト編成評価</p>
                                            <p class="mt-1 text-xs font-bold text-amber-700">管理者専用・メンバーには表示されません</p>
                                        </div>
                                        <span class="rounded-full border border-amber-200 bg-white px-2.5 py-1 text-xs font-black text-amber-700">非公開</span>
                                    </div>
                                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">勤怠評価（0〜100）</label>
                                            <input name="attendance_score" type="number" min="0" max="100" value="{{ old('attendance_score', $editingMember->schedulingProfile?->attendance_score ?? 50) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">人気度・接客評価（0〜100）</label>
                                            <input name="popularity_score" type="number" min="0" max="100" value="{{ old('popularity_score', $editingMember->schedulingProfile?->popularity_score ?? 50) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">優先ポイント（-1000〜1000）</label>
                                            <input name="priority_points" type="number" min="-1000" max="1000" value="{{ old('priority_points', $editingMember->schedulingProfile?->priority_points ?? 0) }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">新人扱い期間</label>
                                            <div class="mt-2 min-h-11 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                                @if ($editingMember->newcomerUntil())
                                                    {{ $editingMember->joined_on->format('Y/m/d') }}〜{{ $editingMember->newcomerUntil()->format('Y/m/d') }}
                                                @else
                                                    入店日を設定してください
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs text-slate-500">入店日から3か月間で自動計算します。</p>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-sm font-bold text-slate-700">編成用の管理者メモ</label>
                                        <textarea name="scheduling_admin_notes" rows="2" class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500">{{ old('scheduling_admin_notes', $editingMember->schedulingProfile?->admin_notes) }}</textarea>
                                    </div>
                                </section>
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
                    <div>
                        <label class="block text-sm font-bold text-slate-700">入店日</label>
                        <input name="joined_on" type="date" value="{{ now()->format('Y-m-d') }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                        <p class="mt-1 text-xs text-slate-500">この日から3か月間、新人として自動編成で優先します。</p>
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
                    schedulesBase: @json(url('/dashboard/schedules')),
                };
                const editingSchedule = @json($editingSchedule ?? null);

                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const $ = (selector) => document.querySelector(selector);
                const $$ = (selector) => Array.from(document.querySelectorAll(selector));
                const weekdays = ['日', '月', '火', '水', '木', '金', '土'];

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
                    const contentType = response.headers.get('content-type') || '';
                    const data = text && contentType.includes('application/json') ? JSON.parse(text) : {};
                    if (!response.ok) {
                        const message = data.message || Object.values(data.errors || {}).flat().join('\n') || `処理に失敗しました。HTTP ${response.status}`;
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

                let noticeTimer = null;
                const setMessage = (selector, message, options = {}) => {
                    const element = $(selector);
                    if (selector === '[data-notice]') {
                        window.clearTimeout(noticeTimer);
                    }
                    element.textContent = message;
                    element.classList.toggle('hidden', !message);
                    if (message) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    if (message && selector === '[data-notice]' && options.autoHide !== false) {
                        noticeTimer = window.setTimeout(() => {
                            element.classList.add('hidden');
                            element.textContent = '';
                        }, 3500);
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

                const normalizeLineAtId = (value) => {
                    const lineAtId = String(value || '').trim();
                    if (!lineAtId) {
                        return '';
                    }

                    return lineAtId.startsWith('@') ? lineAtId : `@${lineAtId}`;
                };

                const lineTimelineUrl = (lineAtId) => {
                    const normalizedLineAtId = normalizeLineAtId(lineAtId);

                    return normalizedLineAtId ? `https://line.me/R/ti/p/${normalizedLineAtId}` : '';
                };

                const setupLineTimelineAutofill = (form) => {
                    const lineAtIdField = form.elements.line_official_line_at_id;
                    const timelineUrlField = form.elements.line_official_line_timeline_url;
                    if (!lineAtIdField || !timelineUrlField) {
                        return;
                    }

                    const currentGeneratedUrl = lineTimelineUrl(lineAtIdField.value);
                    timelineUrlField.dataset.autogenerated = timelineUrlField.value.trim() === currentGeneratedUrl ? 'true' : 'false';

                    lineAtIdField.addEventListener('input', () => {
                        const generatedUrl = lineTimelineUrl(lineAtIdField.value);
                        const currentTimelineUrl = timelineUrlField.value.trim();
                        if (!generatedUrl) {
                            if (timelineUrlField.dataset.autogenerated === 'true') {
                                timelineUrlField.value = '';
                            }
                            return;
                        }

                        if (!currentTimelineUrl || timelineUrlField.dataset.autogenerated === 'true') {
                            timelineUrlField.value = generatedUrl;
                            timelineUrlField.dataset.autogenerated = 'true';
                        }
                    });

                    timelineUrlField.addEventListener('input', () => {
                        timelineUrlField.dataset.autogenerated = timelineUrlField.value.trim() === lineTimelineUrl(lineAtIdField.value) ? 'true' : 'false';
                    });
                };

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
                    renderBulkStoreOptions();
                    renderScheduleDayFields();
                };
                const fillScheduleForm = () => {
                    const form = $('[data-form="schedule"]');
                    if (!form || !editingSchedule || form.dataset.initialized === 'true') return;

                    form.elements.store_id.value = editingSchedule.store_id || '';
                    form.elements.submission_deadline_at.value = toDateTimeLocal(editingSchedule.submission_deadline_at);
                    form.elements.auto_schedule_enabled.checked = Boolean(editingSchedule.auto_schedule_enabled);
                    setScheduleMonth(form, String(editingSchedule.starts_on || '').slice(0, 7));
                    form.dataset.initialized = 'true';
                };

                const parseDate = (value) => {
                    if (!value) return null;
                    const date = new Date(`${value}T00:00:00Z`);
                    return Number.isNaN(date.getTime()) ? null : date;
                };
                const formatDate = (date) => date.toISOString().slice(0, 10);
                const toDateTimeLocal = (value) => {
                    if (!value) return '';
                    const date = new Date(value);
                    const localDate = new Date(date.getTime() - (date.getTimezoneOffset() * 60000));
                    return localDate.toISOString().slice(0, 16);
                };
                const setScheduleMonth = (form, month) => {
                    const monthInput = form?.querySelector('[data-schedule-month]');
                    const monthStart = parseDate(`${month}-01`);
                    if (!monthInput || !monthStart || !/^\d{4}-\d{2}$/.test(month)) return;

                    const monthEnd = new Date(Date.UTC(monthStart.getUTCFullYear(), monthStart.getUTCMonth() + 1, 0));
                    monthInput.value = month;
                    form.elements.starts_on.value = formatDate(monthStart);
                    form.elements.ends_on.value = formatDate(monthEnd);
                    renderScheduleDayFields();
                };
                const holidayCache = new Map();
                const nthMonday = (year, monthIndex, nth) => {
                    const first = new Date(Date.UTC(year, monthIndex, 1));
                    const day = 1 + ((8 - first.getUTCDay()) % 7) + ((nth - 1) * 7);
                    return new Date(Date.UTC(year, monthIndex, day));
                };
                const japaneseHolidaySet = (year) => {
                    if (holidayCache.has(year)) return holidayCache.get(year);

                    const holidays = new Set();
                    const originalHolidays = [];
                    const addHoliday = (date) => {
                        holidays.add(formatDate(date));
                        originalHolidays.push(date);
                    };
                    addHoliday(new Date(Date.UTC(year, 0, 1)));
                    addHoliday(nthMonday(year, 0, 2));
                    addHoliday(new Date(Date.UTC(year, 1, 11)));
                    addHoliday(new Date(Date.UTC(year, 1, 23)));
                    addHoliday(new Date(Date.UTC(year, 2, Math.floor(20.8431 + (0.242194 * (year - 1980)) - Math.floor((year - 1980) / 4)))));
                    addHoliday(new Date(Date.UTC(year, 3, 29)));
                    addHoliday(new Date(Date.UTC(year, 4, 3)));
                    addHoliday(new Date(Date.UTC(year, 4, 4)));
                    addHoliday(new Date(Date.UTC(year, 4, 5)));
                    addHoliday(nthMonday(year, 6, 3));
                    addHoliday(new Date(Date.UTC(year, 7, 11)));
                    addHoliday(nthMonday(year, 8, 3));
                    addHoliday(new Date(Date.UTC(year, 8, Math.floor(23.2488 + (0.242194 * (year - 1980)) - Math.floor((year - 1980) / 4)))));
                    addHoliday(nthMonday(year, 9, 2));
                    addHoliday(new Date(Date.UTC(year, 10, 3)));
                    addHoliday(new Date(Date.UTC(year, 10, 23)));

                    originalHolidays.forEach((holiday) => {
                        if (holiday.getUTCDay() !== 0) return;
                        const substitute = new Date(holiday);
                        do {
                            substitute.setUTCDate(substitute.getUTCDate() + 1);
                        } while (holidays.has(formatDate(substitute)));
                        holidays.add(formatDate(substitute));
                    });

                    for (let month = 0; month < 12; month += 1) {
                        const lastDay = new Date(Date.UTC(year, month + 1, 0)).getUTCDate();
                        for (let day = 2; day < lastDay; day += 1) {
                            const date = new Date(Date.UTC(year, month, day));
                            const previous = new Date(Date.UTC(year, month, day - 1));
                            const next = new Date(Date.UTC(year, month, day + 1));
                            if (date.getUTCDay() !== 0 && holidays.has(formatDate(previous)) && holidays.has(formatDate(next))) {
                                holidays.add(formatDate(date));
                            }
                        }
                    }

                    holidayCache.set(year, holidays);
                    return holidays;
                };
                const isJapaneseHoliday = (date) => japaneseHolidaySet(date.getUTCFullYear()).has(formatDate(date));
                const calendarDateClass = (date) => {
                    if (date.getUTCDay() === 0 || isJapaneseHoliday(date)) return 'text-red-600';
                    if (date.getUTCDay() === 6) return 'text-blue-600';
                    return 'text-slate-800';
                };
                const scheduleDayOptions = (selectedStoreId) => state.stores.map((store) => {
                    const selected = String(store.id) === String(selectedStoreId) ? ' selected' : '';
                    return `<option value="${escapeHtml(store.id)}"${selected}>${escapeHtml(store.name)}</option>`;
                }).join('');
                const timeOptions = (selectedTime) => {
                    const normalized = selectedTime ? selectedTime.slice(0, 5) : '';
                    const options = ['<option value="">--:--</option>'];
                    for (let hour = 0; hour < 24; hour += 1) {
                        for (const minute of ['00', '30']) {
                            const value = `${String(hour).padStart(2, '0')}:${minute}`;
                            const selected = value === normalized ? ' selected' : '';
                            options.push(`<option value="${value}"${selected}>${value}</option>`);
                        }
                    }
                    return options.join('');
                };
                const renderBulkTimeOptions = () => {
                    $$('[data-bulk-start], [data-bulk-end]').forEach((select) => {
                        const current = select.value || '';
                        select.innerHTML = timeOptions(current);
                        select.value = current;
                    });
                };
                const renderBulkStoreOptions = () => {
                    const select = $('[data-bulk-store]');
                    if (!select) return;

                    const defaultStoreId = $('[data-form="schedule"] select[name="store_id"]')?.value || state.stores[0]?.id || '';
                    const current = select.value || defaultStoreId;
                    select.innerHTML = scheduleDayOptions(current);
                    select.value = current;
                };
                const applyBulkStore = () => {
                    const storeId = $('[data-bulk-store]')?.value || '';
                    if (!storeId) return;

                    $$('[data-schedule-day-store]').forEach((select) => {
                        select.value = storeId;
                    });
                };
                const applyBulkTime = () => {
                    const startsAt = $('[data-bulk-start]')?.value || '';
                    const endsAt = $('[data-bulk-end]')?.value || '';

                    $$('[data-schedule-day-row]').forEach((row) => {
                        if (row.querySelector('[data-schedule-day-off]')?.checked) return;

                        const startSelect = row.querySelector('[data-schedule-day-start]');
                        const endSelect = row.querySelector('[data-schedule-day-end]');
                        if (startSelect) startSelect.value = startsAt;
                        if (endSelect) endSelect.value = endsAt;
                    });
                };
                const setDayOffRow = (row, isDayOff) => {
                    row.dataset.dayOff = String(isDayOff);
                    const checkbox = row.querySelector('[data-schedule-day-off]');
                    if (checkbox) checkbox.checked = isDayOff;
                    row.querySelectorAll('[data-schedule-day-store], [data-schedule-day-start], [data-schedule-day-end], [data-schedule-day-headcount]').forEach((field) => {
                        field.disabled = isDayOff;
                    });
                };
                const selectedBulkWeekdays = () => $$('[data-bulk-weekday]:checked').map((checkbox) => Number(checkbox.value));
                const applyBulkDayOff = (isDayOff) => {
                    const selectedWeekdays = selectedBulkWeekdays();
                    if (!selectedWeekdays.length) return;

                    $$('[data-schedule-day-row]').forEach((row) => {
                        const date = parseDate(row.dataset.scheduleDayRow);
                        if (!date || !selectedWeekdays.includes(date.getUTCDay())) return;

                        setDayOffRow(row, isDayOff);
                    });
                };
                const renderScheduleDayFields = () => {
                    const form = $('[data-form="schedule"]');
                    const list = $('[data-list="schedule-days"]');
                    if (!form || !list) return;

                    const startsOn = parseDate(form.elements.starts_on?.value || '');
                    const endsOn = parseDate(form.elements.ends_on?.value || '');
                    if (!startsOn || !endsOn) {
                        list.innerHTML = '<p class="py-3 text-sm text-slate-500">開始日と終了日を選択してください。</p>';
                        return;
                    }
                    if (startsOn > endsOn) {
                        list.innerHTML = '<p class="py-3 text-sm text-red-600">終了日は開始日以降にしてください。</p>';
                        return;
                    }
                    if (startsOn.getUTCFullYear() !== endsOn.getUTCFullYear() || startsOn.getUTCMonth() !== endsOn.getUTCMonth()) {
                        list.innerHTML = '<p class="py-3 text-sm text-red-600">同じ月内で指定してください。</p>';
                        return;
                    }

                    const defaultStoreId = form.elements.store_id?.value || state.stores[0]?.id || '';
                    const existingRows = $$('[data-schedule-day-row]');
                    const existingValues = existingRows.length ? Object.fromEntries(existingRows.map((row) => [row.dataset.scheduleDayRow, {
                        storeId: row.querySelector('[data-schedule-day-store]')?.value || '',
                        startsAt: row.querySelector('[data-schedule-day-start]')?.value || '',
                        endsAt: row.querySelector('[data-schedule-day-end]')?.value || '',
                        requiredHeadcount: row.querySelector('[data-schedule-day-headcount]')?.value || '1',
                        isDayOff: row.querySelector('[data-schedule-day-off]')?.checked || false,
                    }])) : Object.fromEntries((editingSchedule?.days || []).map((day) => [day.scheduled_on, {
                        storeId: day.store_id || '',
                        startsAt: day.starts_at ? day.starts_at.slice(0, 5) : '',
                        endsAt: day.ends_at ? day.ends_at.slice(0, 5) : '',
                        requiredHeadcount: day.required_headcount || 1,
                        isDayOff: Boolean(day.is_day_off),
                    }]));
                    const monthStart = new Date(Date.UTC(startsOn.getUTCFullYear(), startsOn.getUTCMonth(), 1));
                    const monthEnd = new Date(Date.UTC(startsOn.getUTCFullYear(), startsOn.getUTCMonth() + 1, 0));
                    const cells = weekdays.map((day, index) => `<div class="shift-editor-weekday py-1 text-center text-xs font-black ${index === 0 ? 'text-red-600' : index === 6 ? 'text-blue-600' : 'text-slate-500'}">${day}</div>`);
                    for (let index = 0; index < monthStart.getUTCDay(); index += 1) {
                        cells.push('<div class="shift-editor-empty" aria-hidden="true"></div>');
                    }
                    for (const date = new Date(monthStart); date <= monthEnd; date.setUTCDate(date.getUTCDate() + 1)) {
                        const scheduledOn = formatDate(date);
                        const isInRange = date >= startsOn && date <= endsOn;
                        if (!isInRange) {
                            cells.push(`
                                <div class="shift-editor-outside min-h-36 rounded-md border border-slate-100 bg-slate-100/60 p-2 text-xs font-bold ${calendarDateClass(date)} opacity-50">
                                    ${date.getUTCDate()}
                                </div>
                            `);
                            continue;
                        }
                        const values = existingValues[scheduledOn] || {};
                        const selectedStoreId = values.storeId || defaultStoreId;
                        const isDayOff = Boolean(values.isDayOff);
                        cells.push(`
                            <div class="shift-editor-day min-h-36 space-y-2 rounded-md border border-slate-200 bg-white p-2" id="schedule-day-${scheduledOn}" data-schedule-day-row="${scheduledOn}" data-day-off="${isDayOff}" data-weekday="${date.getUTCDay()}">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-black ${calendarDateClass(date)}">${date.getUTCDate()}</span>
                                    <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600">
                                        <input type="checkbox" class="rounded border-slate-300 text-teal-700 accent-teal-700" data-schedule-day-off ${isDayOff ? 'checked' : ''}>
                                        休み
                                    </label>
                                </div>
                                <select class="min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 disabled:bg-slate-100 disabled:text-slate-400" aria-label="${scheduledOn} 店舗" data-schedule-day-store ${isDayOff ? 'disabled' : ''}>
                                    ${scheduleDayOptions(selectedStoreId)}
                                </select>
                                <div class="grid grid-cols-2 gap-2">
                                    <select class="min-h-10 w-full rounded-xl border border-slate-200 bg-white px-2 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 disabled:bg-slate-100 disabled:text-slate-400" aria-label="${scheduledOn} 開始時刻" data-schedule-day-start ${isDayOff ? 'disabled' : ''}>
                                        ${timeOptions(values.startsAt || '')}
                                    </select>
                                    <select class="min-h-10 w-full rounded-xl border border-slate-200 bg-white px-2 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 disabled:bg-slate-100 disabled:text-slate-400" aria-label="${scheduledOn} 終了時刻" data-schedule-day-end ${isDayOff ? 'disabled' : ''}>
                                        ${timeOptions(values.endsAt || '')}
                                    </select>
                                </div>
                                <label class="block text-xs font-bold text-slate-600">
                                    必要人数
                                    <input type="number" min="1" max="50" value="${escapeHtml(values.requiredHeadcount || 1)}" class="mt-1 min-h-9 w-full rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-900 outline-none transition focus:border-teal-500 disabled:bg-slate-100" data-schedule-day-headcount ${isDayOff ? 'disabled' : ''}>
                                </label>
                            </div>
                        `);
                    }
                    list.innerHTML = `<div class="shift-editor-grid">${cells.join('')}</div>`;
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

                const scheduleMatchesStore = (schedule, storeId) => String(schedule.store_id || '') === storeId
                    || (schedule.days || []).some((day) => !day.is_day_off && String(day.store_id || '') === storeId);
                const scheduleStoreSummary = (schedule) => {
                    const days = schedule.days || [];
                    if (!days.length) {
                        return escapeHtml(schedule.store?.name || '-');
                    }

                    return days.slice(0, 8).map((day) => {
                        const label = String(day.scheduled_on || '').slice(5, 10).replace('-', '/');
                        if (day.is_day_off) {
                            return `${escapeHtml(label)} 休み`;
                        }
                        const time = day.starts_at && day.ends_at ? ` ${day.starts_at.slice(0, 5)}-${day.ends_at.slice(0, 5)}` : '';
                        return `${escapeHtml(label)} ${escapeHtml(day.store?.name || '-')}${escapeHtml(time)}`;
                    }).join(' / ') + (days.length > 8 ? ' ...' : '');
                };
                const scheduleMemberSummary = (schedule) => {
                    const slots = schedule.shift_slots || [];
                    const activeAssignments = slots.flatMap((slot) => (slot.assignments || []).filter((assignment) => assignment.member && assignment.status !== 'cancelled'));
                    const members = Array.from(new Map(activeAssignments.map((assignment) => [String(assignment.member.id), assignment.member])).values());
                    const unassignedSlotCount = slots.reduce((total, slot) => {
                        const assignedCount = (slot.assignments || []).filter((assignment) => assignment.member && assignment.status !== 'cancelled').length;
                        return total + Math.max(0, Number(slot.required_headcount || 1) - assignedCount);
                    }, 0);

                    if (!members.length) {
                        return '<span class="font-bold text-amber-700">未割り当て</span>';
                    }

                    const memberBadges = members.map((member) => `<span class="inline-flex rounded-full border border-teal-200 bg-teal-50 px-2 py-0.5 text-xs font-bold text-teal-700">${escapeHtml(memberDisplayName(member))}</span>`);
                    if (unassignedSlotCount > 0) {
                        memberBadges.push(`<span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2 py-0.5 text-xs font-bold text-red-700">人員不足 ${unassignedSlotCount}名</span>`);
                    }

                    return `<div class="flex min-w-40 flex-wrap gap-1.5">${memberBadges.join('')}</div>`;
                };

                const scheduleOperations = (schedule) => schedule.operations || {
                    stage: schedule.status === 'published' ? 'published' : 'setup',
                    eligible_members: 0,
                    completed_members: 0,
                    partial_members: 0,
                    unsubmitted_members: 0,
                    submission_percent: 0,
                    required_headcount: 0,
                    assigned_headcount: 0,
                    shortage_headcount: 0,
                    coverage_percent: 0,
                };
                const stageMeta = (stage) => ({
                    setup: { label: '基本設定', className: 'border-slate-200 bg-slate-50 text-slate-700', number: 1 },
                    collecting: { label: '希望回収中', className: 'border-sky-200 bg-sky-50 text-sky-700', number: 2 },
                    overdue: { label: '期限超過', className: 'border-red-200 bg-red-50 text-red-700', number: 2 },
                    reviewing: { label: '編成・調整中', className: 'border-amber-200 bg-amber-50 text-amber-700', number: 3 },
                    published: { label: '公開済み', className: 'border-emerald-200 bg-emerald-50 text-emerald-700', number: 4 },
                }[stage] || { label: '基本設定', className: 'border-slate-200 bg-slate-50 text-slate-700', number: 1 });
                const progressMeter = (value, color = 'bg-teal-500') => `<div class="mt-2 h-1.5 w-28 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full ${color}" style="width:${Math.max(0, Math.min(100, Number(value || 0)))}%"></div></div>`;
                const scheduleMonthLabel = (schedule) => {
                    const [year, month] = String(schedule.starts_on || '').split('-');
                    return year && month ? `${year}年${Number(month)}月` : '-';
                };

                const renderSchedules = () => {
                    const list = $('[data-list="schedules"]');
                    const filter = $('[data-filter="scheduleStore"]');
                    if (!list || !filter) {
                        return;
                    }

                    const storeId = filter.value;
                    const schedules = storeId ? state.schedules.filter((schedule) => scheduleMatchesStore(schedule, storeId)) : state.schedules;
                    $('[data-schedule-stat="collecting"]') && ($('[data-schedule-stat="collecting"]').textContent = schedules.filter((schedule) => scheduleOperations(schedule).stage === 'collecting').length);
                    $('[data-schedule-stat="shortage"]') && ($('[data-schedule-stat="shortage"]').textContent = schedules
                        .filter((schedule) => ['reviewing', 'published'].includes(scheduleOperations(schedule).stage))
                        .reduce((total, schedule) => total + scheduleOperations(schedule).shortage_headcount, 0));
                    $('[data-schedule-stat="published"]') && ($('[data-schedule-stat="published"]').textContent = schedules.filter((schedule) => scheduleOperations(schedule).stage === 'published').length);
                    list.innerHTML = schedules.length
                        ? schedules.map((schedule) => {
                            const operations = scheduleOperations(schedule);
                            const stage = stageMeta(operations.stage);
                            const hasAssignments = operations.assigned_headcount > 0 || (schedule.shift_slots || []).length > 0;
                            return `
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-black text-slate-950">${escapeHtml(scheduleMonthLabel(schedule))}</div>
                                    <div class="mt-1 text-xs font-bold text-teal-700">${escapeHtml(schedule.store?.name || '-')}</div>
                                    <div class="mt-1 max-w-md text-xs leading-5 text-slate-500">${scheduleStoreSummary(schedule)}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    <span class="font-black text-slate-900">${operations.completed_members}/${operations.eligible_members}名</span>
                                    <span class="ml-1 text-xs text-slate-500">${operations.submission_percent}%</span>
                                    ${progressMeter(operations.submission_percent, 'bg-sky-500')}
                                    ${schedule.submission_deadline_at ? `<span class="mt-1.5 block text-xs text-slate-500">期限 ${escapeHtml(toDateTimeLocal(schedule.submission_deadline_at).replace('T', ' '))}</span>` : '<span class="mt-1.5 block text-xs text-amber-700">期限未設定</span>'}
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    ${hasAssignments ? `<span class="font-black ${operations.shortage_headcount > 0 ? 'text-red-700' : 'text-emerald-700'}">${operations.assigned_headcount}/${operations.required_headcount}枠</span>${progressMeter(operations.coverage_percent, operations.shortage_headcount > 0 ? 'bg-red-500' : 'bg-emerald-500')}${operations.shortage_headcount > 0 ? `<span class="mt-1.5 block text-xs font-bold text-red-700">あと${operations.shortage_headcount}枠不足</span>` : '<span class="mt-1.5 block text-xs font-bold text-emerald-700">充足</span>'}` : '<span class="text-xs font-bold text-slate-500">自動編成前</span>'}
                                </td>
                                <td class="px-4 py-3">${scheduleMemberSummary(schedule)}</td>
                                <td class="px-4 py-3"><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black ${stage.className}">${stage.label}</span>${schedule.notification_sent_at ? '<span class="mt-1.5 block text-xs font-bold text-emerald-700">LINE通知済み</span>' : ''}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="${routes.schedulesBase}/${schedule.id}/edit" class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs font-black text-slate-700 transition hover:bg-slate-50">編集</a>
                                        ${schedule.status === 'published'
                                            ? '<span class="px-3 py-1.5 text-xs font-black text-slate-500">公開済み</span>'
                                            : `<button type="button" class="rounded-xl border border-teal-200 px-3 py-1.5 text-xs font-black text-teal-700 transition hover:bg-teal-700 hover:text-white" data-publish="${schedule.id}">公開</button>`}
                                    </div>
                                </td>
                            </tr>
                        `}).join('')
                        : '<tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">シフト表がまだありません。</td></tr>';
                };

                const renderDashboard = () => {
                    const dashboardFilter = $('[data-filter="dashboardStore"]');
                    const dashboardTable = $('[data-list="dashboardSchedules"]');
                    const workflow = $('[data-dashboard-workflow]');
                    const actions = $('[data-dashboard-actions]');

                    if (!dashboardFilter || !dashboardTable || !workflow || !actions) {
                        return;
                    }

                    const storeId = dashboardFilter.value;
                    const schedules = storeId
                        ? state.schedules.filter((schedule) => scheduleMatchesStore(schedule, storeId))
                        : state.schedules;
                    const calendarSource = $('[data-calendar-source]');
                    if (calendarSource) {
                        calendarSource.textContent = JSON.stringify({
                            schedules: schedules.map((schedule) => ({
                                id: schedule.id,
                                starts_on: schedule.starts_on,
                                ends_on: schedule.ends_on,
                                submission_deadline_at: schedule.submission_deadline_at,
                                status: schedule.status,
                                store: schedule.store?.name || '店舗未設定',
                                days: (schedule.days || []).map((day) => ({
                                    scheduled_on: day.scheduled_on, is_day_off: day.is_day_off,
                                    starts_at: day.starts_at, ends_at: day.ends_at,
                                    required_headcount: day.required_headcount,
                                })),
                            })),
                            editBase: routes.schedulesBase,
                        });
                        document.dispatchEvent(new Event('shift-calendar:update'));
                    }
                    const unsubmitted = schedules.reduce((total, schedule) => total + scheduleOperations(schedule).unsubmitted_members, 0);
                    const shortage = schedules.filter((schedule) => ['reviewing', 'published'].includes(scheduleOperations(schedule).stage)).reduce((total, schedule) => total + scheduleOperations(schedule).shortage_headcount, 0);
                    $('[data-stat="unsubmitted"]') && ($('[data-stat="unsubmitted"]').textContent = unsubmitted);
                    $('[data-stat="shortage"]') && ($('[data-stat="shortage"]').textContent = shortage);
                    $('[data-stat="scheduled"]') && ($('[data-stat="scheduled"]').textContent = schedules.filter((schedule) => schedule.auto_scheduled_at || (schedule.shift_slots || []).length > 0).length);
                    $('[data-stat="notified"]') && ($('[data-stat="notified"]').textContent = schedules.filter((schedule) => schedule.notification_sent_at).length);

                    const flowSteps = [
                        { number: 1, label: '基本設定', description: '対象月・店舗・必要人数', count: schedules.filter((schedule) => scheduleOperations(schedule).stage === 'setup').length },
                        { number: 2, label: '希望回収', description: 'LINEで希望を収集中', count: schedules.filter((schedule) => ['collecting', 'overdue'].includes(scheduleOperations(schedule).stage)).length },
                        { number: 3, label: '編成・調整', description: '自動案を管理者が確認', count: schedules.filter((schedule) => scheduleOperations(schedule).stage === 'reviewing').length },
                        { number: 4, label: '公開・通知', description: '確定シフトをLINE配信', count: schedules.filter((schedule) => scheduleOperations(schedule).stage === 'published').length },
                    ];
                    workflow.innerHTML = flowSteps.map((step) => `<div class="relative rounded-lg border ${step.count ? 'border-teal-200 bg-teal-50' : 'border-slate-200 bg-slate-50'} p-3"><div class="flex items-center justify-between"><span class="grid size-7 place-items-center rounded-full ${step.count ? 'bg-teal-700 text-white' : 'bg-slate-200 text-slate-500'} text-xs font-black">${step.number}</span><span class="text-xl font-black text-slate-950">${step.count}</span></div><p class="mt-3 text-sm font-black text-slate-900">${step.label}</p><p class="mt-1 text-xs text-slate-500">${step.description}</p></div>`).join('');

                    const actionItems = schedules.flatMap((schedule) => {
                        const operations = scheduleOperations(schedule);
                        const items = [];
                        if (['collecting', 'overdue'].includes(operations.stage) && operations.unsubmitted_members > 0) items.push({ tone: 'amber', title: `${scheduleMonthLabel(schedule)} 希望未提出`, detail: `${schedule.store?.name || '-'}・${operations.unsubmitted_members}名`, id: schedule.id });
                        if (['reviewing', 'published'].includes(operations.stage) && operations.shortage_headcount > 0) items.push({ tone: 'red', title: `${scheduleMonthLabel(schedule)} 人員不足`, detail: `${schedule.store?.name || '-'}・あと${operations.shortage_headcount}枠`, id: schedule.id });
                        if (operations.stage === 'overdue') items.push({ tone: 'red', title: '提出期限を超過', detail: `${schedule.store?.name || '-'}・自動編成を確認`, id: schedule.id });
                        return items;
                    }).slice(0, 8);
                    $('[data-action-count]') && ($('[data-action-count]').textContent = `${actionItems.length}件`);
                    actions.innerHTML = actionItems.length ? actionItems.map((item) => `<a href="${routes.schedulesBase}/${item.id}/edit" class="block rounded-lg border ${item.tone === 'red' ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50'} p-3 transition hover:shadow-sm"><p class="text-sm font-black ${item.tone === 'red' ? 'text-red-800' : 'text-amber-800'}">${escapeHtml(item.title)}</p><p class="mt-1 text-xs text-slate-600">${escapeHtml(item.detail)}</p></a>`).join('') : '<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">現在、対応が必要な項目はありません。</div>';

                    dashboardTable.innerHTML = schedules.length
                        ? schedules.slice(0, 10).map((schedule) => {
                            const operations = scheduleOperations(schedule);
                            const stage = stageMeta(operations.stage);
                            const hasAssignments = operations.assigned_headcount > 0 || (schedule.shift_slots || []).length > 0;
                            return `
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4"><span class="block font-black text-slate-950">${escapeHtml(scheduleMonthLabel(schedule))}</span><span class="mt-1 block text-xs font-bold text-teal-700">${escapeHtml(schedule.store?.name || '-')}</span></td>
                                <td class="px-5 py-4"><span class="font-black text-slate-900">${operations.completed_members}/${operations.eligible_members}名</span><span class="ml-1 text-xs text-slate-500">${operations.submission_percent}%</span>${progressMeter(operations.submission_percent, 'bg-sky-500')}</td>
                                <td class="px-5 py-4">${hasAssignments ? `<span class="font-black ${operations.shortage_headcount ? 'text-red-700' : 'text-emerald-700'}">${operations.assigned_headcount}/${operations.required_headcount}枠</span>${progressMeter(operations.coverage_percent, operations.shortage_headcount ? 'bg-red-500' : 'bg-emerald-500')}` : '<span class="text-xs font-bold text-slate-500">編成前</span>'}</td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black ${stage.className}">${stage.label}</span></td>
                                <td class="px-5 py-4 text-right">
                                    <a href="${routes.schedulesBase}/${schedule.id}/edit" class="inline-flex rounded-md border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50">確認・調整</a>
                                </td>
                            </tr>
                        `}).join('')
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
                    renderBulkTimeOptions();
                    renderStores();
                    renderMembers();
                    renderSchedules();
                    renderStats();
                    renderDashboard();
                    fillScheduleForm();
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
                const schedulePayload = (form) => ({
                    ...formPayload(form),
                    days: $$('[data-schedule-day-row]').map((row) => {
                        const isDayOff = row.querySelector('[data-schedule-day-off]')?.checked || false;

                        return {
                            scheduled_on: row.dataset.scheduleDayRow,
                            is_day_off: isDayOff,
                            store_id: row.querySelector('[data-schedule-day-store]')?.value,
                            starts_at: isDayOff ? null : row.querySelector('[data-schedule-day-start]')?.value,
                            ends_at: isDayOff ? null : row.querySelector('[data-schedule-day-end]')?.value,
                            required_headcount: isDayOff ? 1 : Number(row.querySelector('[data-schedule-day-headcount]')?.value || 1),
                        };
                    }),
                });

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
                    setupLineTimelineAutofill(tenantSettingsForm);
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

                $$('form').forEach((form) => {
                    if (form.dataset.form === 'tenant-settings') {
                        return;
                    }

                    setupLineTimelineAutofill(form);
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

                $('[data-form="schedule"]')?.addEventListener('input', (event) => {
                    if (event.target.matches('[data-schedule-month]')) {
                        setScheduleMonth(event.currentTarget, event.target.value);
                    }
                });
                $('[data-form="schedule"] select[name="store_id"]')?.addEventListener('change', renderScheduleDayFields);
                $('[data-list="schedule-days"]')?.addEventListener('change', (event) => {
                    if (!event.target.matches('[data-schedule-day-off]')) return;
                    const row = event.target.closest('[data-schedule-day-row]');
                    if (row) setDayOffRow(row, event.target.checked);
                });

                $('[data-form="schedule"]')?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    const scheduleId = form.dataset.scheduleId;
                    try {
                        await api(scheduleId ? `/api/admin/shift-schedules/${scheduleId}` : '/api/admin/shift-schedules', {
                            method: scheduleId ? 'PUT' : 'POST',
                            body: JSON.stringify({ status: editingSchedule?.status || 'draft', ...schedulePayload(form) }),
                        });
                        if (!scheduleId) {
                            form.reset();
                        }
                        await load();
                        setMessage('[data-notice]', scheduleId ? 'シフト表を更新しました。' : 'シフト表を作成しました。');
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
                    const saveAssignmentsButton = event.target.closest('[data-action="save-shift-slot-members"]');
                    if (saveAssignmentsButton) {
                        const assignmentPanel = saveAssignmentsButton.closest('[data-shift-slot-assignment]');
                        const memberIds = Array.from(assignmentPanel.querySelectorAll('[data-shift-slot-member]:checked')).map((checkbox) => Number(checkbox.value));
                        saveAssignmentsButton.disabled = true;
                        saveAssignmentsButton.textContent = '保存中...';
                        try {
                            await api(`/api/admin/shift-slots/${assignmentPanel.dataset.shiftSlotAssignment}/assignments`, {
                                method: 'PUT',
                                body: JSON.stringify({ member_ids: memberIds }),
                            });
                            window.location.reload();
                        } catch (error) {
                            saveAssignmentsButton.disabled = false;
                            saveAssignmentsButton.textContent = '担当を保存';
                            setMessage('[data-alert]', error.message);
                        }
                        return;
                    }

                    if (event.target.closest('[data-action="apply-bulk-store"]')) {
                        applyBulkStore();
                        return;
                    }

                    if (event.target.closest('[data-action="apply-bulk-time"]')) {
                        applyBulkTime();
                        return;
                    }

                    if (event.target.closest('[data-action="apply-bulk-day-off"]')) {
                        applyBulkDayOff(true);
                        return;
                    }

                    if (event.target.closest('[data-action="clear-bulk-day-off"]')) {
                        applyBulkDayOff(false);
                        return;
                    }

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
