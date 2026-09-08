import test from 'node:test';
import assert from 'node:assert/strict';
import { dateKey, japanToday, monthDate, monthCells, inPeriod, inMonth } from '../../resources/js/shift-calendar.js';

test('month navigation crosses the year boundary', () => {
    const december = new Date('2026-12-01T00:00:00Z');
    assert.equal(dateKey(monthDate(december, 1)), '2027-01-01');
    assert.equal(dateKey(monthDate(new Date('2027-01-01T00:00:00Z'), -1)), '2026-12-01');
});

test('leap February contains all 29 days in complete Sunday-first weeks', () => {
    const cells = monthCells(new Date('2028-02-01T00:00:00Z'));
    assert.equal(cells.filter(Boolean).length, 29);
    assert.equal(cells.length % 7, 0);
    assert.equal(cells[0], null);
    assert.equal(cells[1], null);
    assert.equal(dateKey(cells[2]), '2028-02-01');
    assert.equal(dateKey(cells.filter(Boolean).at(-1)), '2028-02-29');
});

test('today follows Japan time around midnight', () => {
    assert.equal(japanToday(new Date('2026-09-08T14:59:59Z')), '2026-09-08');
    assert.equal(japanToday(new Date('2026-09-08T15:00:00Z')), '2026-09-09');
});

test('schedule selection includes both boundaries and overlapping months', () => {
    const schedule = { starts_on: '2026-09-25', ends_on: '2026-10-05' };
    assert.equal(inPeriod(schedule, '2026-09-25'), true);
    assert.equal(inPeriod(schedule, '2026-10-05'), true);
    assert.equal(inPeriod(schedule, '2026-10-06'), false);
    assert.equal(inMonth(schedule, new Date('2026-10-01T00:00:00Z')), true);
    assert.equal(inMonth(schedule, new Date('2026-11-01T00:00:00Z')), false);
});
