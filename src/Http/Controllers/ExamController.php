<?php

namespace ME\SchoolManagement\Http\Controllers;

use Illuminate\Http\Request;
use ME\Http\Controllers\Controller;
use ME\SchoolManagement\Http\Controllers\Concerns\GuardsDeletes;
use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\Exam;
use ME\SchoolManagement\Models\ExamSchedule;
use ME\SchoolManagement\Models\ExamType;
use ME\SchoolManagement\Models\Grade;
use ME\SchoolManagement\Models\Result;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentMark;
use ME\SchoolManagement\Models\Subject;

class ExamController extends Controller
{
    use GuardsDeletes;

    // ── Exam Types ─────────────────────────────────────────────────────────

    public function examTypeIndex()
    {
        $examTypes = ExamType::orderBy('id', 'desc')->paginate(20);
        return view('school-management::exams.types', compact('examTypes'));
    }

    public function examTypeStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        ExamType::create(['name' => $request->name, 'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true]);
        return back()->with('success', 'Exam type created.');
    }

    public function examTypeUpdate(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100']);
        ExamType::findOrFail($id)->update(['name' => $request->name, 'is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Exam type updated.');
    }

    public function examTypeDestroy($id)
    {
        $type = ExamType::findOrFail($id);
        return $this->deleteUnlessUsed($type, [
            'exams' => $this->uses('exams', 'exam_type_id', $id),
        ], $type->name, 'Exam type deleted.');
    }

    // ── Exams ──────────────────────────────────────────────────────────────

    public function examIndex()
    {
        $exams     = Exam::with('examType')->orderBy('id', 'desc')->paginate(20);
        $examTypes = ExamType::where('is_active', true)->get();
        return view('school-management::exams.index', compact('exams', 'examTypes'));
    }

    public function examStore(Request $request)
    {
        $data = $this->validateExam($request);
        // The form has no year picker; attach the current academic year.
        $data['academic_year_id'] = $data['academic_year_id'] ?? AcademicYear::where('is_current', true)->value('id')
            ?? AcademicYear::orderByDesc('id')->value('id');
        if (!$data['academic_year_id']) {
            return back()->withInput()->with('error', 'Create an academic year first.');
        }
        Exam::create($data);
        return back()->with('success', 'Exam created.');
    }

    public function examUpdate(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        $data = $this->validateExam($request);
        if (empty($data['academic_year_id'])) unset($data['academic_year_id']);
        $exam->update($data);
        return back()->with('success', 'Exam updated.');
    }

    public function examDestroy($id)
    {
        $exam = Exam::findOrFail($id);
        return $this->deleteUnlessUsed($exam, [
            'entered marks'  => $this->uses('student_marks', 'exam_id', $id),
            'results'        => $this->uses('results', 'exam_id', $id),
            'exam schedules' => $this->uses('exam_schedules', 'exam_id', $id),
        ], $exam->name, 'Exam deleted.');
    }

    // ── Schedule ───────────────────────────────────────────────────────────

    public function scheduleIndex(Request $request)
    {
        $query = ExamSchedule::with(['exam', 'class', 'subject']);
        if ($request->exam_id)  $query->where('exam_id',  $request->exam_id);
        if ($request->class_id) $query->where('class_id', $request->class_id);
        $schedules = $query->orderBy('date')->orderBy('start_time')->paginate(25)->withQueryString();

        $exams   = Exam::where('is_active', true)->get();
        $classes = Classe::where('is_active', true)->get();
        return view('school-management::exams.schedule', compact('schedules', 'exams', 'classes'));
    }

    public function scheduleStore(Request $request)
    {
        $request->validate([
            'exam_id'    => 'required|exists:exams,id',
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'date'       => 'required|date',
            'start_time' => 'required',
            'end_time'   => 'required',
            'full_mark'  => 'required|numeric|min:1',
            'pass_mark'  => 'required|numeric|min:0|lte:full_mark',
        ]);
        ExamSchedule::create($request->only(
            'exam_id','class_id','subject_id','date','start_time','end_time','room','full_mark','pass_mark'
        ));
        return back()->with('success', 'Schedule added.');
    }

    public function scheduleUpdate(Request $request, $id)
    {
        $request->validate([
            'exam_id'    => 'required|exists:exams,id',
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'date'       => 'required|date',
            'start_time' => 'required',
            'end_time'   => 'required',
            'full_mark'  => 'required|numeric|min:1',
            'pass_mark'  => 'required|numeric|min:0|lte:full_mark',
        ]);
        ExamSchedule::findOrFail($id)->update($request->only(
            'exam_id','class_id','subject_id','date','start_time','end_time','room','full_mark','pass_mark'
        ));
        return back()->with('success', 'Schedule updated.');
    }

    public function scheduleDestroy($id)
    {
        ExamSchedule::findOrFail($id)->delete();
        return back()->with('success', 'Schedule deleted.');
    }

    public function getSubjectsByClass($class_id)
    {
        return Subject::where('class_id', $class_id)->where('is_active', true)->get(['id','name']);
    }

    // ── Grades ─────────────────────────────────────────────────────────────

    public function gradeIndex()
    {
        $grades = Grade::orderBy('mark_from', 'desc')->paginate(20);
        return view('school-management::exams.grades', compact('grades'));
    }

    public function gradeStore(Request $request)
    {
        Grade::create($this->validateGrade($request) + ['is_active' => true]);
        return back()->with('success', 'Grade created.');
    }

    public function gradeUpdate(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);
        $grade->update($this->validateGrade($request, $grade->id));
        return back()->with('success', 'Grade updated.');
    }

    public function gradeDestroy($id)
    {
        $grade = Grade::findOrFail($id);
        return $this->deleteUnlessUsed($grade, [
            'entered marks' => $this->uses('student_marks', 'grade_id', $id),
        ], $grade->name, 'Grade deleted.');
    }

    // ── Marks Entry ────────────────────────────────────────────────────────

    public function marksIndex(Request $request)
    {
        $exams   = Exam::where('is_active', true)->get();
        $classes = Classe::where('is_active', true)->get();
        $subjects = $request->class_id
            ? Subject::where('class_id', $request->class_id)->where('is_active', true)->get()
            : collect();

        $students   = collect();
        $marksMap   = [];
        $examObj    = null;
        $subjectObj = null;
        $schedule   = null;

        if ($request->exam_id && $request->class_id && $request->subject_id) {
            $examObj    = Exam::find($request->exam_id);
            $subjectObj = Subject::find($request->subject_id);
            $schedule   = ExamSchedule::where('exam_id',    $request->exam_id)
                ->where('class_id',   $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->first();

            $students = Student::where('class_id', $request->class_id)
                ->where('is_active', true)
                ->orderBy('roll_number')
                ->orderBy('first_name')
                ->get();

            $existingMarks = StudentMark::where('exam_id',    $request->exam_id)
                ->where('subject_id', $request->subject_id)
                ->whereIn('student_id', $students->pluck('id'))
                ->with('grade')
                ->get()
                ->keyBy('student_id');

            foreach ($students as $student) {
                $marksMap[$student->id] = $existingMarks[$student->id] ?? null;
            }
        }

        return view('school-management::exams.marks', compact(
            'exams','classes','subjects','students','marksMap','examObj','subjectObj','schedule'
        ));
    }

    public function marksPrint(Request $request)
    {
        $exams   = Exam::where('is_active', true)->get();
        $classes = Classe::where('is_active', true)->get();
        $subjects = $request->class_id
            ? Subject::where('class_id', $request->class_id)->where('is_active', true)->get()
            : collect();

        $students   = collect();
        $marksMap   = [];
        $examObj    = null;
        $subjectObj = null;
        $schedule   = null;

        if ($request->exam_id && $request->class_id && $request->subject_id) {
            $examObj    = Exam::find($request->exam_id);
            $subjectObj = Subject::find($request->subject_id);
            $schedule   = ExamSchedule::where('exam_id',    $request->exam_id)
                ->where('class_id',   $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->first();

            $students = Student::where('class_id', $request->class_id)
                ->where('is_active', true)
                ->orderBy('roll_number')
                ->orderBy('first_name')
                ->get();

            $existingMarks = StudentMark::where('exam_id',    $request->exam_id)
                ->where('subject_id', $request->subject_id)
                ->whereIn('student_id', $students->pluck('id'))
                ->with('grade')
                ->get()
                ->keyBy('student_id');

            foreach ($students as $student) {
                $marksMap[$student->id] = $existingMarks[$student->id] ?? null;
            }
        }

        return view('school-management::exams.marks-print', compact(
            'exams','classes','subjects','students','marksMap','examObj','subjectObj','schedule'
        ));
    }

    public function marksStore(Request $request)
    {
        $request->validate([
            'exam_id'     => 'required|exists:exams,id',
            'class_id'    => 'required|exists:classes,id',
            'subject_id'  => 'required|exists:subjects,id',
            'written.*'   => 'nullable|numeric|min:0',
            'mcq.*'       => 'nullable|numeric|min:0',
            'practical.*' => 'nullable|numeric|min:0',
            'ca.*'        => 'nullable|numeric|min:0',
        ]);
        if (empty($request->student_ids)) {
            return back()->with('error', 'No students to save marks for.');
        }

        [$fullMark, $passMark] = $this->subjectMarks($request->exam_id, $request->class_id, $request->subject_id);
        $classStudentIds = Student::where('class_id', $request->class_id)->pluck('id')->flip();

        // Validate every row first so a bad row doesn't leave a half-saved sheet.
        foreach ($request->student_ids as $studentId) {
            $total = (float) ($request->written[$studentId] ?? 0) + (float) ($request->mcq[$studentId] ?? 0)
                   + (float) ($request->practical[$studentId] ?? 0) + (float) ($request->ca[$studentId] ?? 0);
            if ($total > $fullMark) {
                return back()->withInput()->with('error', "Total marks ($total) exceed the full mark ($fullMark) for student #$studentId.");
            }
        }

        foreach ($request->student_ids as $studentId) {
            if (!isset($classStudentIds[$studentId])) continue;
            $written   = (float) ($request->written[$studentId]   ?? 0);
            $mcq       = (float) ($request->mcq[$studentId]       ?? 0);
            $practical = (float) ($request->practical[$studentId] ?? 0);
            $ca        = (float) ($request->ca[$studentId]        ?? 0);
            $isAbsent  = isset($request->is_absent[$studentId]);
            $total     = $isAbsent ? 0 : ($written + $mcq + $practical + $ca);

            // Grade on the percentage, and a subject below its pass mark is an F.
            $grade = ($isAbsent || $total < $passMark)
                ? $this->failGrade()
                : $this->gradeForPercent($total / $fullMark * 100);

            StudentMark::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'exam_id'    => $request->exam_id,
                    'subject_id' => $request->subject_id,
                ],
                [
                    'class_id'    => $request->class_id,
                    'written'     => $written,
                    'mcq'         => $mcq,
                    'practical'   => $practical,
                    'ca'          => $ca,
                    'total_mark'  => $total,
                    'grade_id'    => $grade['id'],
                    'grade_point' => $grade['point'],
                    'is_absent'   => $isAbsent,
                ]
            );
        }

        return back()->with('success', 'Marks saved successfully.');
    }

    // ── Results ────────────────────────────────────────────────────────────

    public function resultIndex(Request $request)
    {
        $query = Result::with(['student', 'exam', 'classe']);
        if ($request->exam_id)  $query->where('exam_id',  $request->exam_id);
        if ($request->class_id) $query->where('class_id', $request->class_id);

        $results = $query->orderBy('position')->orderBy('obtained_mark', 'desc')->paginate(30)->withQueryString();
        $exams   = Exam::where('is_active', true)->get();
        $classes = Classe::where('is_active', true)->get();

        return view('school-management::exams.results', compact('results', 'exams', 'classes'));
    }

    public function generateResult(Request $request)
    {
        $request->validate(['exam_id' => 'required|exists:exams,id', 'class_id' => 'required|exists:classes,id']);

        $studentIds = StudentMark::where('exam_id',  $request->exam_id)
            ->where('class_id', $request->class_id)
            ->distinct()
            ->pluck('student_id');

        if ($studentIds->isEmpty()) {
            return back()->with('error', 'No marks found for the selected exam and class.');
        }

        // Total possible marks from schedule
        $totalPossible = ExamSchedule::where('exam_id',  $request->exam_id)
            ->where('class_id', $request->class_id)
            ->sum('full_mark');

        $subjectCount = Subject::where('class_id', $request->class_id)->where('is_active', true)->count();

        foreach ($studentIds as $studentId) {
            $studentMarks = StudentMark::where('exam_id',    $request->exam_id)
                ->where('class_id',   $request->class_id)
                ->where('student_id', $studentId)
                ->get();

            $obtained = $studentMarks->sum('total_mark');
            // Any absent/failed subject (grade point 0) fails the whole result.
            $failed = $studentMarks->contains(fn($m) => $m->is_absent || (float) $m->grade_point <= 0);
            $gpa    = $failed ? 0 : round(min(5, $studentMarks->avg('grade_point') ?? 0), 2);
            $grade  = $failed ? $this->failGrade()['name'] : $this->gradeForGpa($gpa);

            Result::updateOrCreate(
                ['student_id' => $studentId, 'exam_id' => $request->exam_id],
                [
                    'class_id'      => $request->class_id,
                    'section_id'    => Student::whereKey($studentId)->value('section_id'),
                    'total_mark'    => $totalPossible,
                    'obtained_mark' => $obtained,
                    'gpa'           => $gpa,
                    'grade'         => $grade,
                    'is_published'  => false,
                    'note'          => $subjectCount && $studentMarks->count() < $subjectCount
                        ? 'Marks missing for ' . ($subjectCount - $studentMarks->count()) . ' subject(s)' : null,
                ]
            );
        }

        // Rank by GPA, then total marks; equal scores share a position (1, 2, 2, 4).
        $sorted = Result::where('exam_id',  $request->exam_id)
            ->where('class_id', $request->class_id)
            ->orderBy('gpa', 'desc')
            ->orderBy('obtained_mark', 'desc')
            ->get();

        $pos = 0; $prev = null;
        foreach ($sorted as $i => $r) {
            $key = $r->gpa . '|' . $r->obtained_mark;
            if ($key !== $prev) { $pos = $i + 1; $prev = $key; }
            $r->update(['position' => $pos]);
        }

        return back()->with('success', 'Result generated for ' . $studentIds->count() . ' students.');
    }

    public function publishResult(Request $request)
    {
        $request->validate(['exam_id' => 'required|exists:exams,id', 'class_id' => 'required|exists:classes,id']);
        $count = Result::where('exam_id',  $request->exam_id)
            ->where('class_id', $request->class_id)
            ->update(['is_published' => true]);
        return back()->with('success', "$count results published.");
    }

    // ── Marksheet ──────────────────────────────────────────────────────────

    public function marksheetIndex(Request $request)
    {
        $exams   = Exam::where('is_active', true)->get();
        $classes = Classe::where('is_active', true)->get();
        $students = $request->class_id
            ? Student::where('class_id', $request->class_id)->where('is_active', true)->orderBy('roll_number')->get()
            : collect();

        return view('school-management::exams.marksheet-index', compact('exams', 'classes', 'students'));
    }

    public function marksheet($student_id, $exam_id)
    {
        $student  = Student::with('classe', 'section')->findOrFail($student_id);
        $exam     = Exam::with('academicYear', 'examType')->findOrFail($exam_id);
        $marks    = StudentMark::where('student_id', $student_id)
            ->where('exam_id', $exam_id)
            ->with('subject', 'grade')
            ->get();
        $result   = Result::where('student_id', $student_id)->where('exam_id', $exam_id)->first();

        // Schedule map: subject_id => ExamSchedule (for full_mark / pass_mark)
        $scheduleMap = ExamSchedule::where('exam_id',  $exam_id)
            ->where('class_id', $student->class_id)
            ->get()
            ->keyBy('subject_id');

        $schoolName = \ME\SchoolManagement\Models\SchoolSetting::where('key','school_name')->value('value')
            ?? config('app.name', 'School');

        return view('school-management::exams.marksheet',
            compact('student','exam','marks','result','scheduleMap','schoolName'));
    }

    // ── Grading helpers ────────────────────────────────────────────────────

    /** Standard Bangladesh scale, used when no grades are configured. */
    private const DEFAULT_SCALE = [
        ['name' => 'A+', 'point' => 5.00, 'from' => 80],
        ['name' => 'A',  'point' => 4.00, 'from' => 70],
        ['name' => 'A-', 'point' => 3.50, 'from' => 60],
        ['name' => 'B',  'point' => 3.00, 'from' => 50],
        ['name' => 'C',  'point' => 2.00, 'from' => 40],
        ['name' => 'D',  'point' => 1.00, 'from' => 33],
        ['name' => 'F',  'point' => 0.00, 'from' => 0],
    ];

    private ?array $scale = null;

    /** Grade scale as [id, name, point, from], highest first. Grade ranges are percentages. */
    private function scale(): array
    {
        return $this->scale ??= ($grades = Grade::where('is_active', true)->orderByDesc('mark_from')->get())->isNotEmpty()
            ? $grades->map(fn($g) => ['id' => $g->id, 'name' => $g->name, 'point' => (float) $g->grade_point, 'from' => (float) $g->mark_from])->all()
            : array_map(fn($g) => $g + ['id' => null], self::DEFAULT_SCALE);
    }

    private function gradeForPercent(float $percent): array
    {
        foreach ($this->scale() as $g) {
            if ($percent >= $g['from']) return $g;
        }
        return $this->failGrade();
    }

    private function failGrade(): array
    {
        $scale = $this->scale();
        return end($scale) ?: ['id' => null, 'name' => 'F', 'point' => 0.0, 'from' => 0];
    }

    private function gradeForGpa(float $gpa): string
    {
        foreach ($this->scale() as $g) {
            if ($gpa >= $g['point']) return $g['name'];
        }
        return $this->failGrade()['name'];
    }

    /** [full mark, pass mark] for a subject in an exam; schedule first, then the subject's own. */
    private function subjectMarks($examId, $classId, $subjectId): array
    {
        $schedule = ExamSchedule::where('exam_id', $examId)->where('class_id', $classId)->where('subject_id', $subjectId)->first();
        if ($schedule) return [(float) $schedule->full_mark, (float) $schedule->pass_mark];
        $subject = Subject::find($subjectId);
        $full = (float) ($subject->full_mark ?? 100) ?: 100;
        return [$full, (float) ($subject->pass_mark ?? round($full * 0.33, 2))];
    }

    private function validateExam(Request $request): array
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'exam_type_id'     => 'required|exists:exam_types,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'notice'           => 'nullable|string',
        ]);
        $data['is_published'] = $request->boolean('is_published');
        $data['is_active']    = $request->has('is_active') ? $request->boolean('is_active') : true;
        return $data;
    }

    private function validateGrade(Request $request, $ignoreId = null): array
    {
        $data = $request->validate([
            'name'        => 'required|string|max:10',
            'grade_point' => 'required|numeric|min:0|max:5',
            'mark_from'   => 'required|numeric|min:0|max:100',
            'mark_to'     => 'required|numeric|min:0|max:100|gte:mark_from',
            'description' => 'nullable|string|max:255',
        ]);
        $overlaps = Grade::when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where('mark_from', '<=', $data['mark_to'])->where('mark_to', '>=', $data['mark_from'])->exists();
        if ($overlaps) {
            throw \Illuminate\Validation\ValidationException::withMessages(['mark_from' => 'This mark range overlaps an existing grade.']);
        }
        return $data;
    }
}
