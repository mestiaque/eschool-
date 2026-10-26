<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\Holiday;
use ME\SchoolManagement\Models\SchoolSetting;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentAttendance;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 3.1 / 3.2 — configurable weekly holidays, no attendance on closed days. */
class SchoolCalendarTest extends TestCase
{
    private const FRIDAY = '2020-01-03';
    private const SATURDAY = '2020-01-04';
    private const SUNDAY = '2020-01-05';

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->admin());
        SchoolSetting::put('weekend_days', '5,6'); // Friday + Saturday
    }

    private function cell(string $date)
    {
        return $this->postJson('/students/attendance/cell', ['student_id' => Student::first()->id, 'date' => $date, 'status' => 'present']);
    }

    public function test_weekly_holiday_blocks_attendance(): void
    {
        $this->cell(self::FRIDAY)->assertStatus(422)->assertJsonPath('success', false);
        $this->assertDatabaseMissing('student_attendances', ['student_id' => Student::first()->id, 'date' => self::FRIDAY]);
        $this->cell(self::SUNDAY)->assertOk();
    }

    public function test_weekly_holidays_come_from_settings(): void
    {
        $this->post('/settings/general', ['currency' => 'BDT', 'timezone' => 'Asia/Dhaka', 'date_format' => 'd M Y', 'weekend_days' => [5]])
            ->assertSessionHas('success');
        $this->assertSame('5', SchoolSetting::get('weekend_days'));
        $this->cell(self::SATURDAY)->assertOk();
        $this->cell(self::FRIDAY)->assertStatus(422);
    }

    public function test_declared_holiday_blocks_and_extra_school_day_opens(): void
    {
        Holiday::create(['title' => 'Victory Day', 'date' => self::SUNDAY, 'type' => 'holiday', 'is_active' => true]);
        $this->cell(self::SUNDAY)->assertStatus(422);

        Holiday::create(['title' => 'Make-up class', 'date' => self::SATURDAY, 'type' => 'school_day', 'is_active' => true]);
        $this->cell(self::SATURDAY)->assertOk();
    }

    public function test_bulk_attendance_on_a_closed_day_is_refused(): void
    {
        $student = Student::first();
        $this->post('/students/attendance', ['date' => self::FRIDAY, 'class_id' => $student->class_id,
            'student_ids' => [$student->id]])->assertSessionHas('error');
        $this->assertSame(0, StudentAttendance::where('student_id', $student->id)->whereDate('date', self::FRIDAY)->count());
    }

    public function test_holiday_form_accepts_an_extra_school_day(): void
    {
        $this->post('/holidays', ['title' => 'Make-up', 'date' => '2020-02-01', 'type' => 'school_day'])->assertSessionHas('success');
        $this->assertDatabaseHas('holidays', ['date' => '2020-02-01', 'type' => 'school_day']);
        $this->post('/holidays', ['title' => 'Dup', 'date' => '2020-02-01'])->assertSessionHasErrors('date');
    }

    public function test_future_dates_are_refused(): void
    {
        $this->cell(now()->addDays(3)->toDateString())->assertStatus(422);
    }

    public function test_grid_shows_entries_recorded_on_a_closed_day(): void
    {
        $student = Student::first();
        StudentAttendance::create(['student_id' => $student->id, 'date' => self::FRIDAY, 'status' => 'absent', 'class_id' => $student->class_id]);
        $html = $this->get("/students/attendance?class_id={$student->class_id}&month=1&year=2020")->assertOk()->getContent();
        $this->assertStringContainsString('absent locked', $html);
    }
}
