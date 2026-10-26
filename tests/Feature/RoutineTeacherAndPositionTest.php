<?php

namespace ME\SchoolManagement\Tests\Feature;

use Illuminate\Support\Facades\DB;
use ME\SchoolManagement\Models\ClassRoutine;
use ME\SchoolManagement\Models\Exam;
use ME\SchoolManagement\Models\Result;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\Subject;
use ME\SchoolManagement\Models\Teacher;
use ME\SchoolManagement\Models\TeacherSubject;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 4.8 / 3.5 */
class RoutineTeacherAndPositionTest extends TestCase
{
    public function test_assigning_a_subject_fills_routine_periods_without_a_teacher(): void
    {
        $this->actingAs($this->admin());
        $subject = Subject::whereIn('id', ClassRoutine::select('subject_id'))->first();
        $periods = ClassRoutine::where('class_id', $subject->class_id)->where('subject_id', $subject->id)->get();
        ClassRoutine::whereIn('id', $periods->pluck('id'))->update(['teacher_id' => null]);
        $teacher = Teacher::whereNotIn('id', ClassRoutine::whereNotNull('teacher_id')->select('teacher_id'))->first() ?? Teacher::first();
        ClassRoutine::where('teacher_id', $teacher->id)->update(['teacher_id' => null]); // no clashes
        TeacherSubject::where(['teacher_id' => $teacher->id, 'subject_id' => $subject->id])->delete();

        $this->post('/teachers/subjects', ['teacher_id' => $teacher->id, 'subject_id' => $subject->id])
            ->assertSessionHas('success', fn ($m) => str_contains($m, (string) $periods->count()));
        $this->assertSame($periods->count(), ClassRoutine::whereIn('id', $periods->pluck('id'))->where('teacher_id', $teacher->id)->count());
    }

    public function test_routine_page_warns_and_filters_periods_without_teacher(): void
    {
        $this->actingAs($this->admin());
        ClassRoutine::query()->limit(3)->update(['teacher_id' => null]);
        $html = $this->get('/academic/routines?teacher_id=none')->assertOk()->assertViewHas('withoutTeacher', fn ($n) => $n >= 3)->getContent();
        $this->assertStringContainsString('teacher_id=none', $html);
    }

    public function test_results_rank_within_class_and_section(): void
    {
        $this->actingAs($this->admin());
        $exam = Exam::first();
        $class = Student::whereNotNull('section_id')->select('class_id')->groupBy('class_id')
            ->havingRaw('COUNT(DISTINCT section_id) > 1')->value('class_id');
        $students = Student::where('class_id', $class)->whereNotNull('section_id')->get()->groupBy('section_id')->map->take(2)->flatten()->take(4);
        Result::where('exam_id', $exam->id)->where('class_id', $class)->delete();
        DB::table('student_marks')->where('exam_id', $exam->id)->where('class_id', $class)->delete();
        $subject = Subject::where('class_id', $class)->first();
        foreach ($students->values() as $i => $st) {
            DB::table('student_marks')->insert(['student_id' => $st->id, 'exam_id' => $exam->id, 'subject_id' => $subject->id,
                'class_id' => $class, 'total_mark' => 90 - $i * 10, 'grade_point' => 5 - $i, 'is_absent' => false]);
        }
        $this->post('/exams/results/generate', ['exam_id' => $exam->id, 'class_id' => $class]);

        $rows = Result::where('exam_id', $exam->id)->where('class_id', $class)->get();
        $this->assertSame($rows->count(), $rows->whereNotNull('position')->count());
        foreach ($rows->groupBy('section_id') as $group) {
            $this->assertSame(range(1, $group->count()), $group->sortBy('section_position')->pluck('section_position')->values()->all());
        }
    }
}
