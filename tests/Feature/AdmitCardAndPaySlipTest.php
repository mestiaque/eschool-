<?php

namespace ME\SchoolManagement\Tests\Feature;

use ME\SchoolManagement\Models\Exam;
use ME\SchoolManagement\Models\ExamSchedule;
use ME\SchoolManagement\Models\SalaryPayment;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\Teacher;
use ME\SchoolManagement\Tests\TestCase;

/** Admit cards and salary pay slips. */
class AdmitCardAndPaySlipTest extends TestCase
{
    public function test_admit_cards_print_one_card_per_student_with_the_exam_routine(): void
    {
        $this->actingAs($this->admin());
        $exam = Exam::first();
        $classId = Student::where('is_active', true)->value('class_id');
        $students = Student::where('class_id', $classId)->where('is_active', true)->count();

        $res = $this->get(route('exams.admit-cards', ['exam_id' => $exam->id, 'class_id' => $classId]))->assertOk();
        $this->assertSame($students, substr_count($res->getContent(), 'class="ac-card"'));

        $one = Student::where('class_id', $classId)->where('is_active', true)->first();
        $html = $this->get(route('exams.admit-cards', ['exam_id' => $exam->id, 'class_id' => $classId, 'student_id' => $one->id]))->getContent();
        $this->assertSame(1, substr_count($html, 'class="ac-card"'));
        if ($sc = ExamSchedule::with('subject')->where('exam_id', $exam->id)->where('class_id', $classId)->first()) {
            $this->assertStringContainsString(e($sc->subject->name), $html);
        }
    }

    public function test_pay_slip_shows_net_salary_in_words(): void
    {
        $this->actingAs($this->admin());
        $teacher = Teacher::first();
        $p = SalaryPayment::create(['staff_id' => $teacher->id, 'staff_type' => 'teacher', 'basic_salary' => 20000, 'allowance' => 2500,
            'deduction' => 500, 'gross_salary' => 22500, 'net_salary' => 22000, 'month' => 2, 'year' => 2020, 'payment_date' => '2020-02-28']);

        $html = $this->get(route('teachers.salary.slip', $p->id))->assertOk()->getContent();
        $this->assertStringContainsString(e(sm_amount_words(22000)), $html);
        $this->assertStringContainsString('PS-' . str_pad($p->id, 6, '0', STR_PAD_LEFT), $html);
        $this->get(route('expenses.staff-salary.slip', $p->id))->assertOk();

        // A staff payment can't be opened through the Teachers → Salary route.
        $p->update(['staff_type' => 'staff']);
        $this->get(route('teachers.salary.slip', $p->id))->assertNotFound();
    }
}
