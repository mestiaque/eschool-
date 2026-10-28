<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\Models\User;
use ME\SchoolManagement\Models\LeaveType;
use ME\SchoolManagement\Models\SchoolSetting;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentAttendance;
use ME\SchoolManagement\Models\StudentLeave;
use ME\SchoolManagement\Models\Teacher;
use ME\SchoolManagement\Models\TeacherLeave;
use ME\SchoolManagement\Tests\TestCase;

/** Leave types per person kind, the teacher's own leave page, and withdrawing approvals. */
class LeaveTypesAndSelfServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SchoolSetting::put('weekend_days', '5,6');
    }

    public function test_leave_types_can_be_managed_and_are_limited_to_teachers_or_students(): void
    {
        $this->actingAs($this->admin());
        $this->get('/leave-types')->assertOk();
        $this->post('/leave-types', ['name' => 'Exam prep', 'days' => 3, 'applies_to' => 'student'])->assertSessionHas('success');
        $type = LeaveType::where('name', 'Exam prep')->firstOrFail();

        $this->get('/students/leaves')->assertViewHas('leaveTypes', fn ($t) => $t->contains('id', $type->id));
        $this->get('/teachers/leaves')->assertViewHas('leaveTypes', fn ($t) => !$t->contains('id', $type->id));

        // A teacher can't be given a student-only type, even by a crafted request.
        $teacher = Teacher::first();
        TeacherLeave::where('teacher_id', $teacher->id)->delete();
        $this->post('/teachers/leaves', ['teacher_id' => $teacher->id, 'leave_type_id' => $type->id,
            'start_date' => '2020-02-02', 'end_date' => '2020-02-02', 'reason' => 'x'])->assertSessionHas('error');

        $this->put("/leave-types/{$type->id}", ['name' => 'Exam prep', 'days' => 5, 'applies_to' => 'both'])->assertSessionHas('success');
        $this->assertSame('both', $type->fresh()->applies_to);
        $this->assertFalse($type->fresh()->is_active); // unchecked box deactivates

        $this->delete("/leave-types/{$type->id}")->assertSessionHas('success');
    }

    public function test_days_column_counts_school_days(): void
    {
        $leave = new TeacherLeave(['start_date' => '2020-01-05', 'end_date' => '2020-01-11']); // Sun–Sat, Fri/Sat off
        $this->assertSame(5, $leave->days);
    }

    public function test_teacher_applies_sees_balance_and_cancels_own_leave(): void
    {
        $teacher = Teacher::first();
        TeacherLeave::where('teacher_id', $teacher->id)->delete();
        $user = User::create(['name' => 'T', 'email' => uniqid('t') . '@test.local', 'password' => bcrypt('x'), 'is_active' => 1]);
        $user->forceFill(['teacher_id' => $teacher->id])->save();
        $type = LeaveType::create(['name' => 'Casual ' . uniqid(), 'days' => 10, 'applies_to' => 'teacher', 'is_active' => true]);
        $this->actingAs($user->fresh());

        $start = now()->next('Sunday');
        $this->post('/my/leaves', ['leave_type_id' => $type->id, 'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addDay()->toDateString(), 'reason' => 'Family'])->assertSessionHas('success');
        $leave = TeacherLeave::where('teacher_id', $teacher->id)->firstOrFail();
        $this->assertSame('pending', $leave->status);

        $this->get('/my/leaves')->assertOk()->assertViewHas('balances', fn ($b) => collect($b)->firstWhere('type.id', $type->id)['left'] === 10);

        $this->delete("/my/leaves/{$leave->id}")->assertSessionHas('success');
        $this->assertNull(TeacherLeave::find($leave->id));
    }

    public function test_withdrawing_a_student_approval_removes_excused_marks(): void
    {
        $this->actingAs($this->admin());
        $student = Student::first();
        StudentLeave::where('student_id', $student->id)->delete();
        StudentAttendance::where('student_id', $student->id)->whereBetween('date', ['2020-03-01', '2020-03-31'])->delete();
        $type = LeaveType::create(['name' => 'Sick ' . uniqid(), 'days' => 0, 'applies_to' => 'both', 'is_active' => true]);

        $this->post('/students/leaves', ['student_id' => $student->id, 'leave_type_id' => $type->id,
            'start_date' => '2020-03-01', 'end_date' => '2020-03-02', 'reason' => 'Fever'])->assertSessionHas('success');
        $leave = StudentLeave::where('student_id', $student->id)->latest('id')->firstOrFail();
        $this->post("/students/leaves/{$leave->id}/approve")->assertSessionHas('success');
        $this->assertSame(2, StudentAttendance::where('student_id', $student->id)->where('status', 'excused')->whereBetween('date', ['2020-03-01', '2020-03-02'])->count());

        $this->post("/students/leaves/{$leave->id}/revoke")->assertSessionHas('success');
        $this->assertSame(0, StudentAttendance::where('student_id', $student->id)->where('status', 'excused')->whereBetween('date', ['2020-03-01', '2020-03-02'])->count());
    }
}
