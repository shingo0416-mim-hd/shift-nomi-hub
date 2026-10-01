<section class="shift-analysis" data-shift-analysis>
    <script type="application/json" data-analysis-source>{!! json_encode($analyticsData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <header class="analysis-heading"><img src="{{ asset('images/rshift/diagnosis.png') }}" alt=""><div><h1>シフトの人員配置を分析</h1><p>月ごとの推移と、日ごとの不足・過剰を確認できます。</p></div></header>
    <form method="GET" action="{{ route('admin.analytics') }}" class="analysis-controls">
        <label>対象年 <input type="number" min="2000" max="2100" name="year" value="{{ $analyticsData['year'] }}" required></label><button type="submit">表示</button>
        <label>店舗 <select data-analysis-store><option value="">全店舗</option>@foreach ($analyticsData['stores'] as $store)<option value="{{ $store['id'] }}">{{ $store['name'] }}</option>@endforeach</select></label>
        <label>状態 <select data-analysis-status><option value="">編集中・公開済み</option><option value="published">公開済み</option><option value="draft">編集中</option></select></label>
        <button type="button" data-analysis-export><img src="{{ asset('images/rshift/download.svg') }}" alt="">CSV出力</button>
    </form>
    <p class="analysis-source-note">予定シフトを集計・登録済み休憩時間帯を除外　／　取得日時 {{ \Carbon\CarbonImmutable::parse($analyticsData['generated_at'])->format('Y/m/d H:i') }}</p>
    <div data-analysis-content></div>
    <details class="analysis-definitions"><summary>集計の定義・改善のチェックポイント</summary><div>
        <p>対象年の日別シフトを店舗・状態で絞り込んで集計します。アーカイブ済みのシフトは対象外です。</p>
        <ul><li>必要人時：日別シフトの勤務時間 × 必要人数。</li><li>配置人時：登録された割当時間の合計。同一シフト日の同じ人の重複割当は二重計上しません。取消済み・削除済みメンバーの割当は除きます。</li><li>不足・過剰人時：時間帯ごとに必要人数と配置人数を比較して積算します。別の時間帯の過剰で不足を相殺しません。</li><li>充足率：（必要人時 − 不足人時）÷ 必要人時 × 100。必要人時が0の場合は「—」です。全店舗は人時を合算した加重比率です。</li><li>勤務時間未設定の日は集計から除外します。未編成のシフトは配置0として不足に含めます。日をまたぐ勤務は開始日のシフトとして集計します。</li><li>休憩の開始・終了が登録されている場合は、その時間帯を配置人時から除きます。休憩時間帯が未登録の場合は控除しません。この画面は勤務実績・売上を含まない予定シフトの分析です。</li></ul>
        <p>不足がある日は時間帯・必要人数・割当を、過剰がある日は重複や勤務時間を確認してください。</p>
    </div></details>
</section>
