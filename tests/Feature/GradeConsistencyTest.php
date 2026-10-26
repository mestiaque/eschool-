<?php

namespace ME\SchoolManagement\Tests\Feature;

use Illuminate\Support\Facades\DB;
use ME\SchoolManagement\Models\ClassTest;
use ME\SchoolManagement\Models\ClassTestResult;
use ME\SchoolManagement\Models\Grade;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentAttendance;
use ME\SchoolManagement\Services\GradeScale;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 3.3 / 3.4 — one grade scale everywhere; late counts as attended. */
class GradeConsistencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::table('grades')->delete();
        GradeScale::flush();
    }

    public function test_pass_mark_and_letter_never_disagree(): void
    {
        $this->assertSame('D', GradeScale::letter(35, 100, 33));   // was "pass" + "F"
        $this->assertSame('F', GradeScale::letter(30, 100, 33));
        $this->assertSame('F', GradeScale::letter(38, 100, 40));   // below this test's own pass mark
        $this->assertSame('A-', GradeScale::letter(13, 20));
    }

    public function test_class_test_marks_use_the_shared_scale(): void
    {
        $this->actingAs($this->admin());
        $test = ClassTest::first();
        $test->update(['total_marks' => 100, 'pass_marks' => 33]);
        $student = Student::where('class_id', $test->class_id)->first();

        $this->post("/academic/class-tests/{$test->id}/marks", ['status' => [$student->id => 'present'], 'marks' => [$student->id => 35]]);
        $row = ClassTestResult::where(['class_test_id' => $test->id, 'student_id' => $student->id])->first();
        $this->assertSame(['pass', 'D'], [$row->status, $row->grade]);
    }

    public function test_configured_grades_apply_to_class_tests_too(): void
    {
        Grade::create(['name' => 'Excellent', 'grade_point' => 5, 'mark_from' => 90, 'mark_to' => 100, 'is_active' => true]);
        Grade::create(['name' => 'Fail', 'grade_point' => 0, 'mark_from' => 0, 'mark_to' => 89.99, 'is_active' => true]);
        GradeScale::flush();
        $this->assertSame('Excellent', GradeScale::letter(95, 100));
    }

    public function test_portal_attendance_counts_late_as_attended(): void
    {
        $student = Student::where('is_active', true)->first();
        StudentAttendance::where('student_id', $student->id)->delete();
        foreach (['2020-02-03' => 'present', '2020-02-04' => 'late', '2020-02-05' => 'absent', '2020-02-06' => 'late'] as $d => $st) {
            StudentAttendance::create(['student_id' => $student->id, 'date' => $d, 'status' => $st, 'class_id' => $student->class_id]);
        }
        $this->withSession(['portal_student_id' => $student->id])->get('/student-portal/attendance?year=2020')
            ->assertOk()->assertViewHas('attendancePct', 75.0);
    }
}
