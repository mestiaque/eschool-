<?php

namespace ME\SchoolManagement\Tests\Feature;

use Illuminate\Support\Facades\DB;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\Exam;
use ME\SchoolManagement\Models\Result;
use ME\SchoolManagement\Models\SchoolSetting;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\Subject;
use ME\SchoolManagement\Models\SubjectGroup;
use ME\SchoolManagement\Tests\TestCase;

/** bug.md 4.2 — 4th (optional) subject and group subjects. */
class OptionalSubjectTest extends TestCase
{
    private Classe $class;
    private Exam $exam;
    private array $subjects = [];

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('grades')->delete(); // default Bangladesh scale
        $this->actingAs($this->admin());
        $this->class = Classe::create(['name' => 'Test Nine', 'numeric_name' => random_int(100, 999), 'is_active' => true]);
        $science = SubjectGroup::firstOrCreate(['name' => 'Science'], ['is_active' => true]);
        $mk = fn ($name, $type, $group = null) => Subject::create(['name' => $name, 'class_id' => $this->class->id, 'type' => $type,
            'group_id' => $group, 'full_mark' => 100, 'pass_mark' => 33, 'is_active' => true]);
        $this->subjects = [
            'bangla'  => $mk('Bangla', 'compulsory'),
            'english' => $mk('English', 'compulsory'),
            'physics' => $mk('Physics', 'group', $science->id),
            'biology' => $mk('Biology', 'optional', $science->id),
        ];
        $this->exam = Exam::first();
    }

    private function student(bool $science = true, bool $takesBiology = true): Student
    {
        $base = Student::first();
        return Student::create([
            'student_id' => uniqid('T'), 'first_name' => 'Opt', 'last_name' => 'Test', 'gender' => 'male', 'date_of_birth' => '2010-01-01',
            'class_id' => $this->class->id, 'academic_year_id' => $base->academic_year_id, 'is_active' => true,
            'subject_group_id' => $science ? $this->subjects['physics']->group_id : null,
            'optional_subject_id' => $takesBiology ? $this->subjects['biology']->id : null,
        ]);
    }

    private function marks(Student $st, array $bySubject): Result
    {
        foreach ($bySubject as $key => $written) {
            $this->post('/exams/marks', ['exam_id' => $this->exam->id, 'class_id' => $this->class->id,
                'subject_id' => $this->subjects[$key]->id, 'student_ids' => [$st->id], 'written' => [$st->id => $written]]);
        }
        $this->post('/exams/results/generate', ['exam_id' => $this->exam->id, 'class_id' => $this->class->id]);
        return Result::where(['exam_id' => $this->exam->id, 'student_id' => $st->id])->firstOrFail();
    }

    public function test_fourth_subject_adds_grade_point_above_two(): void
    {
        // Main: A (4) + A (4) + A- (3.5) = 11.5; Biology A+ (5) adds 3 → 14.5 / 3 = 4.83
        $r = $this->marks($this->student(), ['bangla' => 75, 'english' => 72, 'physics' => 65, 'biology' => 90]);
        $this->assertEquals(4.83, (float) $r->gpa);
        $this->assertSame('A', $r->grade);
    }

    public function test_failing_the_fourth_subject_does_not_fail_the_result(): void
    {
        $r = $this->marks($this->student(), ['bangla' => 85, 'english' => 85, 'physics' => 85, 'biology' => 10]);
        $this->assertEquals(5.0, (float) $r->gpa);
        $this->assertSame('A+', $r->grade);
        $this->assertStringContainsString('4th subject failed', (string) $r->note);
    }

    public function test_failing_a_main_subject_fails_the_result(): void
    {
        $r = $this->marks($this->student(), ['bangla' => 85, 'english' => 20, 'physics' => 85, 'biology' => 95]);
        $this->assertEquals(0, (float) $r->gpa);
        $this->assertSame('F', $r->grade);
    }

    public function test_missing_main_subject_marks_fail_with_a_note(): void
    {
        $r = $this->marks($this->student(), ['bangla' => 85, 'english' => 85]);
        $this->assertSame('F', $r->grade);
        $this->assertStringContainsString('Physics', (string) $r->note);
    }

    public function test_bonus_can_be_switched_off(): void
    {
        SchoolSetting::put('optional_subject_bonus', '0');
        $r = $this->marks($this->student(), ['bangla' => 75, 'english' => 72, 'physics' => 65, 'biology' => 90]);
        $this->assertEquals(3.83, (float) $r->gpa); // 11.5 / 3
        SchoolSetting::put('optional_subject_bonus', '1');
    }

    public function test_marks_entry_lists_only_students_who_take_the_subject(): void
    {
        $sci = $this->student(true, true);
        $other = $this->student(false, false);
        $url = fn ($key) => "/exams/marks?exam_id={$this->exam->id}&class_id={$this->class->id}&subject_id={$this->subjects[$key]->id}";

        $this->get($url('bangla'))->assertSee("written[{$sci->id}]", false)->assertSee("written[{$other->id}]", false);
        $this->get($url('physics'))->assertSee("written[{$sci->id}]", false)->assertDontSee("written[{$other->id}]", false);
        $this->get($url('biology'))->assertSee("written[{$sci->id}]", false)->assertDontSee("written[{$other->id}]", false);

        // Saving physics marks for a non-science student is ignored.
        $this->post('/exams/marks', ['exam_id' => $this->exam->id, 'class_id' => $this->class->id,
            'subject_id' => $this->subjects['physics']->id, 'student_ids' => [$other->id], 'written' => [$other->id => 50]]);
        $this->assertDatabaseMissing('student_marks', ['student_id' => $other->id, 'subject_id' => $this->subjects['physics']->id]);
    }
}
