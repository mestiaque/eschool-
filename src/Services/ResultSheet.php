<?php

namespace ME\SchoolManagement\Services;

use Illuminate\Support\Collection;
use ME\SchoolManagement\Models\Exam;
use ME\SchoolManagement\Models\ExamSchedule;
use ME\SchoolManagement\Models\Result;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentMark;
use ME\SchoolManagement\Models\Subject;

/**
 * Data for marksheets and the class result (tabulation) sheet, so the admin
 * marksheet, the bulk print, the student portal and the tabulation sheet all
 * show the same subjects, marks and pass/fail.
 */
class ResultSheet
{
    /** [full mark, pass mark] for a subject in an exam; schedule first, then the subject's own. */
    public static function subjectMarks($examId, $classId, $subjectId, ?Collection $schedules = null, ?Subject $subject = null): array
    {
        $schedule = $schedules
            ? $schedules->get($subjectId)
            : ExamSchedule::where('exam_id', $examId)->where('class_id', $classId)->where('subject_id', $subjectId)->first();
        if ($schedule) return [(float) $schedule->full_mark, (float) $schedule->pass_mark];
        $subject ??= Subject::find($subjectId);
        $full = (float) ($subject->full_mark ?? 100) ?: 100;
        return [$full, (float) ($subject->pass_mark ?? round($full * 0.33, 2))];
    }

    /** Grade for a subject total; absent or below the pass mark is the fail grade. */
    public static function gradeFor(float $total, float $fullMark, float $passMark, bool $absent): array
    {
        return ($absent || $total < $passMark || $fullMark <= 0)
            ? GradeScale::fail()
            : GradeScale::forPercent($total / $fullMark * 100);
    }

    /** Re-grade an exam's marks for a class with the current scale and pass marks. */
    public static function regrade($examId, $classId): void
    {
        $schedules = ExamSchedule::where('exam_id', $examId)->where('class_id', $classId)->get()->keyBy('subject_id');
        $subjects = Subject::where('class_id', $classId)->get()->keyBy('id');
        StudentMark::where('exam_id', $examId)->where('class_id', $classId)->get()
            ->each(function ($mark) use ($examId, $classId, $schedules, $subjects) {
                [$full, $pass] = self::subjectMarks($examId, $classId, $mark->subject_id, $schedules, $subjects->get($mark->subject_id));
                $grade = self::gradeFor((float) $mark->total_mark, $full, $pass, (bool) $mark->is_absent);
                if ($mark->grade_id != $grade['id'] || (float) $mark->grade_point !== (float) $grade['point']) {
                    $mark->update(['grade_id' => $grade['id'], 'grade_point' => $grade['point']]);
                }
            });
    }

    /** Subjects a student sits: their eligible subjects, or whatever was marked when none are set up. */
    public static function subjectsFor(Student $student, Collection $marks): Collection
    {
        $subjects = $student->getEligibleSubjects();
        $extra = $marks->keys()->diff($subjects->pluck('id'));
        if ($extra->isNotEmpty()) {
            $subjects = $subjects->concat(Subject::whereIn('id', $extra)->get());
        }
        // 4th subject last, as on board marksheets.
        return $subjects->sortBy(fn ($s) => [$s->id == $student->optional_subject_id ? 1 : 0, $s->code ?? '', $s->name])->values();
    }

    /**
     * One student's marksheet.
     *
     * @return array{rows: array, obtained: float, possible: float, result: ?Result, passed: bool}
     */
    public static function marksheet(Student $student, Exam $exam): array
    {
        $marks = StudentMark::where('student_id', $student->id)->where('exam_id', $exam->id)->with('grade')->get()->keyBy('subject_id');
        $schedules = ExamSchedule::where('exam_id', $exam->id)->where('class_id', $student->class_id)->get()->keyBy('subject_id');
        $result = Result::where('student_id', $student->id)->where('exam_id', $exam->id)->first();

        $rows = [];
        $obtained = 0; $possible = 0;
        foreach (self::subjectsFor($student, $marks) as $subject) {
            [$full, $pass] = self::subjectMarks($exam->id, $student->class_id, $subject->id, $schedules, $subject);
            $mark = $marks->get($subject->id);
            $status = !$mark ? 'missing' : ($mark->is_absent ? 'absent' : ((float) $mark->grade_point > 0 ? 'pass' : 'fail'));
            $rows[] = [
                'subject'  => $subject,
                'optional' => $subject->id == $student->optional_subject_id,
                'full'     => $full,
                'pass'     => $pass,
                'mark'     => $mark,
                'status'   => $status,
            ];
            $possible += $full;
            $obtained += $mark && !$mark->is_absent ? (float) $mark->total_mark : 0;
        }

        return [
            'rows'     => $rows,
            'obtained' => $obtained,
            'possible' => $possible,
            'result'   => $result,
            'passed'   => $result && !GradeScale::isFail($result->grade),
        ];
    }

    /**
     * Class tabulation sheet: students (by roll), subjects, marks and results.
     *
     * @return array{subjects: Collection, students: Collection, marks: Collection, results: Collection, summary: array}
     */
    public static function tabulation(Exam $exam, $classId, $sectionId = null): array
    {
        $results = Result::where('exam_id', $exam->id)->where('class_id', $classId)
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->get()->keyBy('student_id');
        $marks = StudentMark::where('exam_id', $exam->id)->where('class_id', $classId)->get()->groupBy('student_id')
            ->map(fn ($m) => $m->keyBy('subject_id'));

        $students = Student::withTrashed()->with('section')
            ->whereIn('id', $results->keys()->merge($marks->keys())->unique())
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->get()
            ->sortBy(fn ($s) => [$s->section->name ?? '', (int) $s->roll_number, $s->first_name])->values();

        $subjects = Subject::where('class_id', $classId)
            ->whereIn('id', $marks->flatten(1)->pluck('subject_id')->unique())
            ->get()
            ->sortBy(fn ($s) => [$s->type === 'optional' ? 2 : ($s->type === 'group' ? 1 : 0), $s->code ?? '', $s->name])->values();

        $withResult = $students->filter(fn ($s) => $results->has($s->id));
        $passed = $withResult->filter(fn ($s) => !GradeScale::isFail($results[$s->id]->grade))->count();

        return [
            'subjects' => $subjects,
            'students' => $students,
            'marks'    => $marks,
            'results'  => $results,
            'summary'  => [
                'appeared' => $withResult->count(),
                'passed'   => $passed,
                'failed'   => $withResult->count() - $passed,
                'rate'     => $withResult->count() ? round($passed / $withResult->count() * 100, 1) : 0,
                'grades'   => $results->groupBy('grade')->map->count()
                    ->sortBy(fn ($n, $g) => ($i = array_search($g, array_column(GradeScale::rows(), 'name'))) === false ? 99 : $i),
            ],
        ];
    }
}
