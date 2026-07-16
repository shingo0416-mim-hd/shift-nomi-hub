<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>LINE管理 - {{ config('app.name', 'ShiftHub') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
        <main class="mx-auto min-h-screen w-full max-w-md px-4 py-4">
            <header class="sticky top-0 z-10 -mx-4 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-black text-teal-700">{{ $tenant?->name }}</p>
                        <h1 class="truncate text-xl font-black text-slate-950">シフト管理</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($member?->icon_url)
                            <img src="{{ $member->icon_url }}" alt="" class="h-10 w-10 rounded-full object-cover ring-1 ring-slate-200">
                        @else
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-sm font-black text-slate-600">{{ mb_substr($member?->line_name ?: $member?->displayName() ?: '管', 0, 1) }}</span>
                        @endif
                    </div>
                </div>
            </header>

            <div class="mt-4 space-y-4">
                <p class="hidden rounded-xl border px-4 py-3 text-sm font-bold" data-notice></p>

                <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black text-teal-700">Create</p>
                            <h2 class="mt-1 text-base font-black text-slate-950">シフト表作成</h2>
                        </div>
                    </div>
                    <form class="mt-4 space-y-3" data-form="schedule">
                        <label class="block text-sm font-bold text-slate-700">
                            店舗
                            <select name="store_id" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white"></select>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block text-sm font-bold text-slate-700">
                                開始日
                                <input name="starts_on" type="date" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                            </label>
                            <label class="block text-sm font-bold text-slate-700">
                                終了日
                                <input name="ends_on" type="date" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                            </label>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-black text-slate-800">日別店舗</p>
                                <span class="text-xs font-bold text-slate-500">同じ月内</span>
                            </div>
                            <div class="mt-3 max-h-[34rem] overflow-auto pr-1" data-list="schedule-days">
                                <p class="py-3 text-center text-sm text-slate-500">開始日と終了日を選択してください。</p>
                            </div>
                        </div>
                        <button type="button" class="hidden min-h-11 w-full items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-action="cancel-schedule-edit">
                            編集をキャンセル
                        </button>
                        <button class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800" data-schedule-submit-label>
                            下書き作成
                        </button>
                    </form>
                </section>

                @if ($canManageMembers ?? false)
                <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div>
                        <p class="text-xs font-black text-teal-700">Crew</p>
                        <h2 class="mt-1 text-base font-black text-slate-950">キャスト登録</h2>
                    </div>
                    <form class="mt-4 space-y-3" data-form="member">
                        <label class="block text-sm font-bold text-slate-700">
                            表示名
                            <input name="display_name" required class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                        </label>
                        <label class="block text-sm font-bold text-slate-700">
                            本名
                            <input name="name" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                        </label>
                        <label class="block text-sm font-bold text-slate-700">
                            店舗
                            <select name="store_id" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                <option value="">未割り当て</option>
                            </select>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block text-sm font-bold text-slate-700">
                                電話
                                <input name="phone" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                            </label>
                            <label class="block text-sm font-bold text-slate-700">
                                権限
                                <select name="role" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                                    <option value="cast">キャスト</option>
                                    <option value="manager">店長</option>
                                    <option value="admin">管理者</option>
                                </select>
                            </label>
                        </div>
                        <label class="block text-sm font-bold text-slate-700">
                            メール
                            <input name="email" type="email" class="mt-2 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:bg-white">
                        </label>
                        <button class="inline-flex min-h-11 w-full items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">
                            キャストを追加
                        </button>
                    </form>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 p-4">
                        <p class="text-xs font-black text-teal-700">Crew List</p>
                        <h2 class="mt-1 text-base font-black text-slate-950">キャスト一覧</h2>
                    </div>
                    <div class="divide-y divide-slate-200" data-list="members">
                        <p class="px-4 py-8 text-center text-sm text-slate-500">読み込み中です。</p>
                    </div>
                </section>
                @endif

                <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 p-4">
                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <p class="text-xs font-black text-teal-700">Schedules</p>
                                <h2 class="mt-1 text-base font-black text-slate-950">シフト表一覧</h2>
                            </div>
                            <label class="min-w-32 text-xs font-bold text-slate-600">
                                店舗
                                <select class="mt-1 min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500" data-filter="store">
                                    <option value="">すべて</option>
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-200" data-list="schedules">
                        <p class="px-4 py-8 text-center text-sm text-slate-500">読み込み中です。</p>
                    </div>
                </section>
            </div>
        </main>

        <script>
            (() => {
                const routes = {
                    stores: @json(route('line.admin.api.stores.index', ['tenant' => $tenantPath])),
                    members: @json(route('line.admin.api.members.index', ['tenant' => $tenantPath])),
                    createMember: @json(route('line.admin.api.members.store', ['tenant' => $tenantPath])),
                    schedules: @json(route('line.admin.api.shift-schedules.index', ['tenant' => $tenantPath])),
                    createSchedule: @json(route('line.admin.api.shift-schedules.store', ['tenant' => $tenantPath])),
                    publishScheduleBase: @json(url('/'.$tenantPath.'/line/admin/api/shift-schedules')),
                };
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const canManageMembers = @json($canManageMembers ?? false);
                const currentMemberStoreId = @json($member?->store_id);
                const state = { stores: [], members: [], schedules: [] };
                let editingSchedule = null;
                const $ = (selector) => document.querySelector(selector);
                const $$ = (selector) => Array.from(document.querySelectorAll(selector));
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
                const weekdays = ['日', '月', '火', '水', '木', '金', '土'];

                const api = async (url, options = {}) => {
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
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

                const formPayload = (form) => Object.fromEntries(new FormData(form).entries());
                const setNotice = (message, type = 'success') => {
                    const notice = $('[data-notice]');
                    if (!notice) return;
                    notice.textContent = message;
                    notice.classList.remove('hidden', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700', 'border-red-200', 'bg-red-50', 'text-red-700');
                    notice.classList.add(
                        ...(type === 'error'
                            ? ['border-red-200', 'bg-red-50', 'text-red-700']
                            : ['border-emerald-200', 'bg-emerald-50', 'text-emerald-700'])
                    );
                };

                const statusBadge = (status) => {
                    const label = status === 'published' ? '公開済み' : status === 'archived' ? 'アーカイブ' : '下書き';
                    const klass = status === 'published' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : status === 'archived' ? 'border-slate-200 bg-slate-100 text-slate-500' : 'border-teal-200 bg-teal-50 text-teal-700';
                    return `<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-black ${klass}">${label}</span>`;
                };
                const scheduleMatchesStore = (schedule, storeId) => String(schedule.store_id || '') === storeId
                    || (schedule.days || []).some((day) => !day.is_day_off && String(day.store_id || '') === storeId);
                const scheduleStoreSummary = (schedule) => {
                    const days = schedule.days || [];
                    if (!days.length) {
                        return escapeHtml(schedule.store?.name || '-');
                    }

                    return days.slice(0, 6).map((day) => {
                        const label = String(day.scheduled_on || '').slice(5, 10).replace('-', '/');
                        if (day.is_day_off) {
                            return `${escapeHtml(label)} 休み`;
                        }
                        const time = day.starts_at && day.ends_at ? ` ${day.starts_at.slice(0, 5)}-${day.ends_at.slice(0, 5)}` : '';
                        return `${escapeHtml(label)} ${escapeHtml(day.store?.name || '-')}${escapeHtml(time)}`;
                    }).join(' / ') + (days.length > 6 ? ' ...' : '');
                };

                const renderStores = () => {
                    const options = state.stores.map((store) => `<option value="${escapeHtml(store.id)}">${escapeHtml(store.name)}</option>`).join('');
                    $$('select[name="store_id"]').forEach((select) => select.innerHTML = options);
                    const scheduleStoreSelect = $('[data-form="schedule"] select[name="store_id"]');
                    if (scheduleStoreSelect && currentMemberStoreId && state.stores.some((store) => String(store.id) === String(currentMemberStoreId))) {
                        scheduleStoreSelect.value = String(currentMemberStoreId);
                    }
                    renderScheduleDayFields();
                    const filter = $('[data-filter="store"]');
                    if (filter) {
                        filter.innerHTML = `<option value="">すべて</option>${options}`;
                    }
                };

                const parseDate = (value) => {
                    if (!value) return null;
                    const date = new Date(`${value}T00:00:00Z`);
                    return Number.isNaN(date.getTime()) ? null : date;
                };
                const formatDate = (date) => date.toISOString().slice(0, 10);
                const scheduleDayOptions = (selectedStoreId) => state.stores.map((store) => {
                    const selected = String(store.id) === String(selectedStoreId) ? ' selected' : '';
                    return `<option value="${escapeHtml(store.id)}"${selected}>${escapeHtml(store.name)}</option>`;
                }).join('');
                const renderScheduleDayFields = () => {
                    const form = $('[data-form="schedule"]');
                    const list = $('[data-list="schedule-days"]');
                    if (!form || !list) return;

                    const startsOn = parseDate(form.elements.starts_on?.value || '');
                    const endsOn = parseDate(form.elements.ends_on?.value || '');
                    if (!startsOn || !endsOn) {
                        list.innerHTML = '<p class="py-3 text-center text-sm text-slate-500">開始日と終了日を選択してください。</p>';
                        return;
                    }
                    if (startsOn > endsOn) {
                        list.innerHTML = '<p class="py-3 text-center text-sm text-red-600">終了日は開始日以降にしてください。</p>';
                        return;
                    }
                    if (startsOn.getUTCFullYear() !== endsOn.getUTCFullYear() || startsOn.getUTCMonth() !== endsOn.getUTCMonth()) {
                        list.innerHTML = '<p class="py-3 text-center text-sm text-red-600">同じ月内で指定してください。</p>';
                        return;
                    }

                    const defaultStoreId = form.elements.store_id?.value || currentMemberStoreId || state.stores[0]?.id || '';
                    const existingRows = $$('[data-schedule-day-row]');
                    const existingValues = existingRows.length ? Object.fromEntries(existingRows.map((row) => [row.dataset.scheduleDayRow, {
                        storeId: row.querySelector('[data-schedule-day-store]')?.value || '',
                        startsAt: row.querySelector('[data-schedule-day-start]')?.value || '',
                        endsAt: row.querySelector('[data-schedule-day-end]')?.value || '',
                        isDayOff: row.querySelector('[data-schedule-day-off]')?.checked || false,
                    }])) : Object.fromEntries((editingSchedule?.days || []).map((day) => [day.scheduled_on, {
                        storeId: day.store_id || '',
                        startsAt: day.starts_at ? day.starts_at.slice(0, 5) : '',
                        endsAt: day.ends_at ? day.ends_at.slice(0, 5) : '',
                        isDayOff: Boolean(day.is_day_off),
                    }]));
                    const monthStart = new Date(Date.UTC(startsOn.getUTCFullYear(), startsOn.getUTCMonth(), 1));
                    const monthEnd = new Date(Date.UTC(startsOn.getUTCFullYear(), startsOn.getUTCMonth() + 1, 0));
                    const cells = weekdays.map((day) => `<div class="py-1 text-center text-xs font-black text-slate-500">${day}</div>`);
                    for (let index = 0; index < monthStart.getUTCDay(); index += 1) {
                        cells.push('<div class="min-h-36 rounded-md border border-transparent bg-transparent"></div>');
                    }
                    for (const date = new Date(monthStart); date <= monthEnd; date.setUTCDate(date.getUTCDate() + 1)) {
                        const scheduledOn = formatDate(date);
                        const isInRange = date >= startsOn && date <= endsOn;
                        if (!isInRange) {
                            cells.push(`
                                <div class="min-h-36 rounded-md border border-slate-100 bg-slate-100/60 p-2 text-xs font-bold text-slate-400">
                                    ${date.getUTCDate()}
                                </div>
                            `);
                            continue;
                        }
                        const values = existingValues[scheduledOn] || {};
                        const selectedStoreId = values.storeId || defaultStoreId;
                        const isDayOff = Boolean(values.isDayOff);
                        cells.push(`
                            <div class="min-h-36 space-y-2 rounded-md border border-slate-200 bg-white p-2" data-schedule-day-row="${scheduledOn}">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm font-black text-slate-800">${date.getUTCDate()}</span>
                                    <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600">
                                        <input type="checkbox" class="rounded border-slate-300 text-teal-700 accent-teal-700" data-schedule-day-off ${isDayOff ? 'checked' : ''}>
                                        休み
                                    </label>
                                </div>
                                <select class="min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 disabled:bg-slate-100 disabled:text-slate-400" data-schedule-day-store ${isDayOff ? 'disabled' : ''}>
                                    ${scheduleDayOptions(selectedStoreId)}
                                </select>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="time" class="min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 disabled:bg-slate-100 disabled:text-slate-400" data-schedule-day-start value="${escapeHtml(values.startsAt || '')}" ${isDayOff ? 'disabled' : ''}>
                                    <input type="time" class="min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-teal-500 disabled:bg-slate-100 disabled:text-slate-400" data-schedule-day-end value="${escapeHtml(values.endsAt || '')}" ${isDayOff ? 'disabled' : ''}>
                                </div>
                            </div>
                        `);
                    }
                    list.innerHTML = `<div class="grid min-w-[760px] grid-cols-7 gap-2">${cells.join('')}</div>`;
                };
                const schedulePayload = (form) => ({
                    ...formPayload(form),
                    days: $$('[data-schedule-day-row]').map((row) => {
                        const isDayOff = row.querySelector('[data-schedule-day-off]')?.checked || false;

                        return {
                            scheduled_on: row.dataset.scheduleDayRow,
                            is_day_off: isDayOff,
                            store_id: isDayOff ? null : row.querySelector('[data-schedule-day-store]')?.value,
                            starts_at: isDayOff ? null : row.querySelector('[data-schedule-day-start]')?.value,
                            ends_at: isDayOff ? null : row.querySelector('[data-schedule-day-end]')?.value,
                        };
                    }),
                });
                const setScheduleFormMode = (schedule = null) => {
                    const form = $('[data-form="schedule"]');
                    if (!form) return;

                    editingSchedule = schedule;
                    if (schedule) {
                        form.dataset.scheduleId = schedule.id;
                        form.elements.store_id.value = schedule.store_id || '';
                        form.elements.starts_on.value = schedule.starts_on || '';
                        form.elements.ends_on.value = schedule.ends_on || '';
                    } else {
                        delete form.dataset.scheduleId;
                        form.reset();
                    }
                    $('[data-schedule-submit-label]') && ($('[data-schedule-submit-label]').textContent = schedule ? '下書きを保存' : '下書き作成');
                    $('[data-action="cancel-schedule-edit"]')?.classList.toggle('hidden', !schedule);
                    $('[data-action="cancel-schedule-edit"]')?.classList.toggle('inline-flex', Boolean(schedule));
                    renderScheduleDayFields();
                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                };

                const renderSchedules = () => {
                    const list = $('[data-list="schedules"]');
                    const storeId = $('[data-filter="store"]')?.value || '';
                    const schedules = storeId ? state.schedules.filter((schedule) => scheduleMatchesStore(schedule, storeId)) : state.schedules;

                    list.innerHTML = schedules.length
                        ? schedules.map((schedule) => `
                            <article class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-base font-black text-slate-950">${escapeHtml(schedule.store?.name || '-')}</h3>
                                        <p class="mt-1 text-sm text-slate-600">${escapeHtml(schedule.starts_on)} - ${escapeHtml(schedule.ends_on)}</p>
                                        <p class="mt-1 text-xs leading-5 text-slate-500">${scheduleStoreSummary(schedule)}</p>
                                        <p class="mt-2 text-xs text-slate-500">枠数 ${schedule.shift_slots?.length || 0}</p>
                                    </div>
                                    ${statusBadge(schedule.status)}
                                </div>
                                <div class="mt-3 flex justify-end gap-2">
                                    <button type="button" class="rounded-md border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50" data-edit-schedule="${schedule.id}">編集</button>
                                    ${schedule.status === 'published'
                                        ? '<span class="text-xs font-bold text-slate-500">公開済みです</span>'
                                        : `<button type="button" class="rounded-md border border-teal-200 px-3 py-2 text-xs font-bold text-teal-700 transition hover:bg-teal-50" data-publish="${schedule.id}">公開する</button>`}
                                </div>
                            </article>
                        `).join('')
                        : '<p class="px-4 py-8 text-center text-sm text-slate-500">シフト表がありません。</p>';
                };

                const renderMembers = () => {
                    const list = $('[data-list="members"]');
                    if (!list) return;

                    list.innerHTML = state.members.length
                        ? state.members.map((member) => `
                            <article class="p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-base font-black text-slate-950">${escapeHtml(memberDisplayName(member))}</h3>
                                        ${member.name && member.name !== memberDisplayName(member) ? `<p class="mt-1 truncate text-xs text-slate-500">${escapeHtml(member.name)}</p>` : ''}
                                        <p class="mt-1 text-sm text-slate-600">${escapeHtml(member.store?.name || '未割り当て')}</p>
                                        <p class="mt-1 text-xs text-slate-500">${escapeHtml(member.phone || member.email || '')}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full border px-2.5 py-1 text-xs font-black ${memberRoleClass(member.role)}">${memberRoleLabel(member.role)}</span>
                                </div>
                            </article>
                        `).join('')
                        : '<p class="px-4 py-8 text-center text-sm text-slate-500">キャストがまだ登録されていません。</p>';
                };

                const refresh = async () => {
                    const requests = [
                        api(routes.stores),
                        api(`${routes.schedules}?per_page=100`),
                    ];
                    if (canManageMembers) {
                        requests.push(api(`${routes.members}?per_page=100`));
                    }

                    const [stores, schedules, members] = await Promise.all(requests);
                    state.stores = stores.stores || [];
                    state.schedules = schedules.data || [];
                    state.members = members?.data || [];
                    renderStores();
                    renderSchedules();
                    renderMembers();
                };

                $('[data-form="schedule"]')?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    const scheduleId = form.dataset.scheduleId;
                    try {
                        await api(scheduleId ? `${routes.publishScheduleBase}/${scheduleId}` : routes.createSchedule, {
                            method: scheduleId ? 'PUT' : 'POST',
                            body: JSON.stringify({ status: editingSchedule?.status || 'draft', ...schedulePayload(form) }),
                        });
                        setScheduleFormMode(null);
                        await refresh();
                        setNotice(scheduleId ? 'シフト表を更新しました。' : 'シフト表を作成しました。');
                    } catch (error) {
                        setNotice(error.message, 'error');
                    }
                });

                $('[data-form="member"]')?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    try {
                        await api(routes.createMember, {
                            method: 'POST',
                            body: JSON.stringify({ status: 'active', is_shift_submitter: true, ...formPayload(form) }),
                        });
                        form.reset();
                        await refresh();
                        setNotice('キャストを追加しました。');
                    } catch (error) {
                        setNotice(error.message, 'error');
                    }
                });

                $('[data-filter="store"]')?.addEventListener('change', renderSchedules);
                $('[data-form="schedule"]')?.addEventListener('input', (event) => {
                    if (event.target.matches('input[name="starts_on"], input[name="ends_on"]')) {
                        renderScheduleDayFields();
                    }
                });
                $('[data-form="schedule"] select[name="store_id"]')?.addEventListener('change', renderScheduleDayFields);
                $('[data-list="schedule-days"]')?.addEventListener('change', (event) => {
                    if (!event.target.matches('[data-schedule-day-off]')) return;
                    const row = event.target.closest('[data-schedule-day-row]');
                    row?.querySelectorAll('[data-schedule-day-store], [data-schedule-day-start], [data-schedule-day-end]').forEach((field) => {
                        field.disabled = event.target.checked;
                    });
                });
                document.addEventListener('click', async (event) => {
                    const editButton = event.target.closest('[data-edit-schedule]');
                    if (editButton) {
                        const schedule = state.schedules.find((item) => String(item.id) === String(editButton.dataset.editSchedule));
                        if (schedule) {
                            setScheduleFormMode(schedule);
                        }
                        return;
                    }

                    if (event.target.closest('[data-action="cancel-schedule-edit"]')) {
                        setScheduleFormMode(null);
                        return;
                    }

                    const button = event.target.closest('[data-publish]');
                    if (!button) return;

                    try {
                        await api(`${routes.publishScheduleBase}/${button.dataset.publish}/publish`, {
                            method: 'POST',
                            body: '{}',
                        });
                        await refresh();
                        setNotice('シフト表を公開しました。');
                    } catch (error) {
                        setNotice(error.message, 'error');
                    }
                });

                refresh().catch((error) => setNotice(error.message, 'error'));
            })();
        </script>
    </body>
</html>
