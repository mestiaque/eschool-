<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\Holiday;
use ME\SchoolManagement\Models\LeaveType;
use ME\SchoolManagement\Models\SchoolSetting;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentAttendance;
use ME\SchoolManagement\Models\StudentLeave;
use ME\SchoolManagement\Models\Teacher;
use ME\SchoolManagement\Models\TeacherAttendance;
use ME\SchoolManagement\Models\TeacherLeave;
use ME\SchoolManagement\Services\LeavePolicy;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 4.5 — leave limits, overlaps, and attendance on approved leave. */
class LeavePolicyTest extends TestCase
{
    private LeaveType $casual;
    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->admin());
        SchoolSetting::put('weekend_days', '5,6');
        $this->casual = LeaveType::create(['name' => 'Casual ' . uniqid(), 'days' => 5, 'is_active' => true]);
        $this->teacher = Teacher::first();
        TeacherLeave::where('teacher_id', $this->teacher->id)->delete();
    }

    private function apply(string $from, string $to)
    {
        return $this->post('/teachers/leaves', ['teacher_id' => $this->teacher->id, 'leave_type_id' => $this->casual->id,
            'start_date' => $from, 'end_date' => $to, 'reason' => 'Test']);
    }

    public function test_only_school_days_are_counted(): void
    {
        // Sun 5 Jan 2020 – Sat 11 Jan 2020: Fri/Sat off → 5 school days; a declared holiday removes one more.
        Holiday::create(['title' => 'Test holiday', 'date' => '2020-01-07', 'type' => 'holiday', 'is_active' => true]);
        $this->assertCount(4, \ME\SchoolManagement\Services\SchoolCalendar::schoolDaysBetween('2020-01-05', '2020-01-11'));
    }

    public function test_yearly_limit_is_enforced_at_application_and_approval(): void
    {
        $this->apply('2020-01-05', '2020-01-07')->assertSessionHas('success');           // 3 days
        $first = TeacherLeave::where('teacher_id', $this->teacher->id)->latest('id')->first();
        $this->post("/teachers/leaves/{$first->id}/approve")->assertSessionHas('success');

        $this->apply('2020-02-02', '2020-02-05')->assertSessionHas('error', fn ($m) => str_contains($m, 'only 2 of 5'));
        $this->apply('2020-02-02', '2020-02-03')->assertSessionHas('success');           // exactly the 2 left
        $this->assertSame(3, LeavePolicy::usedDays('teacher', $this->teacher->id, $this->casual->id, 2020));
    }

    public function test_overlapping_leaves_are_refused(): void
    {
        $this->apply('2020-03-01', '2020-03-03')->assertSessionHas('success');
        $this->apply('2020-03-03', '2020-03-04')->assertSessionHas('error', fn ($m) => str_contains($m, 'Overlaps'));
    }

    public function test_approval_marks_attendance_excused_and_withdrawal_removes_it(): void
    {
        TeacherAttendance::where('teacher_id', $this->teacher->id)->whereBetween('date', ['2020-04-01', '2020-04-30'])->delete();
        TeacherAttendance::create(['teacher_id' => $this->teacher->id, 'date' => '2020-04-06', 'status' => 'present']);
        $this->apply('2020-04-05', '2020-04-07');
        $leave = TeacherLeave::where('teacher_id', $this->teacher->id)->latest('id')->first();

        $this->post("/teachers/leaves/{$leave->id}/approve");
        $statuses = TeacherAttendance::where('teacher_id', $this->teacher->id)->whereBetween('date', ['2020-04-05', '2020-04-07'])
            ->orderBy('date')->pluck('status')->all();
        $this->assertSame(['excused', 'present', 'excused'], $statuses); // came in on the 6th: kept

        $this->put("/teachers/leaves/{$leave->id}", ['status' => 'rejected']);
        $this->assertSame(['present'], TeacherAttendance::where('teacher_id', $this->teacher->id)
            ->whereBetween('date', ['2020-04-05', '2020-04-07'])->pluck('status')->all());
    }

    public function test_student_leave_approval_marks_attendance(): void
    {
        $student = Student::first();
        StudentLeave::where('student_id', $student->id)->delete();
        StudentAttendance::where('student_id', $student->id)->whereBetween('date', ['2020-05-03', '2020-05-04'])->delete();
        $this->post('/students/leaves', ['student_id' => $student->id, 'leave_type_id' => $this->casual->id,
            'start_date' => '2020-05-03', 'end_date' => '2020-05-04', 'reason' => 'Ill'])->assertSessionHas('success');
        $leave = StudentLeave::where('student_id', $student->id)->latest('id')->first();
        $this->post("/students/leaves/{$leave->id}/approve")->assertSessionHas('success');
        $this->assertSame(2, StudentAttendance::where('student_id', $student->id)->where('status', 'excused')
            ->whereBetween('date', ['2020-05-03', '2020-05-04'])->count());
    }

    public function test_teacher_in_and_out_times_are_saved(): void
    {
        TeacherAttendance::where('teacher_id', $this->teacher->id)->whereDate('date', '2020-06-01')->delete();
        $this->post('/teachers/attendance', ['date' => '2020-06-01', 'teacher_ids' => [$this->teacher->id],
            'status' => [$this->teacher->id => 'present'], 'in_time' => [$this->teacher->id => '08:05'], 'out_time' => [$this->teacher->id => '14:10']]);
        $row = TeacherAttendance::where('teacher_id', $this->teacher->id)->whereDate('date', '2020-06-01')->first();
        $this->assertSame(['08:05:00', '14:10:00'], [$row->in_time, $row->out_time]);
    }
}
