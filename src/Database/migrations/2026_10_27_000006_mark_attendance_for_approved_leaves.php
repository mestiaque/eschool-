<?php

use Illuminate\Database\Migrations\Migration;
use ME\SchoolManagement\Models\StudentLeave;
use ME\SchoolManagement\Models\TeacherLeave;
use ME\SchoolManagement\Services\LeavePolicy;

return new class extends Migration
{
    // Leaves approved before approval started marking attendance.
    public function up(): void
    {
        TeacherLeave::where('status', 'approved')->get()->each(fn ($l) => LeavePolicy::markAttendance('teacher', $l));
        StudentLeave::where('status', 'approved')->get()->each(fn ($l) => LeavePolicy::markAttendance('student', $l));
    }

    public function down(): void
    {
        TeacherLeave::where('status', 'approved')->get()->each(fn ($l) => LeavePolicy::unmarkAttendance('teacher', $l));
        StudentLeave::where('status', 'approved')->get()->each(fn ($l) => LeavePolicy::unmarkAttendance('student', $l));
    }
};
