<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\ClassRoutine;
use ME\SchoolManagement\Models\Section;
use ME\SchoolManagement\Services\RoutineGrid;
use ME\SchoolManagement\Tests\TestCase;

/** Routine print: one timetable per title → class (→ section), time across, days down. */
class RoutinePrintTest extends TestCase
{
    public function test_routine_list_prints_one_timetable_per_class(): void
    {
        $this->actingAs($this->admin());
        ClassRoutine::whereNotNull('section_id')->update(['section_id' => null]);
        $classes = ClassRoutine::distinct()->count('class_id');

        $html = $this->get('/academic/routines/print')->assertOk()->getContent();

        $this->assertSame($classes, substr_count($html, 'class="routine-chart"'));
        $this->assertSame($classes, substr_count($html, 'class="rc-table"'));
        $this->assertStringNotContainsString('Start Time', $html); // old flat list is gone
    }

    public function test_title_filter_and_section_split(): void
    {
        $this->actingAs($this->admin());
        $class = ClassRoutine::value('class_id');
        $sections = Section::where('class_id', $class)->take(2)->pluck('id');
        $this->assertCount(2, $sections);
        ClassRoutine::query()->update(['title' => 'Old', 'section_id' => null]);
        $rows = ClassRoutine::where('class_id', $class)->take(2)->get();
        $rows[0]->update(['title' => 'Summer', 'section_id' => $sections[0]]);
        $rows[1]->update(['title' => 'Summer', 'section_id' => $sections[1]]);

        $html = $this->get('/academic/routines/print?title=Summer')->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'class="routine-chart"'));
        $this->assertStringContainsString('Summer', $html);
    }

    public function test_class_print_shows_teacher_and_room(): void
    {
        $this->actingAs($this->admin());
        $routine = ClassRoutine::whereNotNull('teacher_id')->whereNotNull('room_id')->with(['teacher', 'room'])->first()
            ?? tap(ClassRoutine::first())->update(['teacher_id' => \ME\SchoolManagement\Models\Teacher::value('id')]);
        $routine->load(['teacher', 'room']);
        ClassRoutine::where('class_id', $routine->class_id)->update(['title' => $routine->title]);

        $html = $this->get("/academic/classes/{$routine->class_id}/routine/print")->assertOk()->getContent();

        $this->assertStringContainsString(e($routine->teacher->full_name), $html);
        if ($routine->room) {
            $this->assertStringContainsString(e($routine->room->name), $html);
        }
    }

    public function test_slots_mark_gaps_as_breaks_and_days_follow_school_week(): void
    {
        $make = fn ($day, $s, $e) => new ClassRoutine(['day' => $day, 'start_time' => $s, 'end_time' => $e]);
        $routines = collect([
            $make('Sunday', '08:00:00', '08:45:00'),
            $make('Monday', '08:00:00', '08:45:00'),
            $make('Sunday', '09:15:00', '10:00:00'),
        ]);

        $slots = RoutineGrid::slots($routines);
        $this->assertSame(['period', 'break', 'period'], array_column($slots, 'type'));
        $this->assertSame(2, $slots[2]['number']);

        $days = RoutineGrid::days($routines);
        $this->assertSame(ClassRoutine::WEEK, array_keys($days));
        $this->assertSame('Sunday', array_key_first(RoutineGrid::days($routines, 'Sunday')));
        $this->assertCount(1, RoutineGrid::days($routines, 'Sunday'));
    }
}
