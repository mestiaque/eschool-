<?php

namespace ME\SchoolManagement\Tests\Feature;

use Illuminate\Support\Facades\DB;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\Exam;
use ME\SchoolManagement\Models\Grade;
use ME\SchoolManagement\Models\Result;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentMark;
use ME\SchoolManagement\Models\Subject;
use ME\SchoolManagement\Models\SubjectGroup;
use ME\SchoolManagement\Services\GradeScale;
use ME\SchoolManagement\Tests\TestCase;

/** Grade setup, result generation, marksheets and the class result sheet. */
class ResultSheetTest extends TestCase
{
    private Classe $class;
    private Exam $exam;
    private array $subjects = [];

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('grades')->delete();
        GradeScale::flush();
        $this->actingAs($this->admin());
        $this->class = Classe::create(['name' => 'Sheet Nine', 'numeric_name' => random_int(100, 999), 'is_active' => true]);
        $science = SubjectGroup::firstOrCreate(['name' => 'Science'], ['is_active' => true]);
        $commerce = SubjectGroup::firstOrCreate(['name' => 'Commerce'], ['is_active' => true]);
        $mk = fn ($name, $type, $group = null) => Subject::create(['name' => $name, 'class_id' => $this->class->id, 'type' => $type,
            'group_id' => $group, 'full_mark' => 100, 'pass_mark' => 33, 'is_active' => true]);
        $this->subjects = [
            'bangla'     => $mk('Bangla', 'compulsory'),
            'physics'    => $mk('Physics', 'group', $science->id),
            'accounting' => $mk('Accounting', 'group', $commerce->id),
        ];
        $this->exam = Exam::first();
    }

    protected function tearDown(): void
    {
        GradeScale::flush();
        parent::tearDown();
    }

    private function student(string $name, int $roll): Student
    {
        $base = Student::first();
        return Student::create([
            'student_id' => uniqid('R'), 'first_name' => $name, 'last_name' => 'Test', 'gender' => 'male', 'date_of_birth' => '2010-01-01',
            'class_id' => $this->class->id, 'academic_year_id' => $base->academic_year_id, 'is_active' => true, 'roll_number' => $roll,
            'subject_group_id' => $this->subjects['physics']->group_id,
        ]);
    }

    private function enter(Student $st, array $bySubject): void
    {
        foreach ($bySubject as $key => $written) {
            $this->post('/exams/marks', ['exam_id' => $this->exam->id, 'class_id' => $this->class->id,
                'subject_id' => $this->subjects[$key]->id, 'student_ids' => [$st->id], 'written' => [$st->id => $written]]);
        }
    }

    private function generate(): void
    {
        $this->post('/exams/results/generate', ['exam_id' => $this->exam->id, 'class_id' => $this->class->id])->assertSessionHas('success');
    }

    public function test_results_page_buttons_are_not_inside_the_filter_form(): void
    {
        $html = $this->get('/exams/results?exam_id=' . $this->exam->id . '&class_id=' . $this->class->id)->assertOk()->getContent();
        $depth = 0;
        preg_match_all('#<form\b|</form>#', $html, $m);
        foreach ($m[0] as $tag) {
            $depth += $tag === '</form>' ? -1 : 1;
            $this->assertLessThanOrEqual(1, $depth, 'nested <form> on the results page');
        }
        $this->assertStringContainsString(e(route('exams.results.print', ['exam_id' => $this->exam->id, 'class_id' => $this->class->id])), $html);
    }

    public function test_total_mark_counts_only_the_students_own_subjects(): void
    {
        $st = $this->student('Group', 1);
        $this->enter($st, ['bangla' => 80, 'physics' => 70]);
        $this->generate();

        $r = Result::where(['exam_id' => $this->exam->id, 'student_id' => $st->id])->firstOrFail();
        $this->assertEquals(200, (float) $r->total_mark); // not 300: Accounting is a Commerce subject
        $this->assertEquals(150, (float) $r->obtained_mark);
    }

    public function test_scale_without_a_zero_row_still_fails_low_marks(): void
    {
        foreach ([['A+', 5, 80, 100], ['A', 4, 70, 79], ['A-', 3.5, 60, 69], ['B', 3, 50, 59], ['C', 2, 40, 49], ['D', 1, 33, 39]] as [$n, $p, $f, $t]) {
            Grade::create(['name' => $n, 'grade_point' => $p, 'mark_from' => $f, 'mark_to' => $t, 'is_active' => true]);
        }
        GradeScale::flush();

        $this->assertSame('F', GradeScale::forPercent(20)['name']);
        $this->assertSame(0.0, GradeScale::fail()['point']);
        $this->assertNotEmpty(GradeScale::problems());
    }

    public function test_changing_the_grade_scale_applies_on_regenerate_and_keeps_publish_state(): void
    {
        $st = $this->student('Scale', 1);
        $this->enter($st, ['bangla' => 75, 'physics' => 75]);
        $this->generate();
        $this->post('/exams/results/publish', ['exam_id' => $this->exam->id, 'class_id' => $this->class->id]);
        $this->assertSame('A', Result::where(['exam_id' => $this->exam->id, 'student_id' => $st->id])->value('grade'));

        // School moves A+ down to 75%.
        foreach ([['A+', 5, 75, 100], ['A', 4, 70, 74.99], ['F', 0, 0, 69.99]] as [$n, $p, $f, $t]) {
            $this->post('/exams/grades', ['name' => $n, 'grade_point' => $p, 'mark_from' => $f, 'mark_to' => $t])->assertSessionHasNoErrors();
        }
        $this->generate();

        $r = Result::where(['exam_id' => $this->exam->id, 'student_id' => $st->id])->firstOrFail();
        $this->assertSame('A+', $r->grade);
        $this->assertTrue((bool) $r->is_published);
        $this->assertSame('A+', StudentMark::where(['exam_id' => $this->exam->id, 'student_id' => $st->id])->first()->grade_name);
    }

    public function test_marksheet_lists_missing_subjects_and_result_sheet_summarises_class(): void
    {
        $a = $this->student('Alpha', 1);
        $b = $this->student('Beta', 2);
        $this->enter($a, ['bangla' => 85, 'physics' => 90]);
        $this->enter($b, ['bangla' => 20]);
        $this->generate();

        $html = $this->get(route('exams.marksheet.view', [$b->id, $this->exam->id]))->assertOk()->getContent();
        $this->assertStringContainsString('Physics', $html); // no mark, still listed
        $this->assertStringContainsString('ms-page', $html);

        $sheet = $this->get(route('exams.results.print', ['exam_id' => $this->exam->id, 'class_id' => $this->class->id]))->assertOk();
        $sheet->assertViewHas('sheet', fn ($s) => $s['summary']['appeared'] === 2 && $s['summary']['passed'] === 1);
        $this->assertStringContainsString('Alpha', $sheet->getContent());

        $all = $this->get(route('exams.marksheet.class', ['exam_id' => $this->exam->id, 'class_id' => $this->class->id]))->assertOk()->getContent();
        $this->assertSame(2, substr_count($all, 'class="ms-page"'));
    }

    public function test_failing_mcq_alone_fails_the_subject_when_parts_have_pass_marks(): void
    {
        $base = ['exam_id' => $this->exam->id, 'class_id' => $this->class->id, 'subject_id' => $this->subjects['bangla']->id,
            'date' => '2026-03-01', 'start_time' => '10:00', 'end_time' => '13:00', 'full_mark' => 100, 'pass_mark' => 33];
        // MCQ pass mark above its full mark is refused.
        $this->post('/exams/schedule', $base + ['written_full' => 70, 'written_pass' => 23, 'mcq_full' => 30, 'mcq_pass' => 40])
            ->assertSessionHasErrors('mcq_pass');
        $this->post('/exams/schedule', $base + ['written_full' => 70, 'written_pass' => 23, 'mcq_full' => 30, 'mcq_pass' => 10])
            ->assertSessionHasNoErrors();

        $st = $this->student('Parts', 1);
        $this->post('/exams/marks', ['exam_id' => $this->exam->id, 'class_id' => $this->class->id, 'subject_id' => $this->subjects['bangla']->id,
            'student_ids' => [$st->id], 'written' => [$st->id => 60], 'mcq' => [$st->id => 5]])->assertSessionHas('success');
        $mark = StudentMark::where(['exam_id' => $this->exam->id, 'student_id' => $st->id])->firstOrFail();
        $this->assertEquals(65, (float) $mark->total_mark);
        $this->assertEquals(0, (float) $mark->grade_point); // 65 total, but MCQ 5 < 10

        // Above a part's own full mark is refused.
        $this->post('/exams/marks', ['exam_id' => $this->exam->id, 'class_id' => $this->class->id, 'subject_id' => $this->subjects['bangla']->id,
            'student_ids' => [$st->id], 'written' => [$st->id => 50], 'mcq' => [$st->id => 35]])->assertSessionHas('error');

        $html = $this->get(route('exams.marksheet.view', [$st->id, $this->exam->id]))->getContent();
        $this->assertMatchesRegularExpression('/<td class="fail">5<\/td>/', $html);
    }
}
