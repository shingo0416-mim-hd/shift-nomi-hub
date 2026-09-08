import test from 'node:test';
import assert from 'node:assert/strict';
import {summarize, monthSummary, csvCell} from '../../resources/js/shift-analysis.js';

test('coverage uses required hours as the denominator, not an average of store percentages', () => {
    const rows = [{configured:true,required:1,assigned:0,shortage:1,excess:0},{configured:true,required:9,assigned:9,shortage:0,excess:0}];
    assert.equal(summarize(rows).coverage, 90);
});
test('missing configuration and absent months remain missing, not zero-valued performance', () => {
    const months = monthSummary([{date:'2026-09-01',configured:false}],2026);
    assert.equal(months[8].excluded,1);
    assert.equal(months[8].coverage,null);
    assert.equal(months[7].coverage,null);
});
test('CSV cells escape quotes and prevent spreadsheet formulas', () => {
    assert.equal(csvCell('店舗"A'), '"店舗""A"');
    assert.equal(csvCell('=1+1'), '"\'=1+1"');
});
