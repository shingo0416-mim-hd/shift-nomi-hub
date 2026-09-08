<?php

namespace Tests\Unit;

use App\Services\ShiftAnalytics;
use PHPUnit\Framework\TestCase;

class ShiftAnalyticsTest extends TestCase
{
    public function test_shortage_and_excess_do_not_cancel_across_time_intervals(): void
    {
        $result = (new ShiftAnalytics)->measure(0, 7200, 1, [
            ['start' => 0, 'end' => 3600, 'members' => [1, 2]],
        ]);
        $this->assertEquals(['required' => 2, 'assigned' => 2, 'shortage' => 1, 'excess' => 1], $result);
    }

    public function test_overlapping_assignments_of_one_member_are_counted_once(): void
    {
        $result = (new ShiftAnalytics)->measure(0, 7200, 1, [
            ['start' => 0, 'end' => 7200, 'members' => [1]],
            ['start' => 3600, 'end' => 7200, 'members' => [1]],
        ]);
        $this->assertEquals(2, $result['assigned']);
        $this->assertEquals(0, $result['excess']);
    }

    public function test_overnight_and_out_of_plan_hours_are_measured(): void
    {
        $result = (new ShiftAnalytics)->measure(22 * 3600, 26 * 3600, 2, [
            ['start' => 21 * 3600, 'end' => 26 * 3600, 'members' => [1, 2]],
        ]);
        $this->assertEquals(['required' => 8, 'assigned' => 10, 'shortage' => 0, 'excess' => 2], $result);
    }
}
