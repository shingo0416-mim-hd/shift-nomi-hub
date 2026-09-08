const escape = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'}[c]));
export const dateKey = (date) => date.toISOString().slice(0, 10);
export const japanToday = (now = new Date()) => new Intl.DateTimeFormat('sv-SE', {timeZone: 'Asia/Tokyo', year: 'numeric', month: '2-digit', day: '2-digit'}).format(now);
export const monthDate = (date, offset = 0) => new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + offset, 1));
const dayKey = (value) => String(value || '').slice(0, 10);
export const inPeriod = (schedule, day) => dayKey(schedule.starts_on) <= day && day <= dayKey(schedule.ends_on);
export const inMonth = (schedule, month) => dayKey(schedule.starts_on) <= dateKey(new Date(Date.UTC(month.getUTCFullYear(), month.getUTCMonth() + 1, 0))) && dayKey(schedule.ends_on) >= dateKey(month);
export const monthCells = (month) => {
    const first = monthDate(month);
    const count = new Date(Date.UTC(first.getUTCFullYear(), first.getUTCMonth() + 1, 0)).getUTCDate();
    const cells = Array(first.getUTCDay()).fill(null);
    for (let day = 1; day <= count; day++) cells.push(new Date(Date.UTC(first.getUTCFullYear(), first.getUTCMonth(), day)));
    while (cells.length % 7) cells.push(null);
    return cells;
};
const labelMonth = (date) => `${date.getUTCFullYear()}年${date.getUTCMonth() + 1}月`;
const weekdays = ['日', '月', '火', '水', '木', '金', '土'];
const deadlineDay = (value) => value ? japanToday(new Date(value.includes('T') ? value : value.replace(' ', 'T') + '+09:00')) : '';

