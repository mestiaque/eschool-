<?php

namespace ME\SchoolManagement\Tests\Feature;

use Illuminate\Support\Facades\DB;
use ME\SchoolManagement\Models\Exam;
use ME\SchoolManagement\Models\ExamSchedule;
use ME\SchoolManagement\Models\Result;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentMark;
use ME\SchoolManagement\Models\Subject;
use ME\SchoolManagement\Tests\TestCase;

class ExamGradingTest extends TestCase
{
    private function setUpExam(int $fullMark = 50): array
    {
        DB::table('grades')->delete(); // use the default Bangladesh scale
        $student = Student::whereNotNull('class_id')->first();
        $subjects = Subject::where('class_id', $student->class_id)->take(2)->get();
        $exam = Exam::first();
        StudentMark::where('exam_id', $exam->id)->where('class_id', $student->class_id)->delete();
        Result::where('exam_id', $exam->id)->delete();
        foreach ($subjects as $sub) {
            ExamSchedule::updateOrCreate(['exam_id' => $exam->id, 'class_id' => $student->class_id, 'subject_id' => $sub->id],
                ['date' => now(), 'start_time' => '09:00', 'end_time' => '10:00', 'full_mark' => $fullMark, 'pass_mark' => round($fullMark * .33)]);
        }
        return [$student, $subjects, $exam];
    }

    private function enter($exam, $student, $subject, $written, array $extra = [])
    {
        return $this->post('/exams/marks', array_merge([
            'exam_id' => $exam->id, 'class_id' => $student->class_id, 'subject_id' => $subject->id,
            'student_ids' => [$student->id], 'written' => [$student->id => $written],
        ], $extra));
    }

    public function test_grade_uses_percentage_of_full_mark(): void
    {
        $this->actingAs($this->admin());
        [$student, $subjects, $exam] = $this->setUpExam(50);
        $this->enter($exam, $student, $subjects[0], 45); // 90% → A+

        $mark = StudentMark::where(['exam_id' => $exam->id, 'student_id' => $student->id, 'subject_id' => $subjects[0]->id])->first();
        $this->assertSame('A+', $mark->grade_name);
        $this->assertEquals(5.0, (float) $mark->grade_point);
    }

    public function test_marks_above_full_mark_are_rejected(): void
    {
        $this->actingAs($this->admin());
        [$student, $subjects, $exam] = $this->setUpExam(50);
        $this->enter($exam, $student, $subjects[0], 60)->assertSessionHas('error');
        $this->assertDatabaseMissing('student_marks', ['exam_id' => $exam->id, 'student_id' => $student->id, 'subject_id' => $subjects[0]->id]);
    }

    public function test_one_failed_subject_fails_the_result(): void
    {
        $this->actingAs($this->admin());
        [$student, $subjects, $exam] = $this->setUpExam(50);
        $this->enter($exam, $student, $subjects[0], 48);
        $this->enter($exam, $student, $subjects[1], 5); // 10% → F

        $this->post('/exams/results/generate', ['exam_id' => $exam->id, 'class_id' => $student->class_id]);
        $result = Result::where(['exam_id' => $exam->id, 'student_id' => $student->id])->first();
        $this->assertEquals(0, (float) $result->gpa);
        $this->assertSame('F', $result->grade);
    }

    public function test_absent_subject_fails_the_result(): void
    {
        $this->actingAs($this->admin());
        [$student, $subjects, $exam] = $this->setUpExam(50);
        $this->enter($exam, $student, $subjects[0], 48);
        $this->enter($exam, $student, $subjects[1], 0, ['is_absent' => [$student->id => 1]]);

        $this->post('/exams/results/generate', ['exam_id' => $exam->id, 'class_id' => $student->class_id]);
        $this->assertSame('F', Result::where(['exam_id' => $exam->id, 'student_id' => $student->id])->value('grade'));
    }
}
