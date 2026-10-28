<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\ClassRoutine;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 2.2 / 2.3 / 5.5 */
class DisplayOrderTest extends TestCase
{
    public function test_routines_follow_the_school_week_and_show_readable_times(): void
    {
        $this->actingAs($this->admin());
        $days = ClassRoutine::orderByWeekday()->pluck('day')->unique()->values()->all();
        $expected = array_values(array_intersect(ClassRoutine::WEEK, $days));
        $this->assertSame($expected, $days);

        $html = $this->get('/academic/routines')->assertOk()->getContent();
        $first = ClassRoutine::orderByWeekday()->orderBy('start_time')->first();
        $this->assertStringContainsString(sm_time($first->start_time), $html);
        $this->assertSame('08:00 AM', sm_time('08:00:00'));
        $this->assertSame('—', sm_time(null));
    }
}
