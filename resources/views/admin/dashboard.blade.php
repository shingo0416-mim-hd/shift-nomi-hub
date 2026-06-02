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
                        <button type="button" class="grid size-10 place-items-center rounded-lg bg-teal-600 text-sm font-bold text-white lg:hidden" data-action="toggle-sidebar" aria-label="管理メニュー">
                            SH
                        </button>
                        <div class="hidden size-10 place-items-center rounded-lg bg-teal-600 text-sm font-bold text-white lg:grid">SH</div>
                        <div>
                            <p class="text-sm font-semibold text-slate-950">ShiftHub</p>
                            <p class="text-xs text-slate-500" data-bind="tenantName">管理画面</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>
                        </div>
                        <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50" data-action="reload">
                            更新
                        </button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                                ログアウト
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <div class="lg:flex">
                <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 border-r border-slate-200 bg-white pt-16 shadow-xl lg:sticky lg:top-16 lg:z-20 lg:block lg:h-[calc(100vh-4rem)] lg:pt-0 lg:shadow-none" data-sidebar>
                    <div class="flex h-full flex-col overflow-y-auto px-4 py-5">
                        <nav class="space-y-7" aria-label="管理メニュー">
                            <div>
                                <p class="px-3 pb-2 text-xs font-bold text-slate-400">業務</p>
                                <div class="space-y-1">
                                    <a href="#overview" class="flex items-center gap-3 rounded-lg bg-teal-50 px-3 py-3 text-sm font-semibold text-teal-700">
                                        <span class="grid size-8 place-items-center rounded-md bg-teal-100">概</span>
                                        ダッシュボード
                                    </a>
                                    <a href="#stores" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <span class="grid size-8 place-items-center rounded-md bg-slate-100">店</span>
                                        店舗管理
                                    </a>
                                    <a href="#members" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <span class="grid size-8 place-items-center rounded-md bg-slate-100">人</span>
                                        キャスト管理
                                    </a>
                                    <a href="#schedules" class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        <span class="grid size-8 place-items-center rounded-md bg-slate-100">表</span>
                                        シフト管理
                                    </a>
                                </div>
                            </div>

                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-bold text-slate-500">運用メモ</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">店舗とキャストを登録後、対象期間のシフト表を作成して公開できます。</p>
                            </div>
                        </nav>
                    </div>
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
                                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                                店舗
                                                <select class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-500 focus:ring-4 focus:ring-teal-100" data-filter="memberStore">
                                                    <option value="">すべて</option>
                                                </select>
                                            </label>
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
                                    <h2 class="text-lg font-semibold text-slate-950">キャスト登録</h2>
                                    <form class="mt-4 space-y-4" data-form="member">
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
                                        <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">キャストを追加</button>
                                    </form>
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

                    if (event.target.closest('[data-action="toggle-sidebar"]')) {
                        $('[data-sidebar]').classList.toggle('hidden');
                    }
                });

                $$('[data-filter="memberStore"], [data-filter="scheduleStore"]').forEach((select) => {
                    select.addEventListener('change', render);
                });

                load().catch((error) => setMessage('[data-alert]', error.message));
            })();
        </script>
    </body>
</html>
