const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'}[c]));
const number = (value) => new Intl.NumberFormat('ja-JP', {maximumFractionDigits: 1}).format(value);
export function summarize(rows) {
    const result = {required: 0, assigned: 0, shortage: 0, excess: 0, configured: 0, excluded: 0, coverage: null};
    for (const row of rows) {
        if (!row.configured) { result.excluded++; continue; }
        result.configured++;
        for (const key of ['required', 'assigned', 'shortage', 'excess']) result[key] += row[key];
    }
    if (result.required > 0) result.coverage = Math.max(0, Math.min(100, (result.required - result.shortage) / result.required * 100));
    return result;
}
export function csvCell(value) {
    const text = String(value ?? '');
    return '"' + (/^[\s]*[=+\-@]/.test(text) ? "'" + text : text).replaceAll('"', '""') + '"';
}
export function monthSummary(rows, year) {
    return Array.from({length: 12}, (_, i) => {
        const key = `${year}-${String(i + 1).padStart(2, '0')}`;
        return {key, ...summarize(rows.filter((row) => row.date.slice(0, 7) === key))};
    });
}
function chart(series, comparison) {
    const x = (i) => 55 + i * 75;
    const y = (value) => 270 - value * 2.2;
    const line = (data, color, className) => {
        let segment = ''; const paths = [];
        data.forEach((point, i) => {
            if (point.coverage === null) { if (segment) paths.push(segment); segment = ''; }
            else segment += `${segment ? ' L' : 'M'}${x(i)},${y(point.coverage)}`;
        });
        if (segment) paths.push(segment);
        return paths.map((path) => `<path d="${path}" stroke="${color}" stroke-width="3" fill="none"/>`).join('') + data.map((point, i) => point.coverage === null ? '' : `<circle class="${className}" cx="${x(i)}" cy="${y(point.coverage)}" r="5" fill="${color}" tabindex="0" role="button" data-analysis-month="${i + 1}" aria-label="${i + 1}月 ${className === 'analysis-point' ? '選択店舗' : '全店舗'}充足率 ${number(point.coverage)}%、日別を表示"><title>${i + 1}月：${number(point.coverage)}%\n必要 ${number(point.required)} / 配置 ${number(point.assigned)} / 不足 ${number(point.shortage)} / 過剰 ${number(point.excess)} 人時</title></circle>`).join('');
    };
    return `<svg viewBox="0 0 940 320" role="group" aria-label="月別の充足率。数値は直前の月別表でも確認できます。">${[0,20,40,60,80,100].map((v) => `<line x1="55" y1="${y(v)}" x2="880" y2="${y(v)}" stroke="#e2e8e8"/><text x="42" y="${y(v) + 4}" text-anchor="end">${v}%</text>`).join('')}${series.map((_, i) => `<line x1="${x(i)}" y1="50" x2="${x(i)}" y2="270" stroke="#edf1f1"/><text x="${x(i)}" y="296" text-anchor="middle">${i+1}月</text>`).join('')}${comparison ? line(comparison, '#afb9ba', 'analysis-comparison-point') : ''}${line(series, '#2164ce', 'analysis-point')}</svg>`;
}
export function mountShiftAnalysis(root) {
    const data = JSON.parse(root.querySelector('[data-analysis-source]').textContent);
    const content = root.querySelector('[data-analysis-content]');
    const storeSelect = root.querySelector('[data-analysis-store]');
    const statusSelect = root.querySelector('[data-analysis-status]');
    const storeNames = new Map(data.stores.map((store) => [String(store.id), store.name]));
    let selectedMonth = new Date().getMonth() + 1;
    if (data.rows.length) selectedMonth = Number(data.rows.at(-1).date.slice(5,7));
    function filtered(ignoreStore = false) {
        return data.rows.filter((row) => (!statusSelect.value || row.status === statusSelect.value) && (ignoreStore || !storeSelect.value || String(row.store_id) === storeSelect.value));
    }
    function render(focusMonth = false) {
        const rows = filtered();
        const allRows = filtered(true);
        const totals = summarize(rows);
        const months = monthSummary(rows, data.year);
        const selected = months[selectedMonth - 1];
        const storeLabel = storeSelect.value ? storeNames.get(storeSelect.value) : '全店舗（合算）';
        const monthRows = rows.filter((row) => row.date.startsWith(selected.key));
        const ranking = data.stores.map((store) => ({...store, ...summarize(allRows.filter((row) => String(row.store_id) === String(store.id) && row.date.startsWith(selected.key)))})).sort((a,b) => (b.coverage ?? -1) - (a.coverage ?? -1));
        content.innerHTML = `<div class="analysis-kpis" aria-label="対象年の合計">${[['required','必要人時'],['assigned','配置人時'],['shortage','不足人時'],['excess','過剰人時']].map(([key,label]) => `<article class="metric-${key}"><span>${data.year}年 ${label}</span><strong>${totals.configured ? number(totals[key]) : '—'}<small>人時</small></strong></article>`).join('')}</div>
            ${totals.excluded ? `<p class="analysis-warning">勤務時間未設定の ${totals.excluded} 件を除外しています。日別明細から設定を確認してください。</p>` : ''}
            <div class="analysis-section-title"><h2>月度ごとの推移 <span>（${data.year}年1月 ～ 12月）</span></h2><p>月を選択すると日別の配置を表示します。</p></div>
            <div class="analysis-table-scroll"><table class="analysis-month-table"><caption class="sr-only">${esc(storeLabel)}の月別集計</caption><thead><tr><th scope="col">${esc(storeLabel)}</th>${months.map((m,i) => `<th scope="col"><button type="button" data-analysis-month="${i+1}" aria-pressed="${selectedMonth===i+1}">${i+1}月</button></th>`).join('')}</tr></thead><tbody>${[['coverage','充足率','%'],['shortage','不足人時',''],['excess','過剰人時','']].map(([key,label,unit]) => `<tr><th scope="row">${label}</th>${months.map((m) => `<td>${m.configured && m[key] !== null ? number(m[key])+unit : '—'}</td>`).join('')}</tr>`).join('')}</tbody></table></div>
            <h2 class="analysis-chart-title">充足率の推移</h2><div class="analysis-legend"><span><i></i>${esc(storeLabel)}</span>${storeSelect.value ? '<span><i class="comparison"></i>全店舗（合算）</span>' : ''}</div>
            <div class="analysis-chart-scroll">${chart(months, storeSelect.value ? monthSummary(allRows,data.year) : null)}</div>
            ${!totals.configured ? '<p class="analysis-empty">集計できるシフトがありません。対象年・店舗・状態、勤務時間の設定を確認してください。</p>' : ''}
            <section class="analysis-month-detail"><div class="analysis-section-title"><h2>${selectedMonth}月の日別明細</h2><span>必要 ${selected.configured ? number(selected.required) : '—'} ／ 配置 ${selected.configured ? number(selected.assigned) : '—'} ／ 不足 ${selected.configured ? number(selected.shortage) : '—'} ／ 過剰 ${selected.configured ? number(selected.excess) : '—'} 人時</span></div>
            <div class="analysis-table-scroll"><table><thead><tr><th>日付</th><th>店舗</th><th>必要人時</th><th>配置人時</th><th>不足人時</th><th>過剰人時</th><th>状態・操作</th></tr></thead><tbody>${monthRows.map((row) => `<tr><td>${esc(row.date)}</td><td>${esc(storeNames.get(String(row.store_id)))}</td>${['required','assigned','shortage','excess'].map((key) => `<td class="${key === 'shortage' && row[key] > 0 ? 'has-shortage' : key==='excess' && row[key] > 0 ? 'has-excess' : ''}">${row.configured ? number(row[key]) : '—'}</td>`).join('')}<td><a href="/dashboard/schedules/${encodeURIComponent(row.schedule_id)}/edit#schedule-day-${row.date}">${!row.configured ? '時間未設定' : row.status === 'published' ? '公開済み' : '編集中'} · 調整 →</a></td></tr>`).join('') || '<tr><td colspan="7">対象月のシフトはありません。</td></tr>'}</tbody></table></div></section>
            <section class="analysis-ranking"><h2><img src="/images/rshift/analytics.png" alt="">${selectedMonth}月の店舗比較 <span>全店舗・同じ状態条件で比較</span></h2><div class="analysis-table-scroll"><table><thead><tr><th>店舗</th><th>充足率</th><th>不足人時</th><th>過剰人時</th><th>時間未設定</th></tr></thead><tbody>${ranking.map((store) => `<tr><td>${esc(store.name)}</td><td>${store.coverage === null ? '—' : number(store.coverage)+'%'}</td><td>${store.configured ? number(store.shortage) : '—'}</td><td>${store.configured ? number(store.excess) : '—'}</td><td>${store.excluded}件</td></tr>`).join('') || '<tr><td colspan="5">店舗が登録されていません。</td></tr>'}</tbody></table></div></section>`;
        if (focusMonth) content.querySelector(`button[data-analysis-month="${selectedMonth}"]`)?.focus({preventScroll: true});
    }
    root.addEventListener('click', (event) => {
        const month = event.target.closest('[data-analysis-month]');
        if (month) { selectedMonth = Number(month.dataset.analysisMonth); render(true); }
        if (event.target.closest('[data-analysis-export]')) {
            const lines = [['日付','店舗','状態','必要人時','配置人時','不足人時','過剰人時','設定状態','備考'], ...filtered().map((row) => [row.date, storeNames.get(String(row.store_id)), row.status, ...['required','assigned','shortage','excess'].map((k) => row.configured ? Number(row[k].toFixed(4)) : ''), row.configured ? '設定済み' : '時間未設定', '予定・登録済み休憩時間帯を除外'])];
            const blob = new Blob(['\ufeff'+lines.map((line) => line.map(csvCell).join(',')).join('\r\n')], {type:'text/csv;charset=utf-8;'});
            const url = URL.createObjectURL(blob); const link = document.createElement('a'); link.href=url; link.download=`shift-analysis-${data.year}.csv`; link.click(); setTimeout(()=>URL.revokeObjectURL(url),1000);
        }
    });
    root.addEventListener('keydown', (event) => {
        if (event.target.matches('circle[data-analysis-month]') && ['Enter',' '].includes(event.key)) { event.preventDefault(); selectedMonth=Number(event.target.dataset.analysisMonth); render(true); }
    });
    storeSelect.addEventListener('change', () => render());
    statusSelect.addEventListener('change', () => render());
    render();
}
