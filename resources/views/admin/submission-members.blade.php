<section class="rounded-lg border border-slate-200 bg-white p-4">
    <h3 class="font-black text-slate-950">スタッフ別の提出状況</h3>
    <p class="mt-1 text-sm text-slate-500">全日分の入力で提出完了になります。名前を開くと提出内容を確認できます。</p>
    <p class="mt-2 text-sm font-bold text-teal-700">提出率 {{ data_get($editingSchedule, 'operations.submission_percent', 0) }}%</p>
    <div class="mt-3 divide-y divide-slate-200">
        @forelse($editingSchedule->submission_members ?? [] as $submission)
            <details class="py-3">
                <summary class="cursor-pointer text-sm"><strong>{{ $submission['name'] }}</strong>　{{ $submission['status'] }}（{{ $submission['submitted_days'] }} / {{ $submission['expected_days'] }}日）</summary>
                <div class="mt-2 space-y-2">
                    @forelse($submission['entries'] as $entry)
                        <div class="rounded-lg bg-slate-50 p-3 text-sm">
                            <p class="font-bold">{{ $entry->work_date->format('n/j') }}　{{ ['available'=>'勤務可能','preferred'=>'勤務希望','unavailable'=>'休み希望'][$entry->preference] }}
                            @if($entry->available_from)　{{ substr($entry->available_from, 0, 5) }}〜{{ substr($entry->available_until, 0, 5) }}@endif</p>
                            @if($entry->notes)<p class="mt-1 whitespace-pre-wrap break-words text-slate-600">{{ $entry->notes }}</p>@endif
                        </div>
                    @empty<p class="text-sm text-slate-500">まだ提出されていません。</p>@endforelse
                </div>
            </details>
        @empty<p class="py-3 text-sm text-slate-500">提出対象のスタッフがいません。</p>@endforelse
    </div>
</section>
