<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>希望シフト提出 - {{ config('app.name', 'ShiftHub') }}</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<main class="mx-auto max-w-2xl space-y-5 px-4 py-6">
    <a class="font-bold text-teal-700 underline" href="{{ route('line.workforce', ['tenant' => request()->attributes->get('tenantPath')]) }}">確定シフト・欠員募集・お知らせ</a>
    <header><p class="text-sm font-bold text-teal-700">{{ $member->displayName() }} さん</p><h1 class="mt-1 text-2xl font-black">希望シフトの提出・確認</h1><p class="mt-2 text-sm text-slate-600">日付ごとに希望を保存してください。休み希望も含め、すべての日付を入力すると提出完了になります。締切前は何度でも修正できます。</p></header>
    @if(session('notice'))<p role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('notice') }}</p>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @forelse($schedules as $schedule)
        @php
            $days = $schedule->days->sortBy('scheduled_on');
            $completed = $days->filter(fn ($day) => $availability->has($day->scheduled_on->toDateString()))->count();
            $closed = $schedule->status !== 'draft' || ($schedule->submission_deadline_at && $schedule->submission_deadline_at->lte(now()));
        @endphp
        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-lg font-black">{{ $schedule->starts_on->format('Y/n/j') }}〜{{ $schedule->ends_on->format('n/j') }}</h2>
            <p class="mt-1 text-sm">提出締切：{{ $schedule->submission_deadline_at?->format('Y/n/j H:i') ?? '未設定' }}</p>
            <p class="mt-2 font-bold text-teal-800">{{ $completed === $days->count() && $completed > 0 ? '提出完了' : ($completed > 0 ? '一部入力' : '未提出') }}（{{ $completed }} / {{ $days->count() }}日）</p>
            @if($closed)<p class="mt-2 rounded-lg bg-slate-100 p-3 text-sm">受付終了：内容を確認できます。変更が必要な場合は管理者へ連絡してください。</p>@endif
            <div class="mt-4 space-y-3">
            @foreach($days as $day)
                @php
                    $date = $day->scheduled_on->toDateString();
                    $entry = $availability->get($date);
                    $retry = old('work_date') === $date;
                    $preference = $retry ? old('preference') : $entry?->preference;
                @endphp
                <details class="rounded-xl border border-slate-200" @if($retry) open @endif>
                    <summary class="cursor-pointer px-3 py-4 text-sm font-bold">
                        {{ $day->scheduled_on->format('n/j') }}（{{ ['日','月','火','水','木','金','土'][$day->scheduled_on->dayOfWeek] }}）
                        {{ $entry ? ['available'=>'勤務可能','preferred'=>'勤務希望','unavailable'=>'休み希望'][$entry->preference] : '未入力' }}
                        @if($entry?->available_from) {{ substr($entry->available_from, 0, 5) }}〜{{ substr($entry->available_until, 0, 5) }} @endif
                    </summary>
                    <form method="POST" action="{{ route('line.availability.store', ['tenant' => request()->attributes->get('tenantPath'), 'shiftSchedule' => $schedule]) }}" class="space-y-3 border-t border-slate-100 p-3">
                        @csrf
                        <input type="hidden" name="work_date" value="{{ $date }}">
                        <p class="text-xs text-slate-500">{{ $day->store?->name }}{{ $day->is_day_off ? '（休業日）' : '' }}</p>
                        <fieldset class="space-y-3" @disabled($closed)>
                            <label class="block text-sm font-bold">希望区分
                                <select name="preference" required class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3">
                                    <option value="">選択してください</option>
                                    @foreach(['available'=>'勤務可能', 'preferred'=>'勤務希望', 'unavailable'=>'休み希望'] as $value=>$label)<option value="{{ $value }}" @selected($preference === $value)>{{ $label }}</option>@endforeach
                                </select>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="min-w-0 text-sm font-bold">開始時間<input type="time" name="available_from" value="{{ $retry ? old('available_from') : substr($entry?->available_from ?? '', 0, 5) }}" class="mt-1 min-h-11 w-full min-w-0 rounded-lg border border-slate-300 px-2"></label>
                                <label class="min-w-0 text-sm font-bold">終了時間<input type="time" name="available_until" value="{{ $retry ? old('available_until') : substr($entry?->available_until ?? '', 0, 5) }}" class="mt-1 min-h-11 w-full min-w-0 rounded-lg border border-slate-300 px-2"></label>
                            </div>
                            <p class="text-xs text-slate-500">勤務可能・勤務希望の場合は開始・終了時間を入力してください。休み希望の場合は不要です。</p>
                            <label class="block text-sm font-bold">備考<textarea name="notes" maxlength="1000" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 p-2">{{ $retry ? old('notes') : $entry?->notes }}</textarea></label>
                            @unless($closed)<button class="min-h-11 w-full rounded-lg bg-teal-700 px-4 py-3 font-bold text-white" type="submit">{{ $entry ? '変更を保存する' : 'この日の希望を提出する' }}</button>@endunless
                        </fieldset>
                    </form>
                </details>
            @endforeach
            </div>
        </section>
    @empty
        <p class="rounded-xl bg-white p-5 text-sm">現在、対象のシフト募集はありません。店舗や募集期間について管理者へ確認してください。</p>
    @endforelse
</main>
</body>
</html>
