<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\FeeCollection;
use ME\SchoolManagement\Models\FeeStructure;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 4.1 — detained students stayed in the old academic year and were never billed again. */
class PromotionTest extends TestCase
{
    public function test_promoted_and_detained_students_both_move_to_the_new_year(): void
    {
        $this->actingAs($this->admin());
        [$a, $b] = Student::where('is_active', true)->whereNotNull('section_id')
            ->whereIn('class_id', Student::select('class_id')->groupBy('class_id', 'academic_year_id')->havingRaw('COUNT(*) > 1'))
            ->orderBy('id')->get()->groupBy(fn ($s) => $s->class_id . '-' . $s->academic_year_id)->first()->take(2)->all();
        $fromClass = $a->class_id; $fromYear = $a->academic_year_id; $section = $b->section_id;
        $toClass = \ME\SchoolManagement\Models\Classe::where('id', '!=', $fromClass)->value('id');
        $newYear = AcademicYear::create(['name' => 'Test ' . uniqid(), 'start_date' => '2031-01-01', 'end_date' => '2031-12-31', 'is_active' => true]);

        $this->post('/students/promotion', [
            'from_class' => $fromClass, 'from_year' => $fromYear, 'to_class' => $toClass, 'to_year' => $newYear->id,
            'student_ids' => [$a->id, $b->id],
            'mark_status' => [$a->id => 'promoted', $b->id => 'detained'],
            'roll_numbers' => [$a->id => '7', $b->id => ''],
        ])->assertSessionHas('success', fn ($m) => str_contains($m, '1 detained'));

        $a->refresh(); $b->refresh();
        $this->assertSame([$toClass, $newYear->id, '7'], [$a->class_id, $a->academic_year_id, (string) $a->roll_number]);
        // Detained: same class and section, but the new year.
        $this->assertSame([$fromClass, $section, $newYear->id], [$b->class_id, $b->section_id, $b->academic_year_id]);
        $this->assertDatabaseHas('student_promotions', ['student_id' => $b->id, 'is_promoted' => false, 'to_class_id' => $fromClass]);

        // Next year's monthly fee now reaches the detained student.
        $structure = FeeStructure::where('class_id', $fromClass)->first()
            ?? FeeStructure::create(['class_id' => $fromClass, 'academic_year_id' => $newYear->id, 'fee_type_id' => \ME\SchoolManagement\Models\FeeType::first()->id, 'amount' => 500]);
        FeeStructure::updateOrCreate(['class_id' => $fromClass, 'academic_year_id' => $newYear->id, 'fee_type_id' => $structure->fee_type_id], ['amount' => 500]);
        $this->post('/fees/generate-monthly', ['class_id' => $fromClass, 'academic_year_id' => $newYear->id,
            'fee_type_id' => $structure->fee_type_id, 'month' => 1]);
        $this->assertTrue(FeeCollection::where('student_id', $b->id)->where('academic_year_id', $newYear->id)->exists());
    }
}
