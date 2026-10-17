<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\FeeType;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Tests\TestCase;

class DeleteProtectionTest extends TestCase
{
    public function test_a_class_with_students_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin());
        $class = Classe::whereIn('id', \DB::table('students')->select('class_id'))->first();
        $before = Student::where('class_id', $class->id)->count();
        $this->assertGreaterThan(0, $before);

        $this->from('/academic/classes')->delete("/academic/classes/{$class->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('classes', ['id' => $class->id]);
        $this->assertSame($before, Student::where('class_id', $class->id)->count());
    }

    public function test_used_academic_year_and_fee_type_are_protected(): void
    {
        $this->actingAs($this->admin());
        $year = AcademicYear::whereIn('id', \DB::table('fee_collections')->select('academic_year_id'))->first();
        if ($year) {
            $year->update(['is_current' => false]);
            $this->delete("/academic/years/{$year->id}")->assertSessionHas('error');
            $this->assertDatabaseHas('academic_years', ['id' => $year->id]);
        }
        $type = FeeType::first();
        $this->delete("/fees/types/{$type->id}")->assertSessionHas('error');
        $this->assertDatabaseHas('fee_types', ['id' => $type->id]);
    }

    public function test_deleting_a_student_moves_it_to_the_recycle_bin_and_restore_works(): void
    {
        $this->actingAs($this->admin());
        $student = Student::has('feeCollections')->first();
        $invoices = $student->feeCollections()->count();

        $this->delete("/students/{$student->id}")->assertRedirect();
        $this->assertSoftDeleted('students', ['id' => $student->id]);
        $this->assertSame($invoices, \DB::table('fee_collections')->where('student_id', $student->id)->count(), 'history kept');

        $this->post("/trash/students/{$student->id}/restore")->assertSessionHas('success');
        $this->assertNotSoftDeleted('students', ['id' => $student->id]);
    }

    public function test_permanent_delete_needs_the_typed_code(): void
    {
        $this->actingAs($this->admin());
        $student = Student::first();
        $student->delete();

        $this->delete("/trash/students/{$student->id}", ['confirm_code' => 'wrong'])->assertSessionHas('error');
        $this->assertSoftDeleted('students', ['id' => $student->id]);

        $this->delete("/trash/students/{$student->id}", ['confirm_code' => $student->student_id])->assertSessionHas('success');
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }
}