export function mountShiftCalendar(root) {
    const content = root.querySelector('[data-calendar-content]');
    const today = japanToday();
    const currentMonth = monthDate(new Date(`${today}T00:00:00Z`));
    let viewMonth = currentMonth;
    let selectedDay = today;
    let selectedMonth = null;
    const read = () => JSON.parse(root.querySelector('[data-calendar-source]').textContent);
    function render() {
        const {schedules = [], editBase = '/dashboard/schedules'} = read();
        const deadlines = new Set(schedules.map((s) => deadlineDay(s.submission_deadline_at)).filter(Boolean));
        const months = [viewMonth, monthDate(viewMonth, 1)];
        const calendar = months.map((month) => `<div class="shift-mini-month"><h3>${labelMonth(month)}</h3><table><caption class="sr-only">${labelMonth(month)}のシフトを確認</caption><thead><tr>${weekdays.map((d, i) => `<th scope="col" class="${i === 0 ? 'is-sunday' : i === 6 ? 'is-saturday' : ''}">${d}</th>`).join('')}</tr></thead><tbody>${monthCells(month).map((date, i) => {
            const start = i % 7 === 0 ? '<tr>' : '';
            const end = i % 7 === 6 ? '</tr>' : '';
            if (!date) return `${start}<td></td>${end}`;
            const key = dateKey(date);
            const scheduled = schedules.some((s) => inPeriod(s, key));
            const deadline = deadlines.has(key);
            const active = selectedDay === key;
            const classes = [date.getUTCDay() === 0 ? 'is-sunday' : date.getUTCDay() === 6 ? 'is-saturday' : '', scheduled ? 'has-schedule' : '', key === today ? 'is-today' : '', deadline ? 'is-deadline' : '', active ? 'is-selected' : ''].join(' ');
            return `${start}<td class="${classes}"><button type="button" data-calendar-date="${key}" aria-pressed="${active}" ${key === today ? 'aria-current="date"' : ''} aria-label="${key}${scheduled ? ' シフト期間' : ''}${deadline ? ' 希望提出期限' : ''}">${date.getUTCDate()}</button></td>${end}`;
        }).join('')}</tbody></table></div>`).join('');
        const matching = schedules.filter((s) => selectedMonth ? inMonth(s, selectedMonth) : inPeriod(s, selectedDay));
        const results = matching.map((s) => {
            const day = selectedDay ? (s.days || []).find((d) => dayKey(d.scheduled_on) === selectedDay) : null;
            const description = selectedMonth ? `${dayKey(s.starts_on)} ～ ${dayKey(s.ends_on)}` : day?.is_day_off ? '休業日' : day?.starts_at && day?.ends_at ? `${day.starts_at.slice(0, 5)} ～ ${day.ends_at.slice(0, 5)}　必要人数 ${day.required_headcount || 1}名` : '勤務時間未設定';
            return `<a class="shift-calendar-result" href="${escape(editBase)}/${encodeURIComponent(s.id)}/edit${selectedDay ? '#schedule-day-' + selectedDay : ''}"><div><strong>${escape(s.store)}</strong><p>${escape(description)}</p></div><span>${s.status === 'published' ? '公開済み' : '編集中'} <b aria-hidden="true">›</b></span></a>`;
        }).join('');
        content.innerHTML = `<div class="shift-today-bar">${today.replace('-', '年').replace('-', '月')}日 <span>(${weekdays[new Date(today + 'T00:00:00Z').getUTCDay()]})</span></div>
            <div class="shift-calendar-layout"><div class="shift-calendar-main"><h2>日別シフト<span>を確認</span></h2><div class="shift-month-controls"><button type="button" data-calendar-nav="-1" aria-label="前の月へ">‹</button><button type="button" data-calendar-nav="today">今月に戻る</button><button type="button" data-calendar-nav="1" aria-label="次の月へ">›</button></div><div class="shift-month-pair">${calendar}</div><div class="shift-calendar-legend"><span><i class="legend-period"></i>シフト期間</span><span><i class="legend-deadline"></i>希望提出期限</span><span><i class="legend-today"></i>今日</span></div></div>
            <div class="shift-calendar-shortcuts"><button type="button" class="shift-month-link primary" data-calendar-month="0">▦ 当月シフト<span>を確認</span></button><button type="button" class="shift-month-link" data-calendar-month="1">▦ 翌月シフト<span>を確認</span></button><a class="shift-calendar-guide shift-diagnosis-link" href="/dashboard/analytics"><img src="/images/rshift/diagnosis.png" alt=""><strong>シフト分析</strong><p>人員配置の過不足を確認</p><span>詳しく見る →</span></a></div></div>
            <div class="shift-calendar-results"><h3>${selectedMonth ? labelMonth(selectedMonth) : selectedDay.replaceAll('-', '/')} のシフト</h3><div aria-live="polite">${results || '<p class="shift-calendar-empty">この期間のシフトは登録されていません。</p>'}</div></div>`;
    }
    root.addEventListener('click', (event) => {
        const date = event.target.closest('[data-calendar-date]');
        const nav = event.target.closest('[data-calendar-nav]');
        const month = event.target.closest('[data-calendar-month]');
        let focusSelector;
        if (date) {
            selectedDay = date.dataset.calendarDate;
            selectedMonth = null;
            focusSelector = `[data-calendar-date="${selectedDay}"]`;
        } else if (nav) {
            viewMonth = nav.dataset.calendarNav === 'today' ? currentMonth : monthDate(viewMonth, Number(nav.dataset.calendarNav));
            selectedDay = null;
            selectedMonth = viewMonth;
            focusSelector = `[data-calendar-nav="${nav.dataset.calendarNav}"]`;
        } else if (month) {
            selectedMonth = monthDate(currentMonth, Number(month.dataset.calendarMonth));
            viewMonth = currentMonth;
            selectedDay = null;
            focusSelector = `[data-calendar-month="${month.dataset.calendarMonth}"]`;
        } else return;
        render();
        content.querySelector(focusSelector)?.focus({preventScroll: true});
    });
    document.addEventListener('shift-calendar:update', render);
    render();
}
