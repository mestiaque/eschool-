<?php

namespace ME\SchoolManagement\Http\Controllers;
use ME\Http\Controllers\Controller;

use Illuminate\Http\Request;
use ME\SchoolManagement\Models\Teacher;
use ME\SchoolManagement\Models\TeacherAttendance;
use ME\SchoolManagement\Models\TeacherLeave;
use ME\SchoolManagement\Models\LeaveType;
use ME\SchoolManagement\Models\Subject;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\Section;
use ME\SchoolManagement\Models\TeacherClass;
use ME\SchoolManagement\Models\TeacherSubject;
use ME\SchoolManagement\Models\ClassRoutine;
use ME\SchoolManagement\Models\ClassTest;
use ME\SchoolManagement\Models\ClassTestResult;
use ME\SchoolManagement\Models\Assignment;
use ME\SchoolManagement\Models\AssignmentSubmission;
use ME\SchoolManagement\Models\Notice;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentAttendance;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::query();
        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('first_name', 'like', "%$s%")
                  ->orWhere('last_name', 'like', "%$s%")
                  ->orWhere('teacher_id', 'like', "%$s%"));
        }
        $teachers = $query->orderBy('id', 'desc')->paginate(20);
        return view('school-management::teachers.index', compact('teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->teacherRules());
        $data['teacher_id'] = $this->newTeacherCode();
        $data['salary']     = $data['salary'] ?? 0;
        Teacher::create($data);
        return back()->with('success', 'Teacher added successfully');
    }

    public function show($id)
    {
        $teacher = Teacher::findOrFail($id);
        $linkedUser = \ME\Models\User::where('teacher_id', $id)->first();
        $availableUsers = \ME\Models\User::whereNull('teacher_id')->orderBy('name')->get();
        return view('school-management::teachers.show', compact('teacher', 'linkedUser', 'availableUsers'));
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        // The profile page edits a subset of fields, so only validate what was sent.
        $rules = array_map(fn($r) => 'sometimes|' . $r, $this->teacherRules($teacher->id));
        $data  = $request->validate($rules);
        if (array_key_exists('salary', $data)) $data['salary'] = $data['salary'] ?? 0;
        $teacher->update($data);
        return back()->with('success', 'Teacher updated successfully');
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $log = function_exists('me_change_log') ? me_change_log("Teacher moved to Recycle Bin: {$teacher->full_name}")->watch($teacher) : null;
        $log ? $log->delete(fn () => $teacher->delete()) : $teacher->delete();
        // A binned teacher must not keep using the teacher portal.
        \ME\Models\User::where('teacher_id', $teacher->id)->update(['teacher_id' => null]);
        return back()->with('success', 'Teacher moved to the Recycle Bin (login account unlinked).');
    }

    // Attendance
    public function attendanceIndex(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $teachers = Teacher::with(['attendances' => function($q) use ($date) {
            $q->where('date', $date);
        }])->get();
        return view('school-management::teachers.attendance', compact('teachers', 'date'));
    }

    public function attendanceStore(Request $request)
    {
        $request->validate([
            'date'          => 'required|date|before_or_equal:today',
            'teacher_ids'   => 'required|array|min:1',
            'teacher_ids.*' => 'exists:teachers,id',
            'status.*'      => 'nullable|in:present,absent,late,excused',
        ], ['teacher_ids.required' => 'Select at least one teacher.']);
        if ($closed = \ME\SchoolManagement\Services\SchoolCalendar::closedReason($request->date)) {
            return back()->withInput()->with('error', "Attendance can't be taken on " . sm_date($request->date) . " — {$closed}.");
        }

        foreach ($request->teacher_ids as $teacherId) {
            TeacherAttendance::updateOrCreate(
                ['teacher_id' => $teacherId, 'date' => $request->date],
                [
                    'status'   => $request->status[$teacherId]   ?? 'present',
                    'in_time'  => $request->in_time[$teacherId]  ?? null,
                    'out_time' => $request->out_time[$teacherId] ?? null,
                ]
            );
        }
        return back()->with('success', 'Attendance saved for ' . date('d M Y', strtotime($request->date)));
    }

    // Leaves
    public function leaveIndex()
    {
        $leaves = TeacherLeave::with('teacher')->orderBy('id', 'desc')->paginate(20);
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $teachers = Teacher::where('is_active', true)->orderBy('first_name')->get();
        return view('school-management::teachers.leaves', compact('leaves', 'leaveTypes', 'teachers'));
    }

    public function leaveStore(Request $request)
    {
        $request->validate([
            'teacher_id'    => 'required|exists:teachers,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string',
        ]);
        TeacherLeave::create([
            'teacher_id'    => $request->teacher_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'reason'        => $request->reason,
            'status'        => 'pending',
        ]);
        return back()->with('success', 'Leave request submitted');
    }

    public function leaveUpdate(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,approved,rejected']);
        TeacherLeave::findOrFail($id)->update(['status' => $request->status, 'note' => $request->note]);
        return back()->with('success', 'Leave status updated');
    }

    public function leaveApprove($id)
    {
        TeacherLeave::findOrFail($id)->update(['status' => 'approved']);
        return back()->with('success', 'Leave approved');
    }

    public function leaveReject($id)
    {
        TeacherLeave::findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'Leave rejected');
    }

    // Subject Assignment
    public function subjectIndex()
    {
        $teachers = Teacher::with('subjects.subject', 'subjects.classe')->where('is_active', true)->get();
        $subjects = Subject::with('class')->get();
        return view('school-management::teachers.subjects', compact('teachers', 'subjects'));
    }

    public function subjectStore(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id'   => 'nullable|exists:classes,id',
        ]);
        // A subject belongs to one class; derive it when the form doesn't send it.
        $data['class_id'] = $data['class_id'] ?? Subject::whereKey($data['subject_id'])->value('class_id');
        $assignment = TeacherSubject::firstOrCreate($data);
        return back()->with($assignment->wasRecentlyCreated ? 'success' : 'error',
            $assignment->wasRecentlyCreated ? 'Subject assigned successfully' : 'This subject is already assigned to the teacher');
    }

    public function subjectDestroy($id)
    {
        \ME\SchoolManagement\Models\TeacherSubject::findOrFail($id)->delete();
        return back()->with('success', 'Subject unassigned');
    }

    // Class Assignment
    public function classIndex()
    {
        $assignments = \ME\SchoolManagement\Models\TeacherClass::with('teacher', 'classe', 'section')->get();
        $teachers = Teacher::where('is_active', true)->get();
        $classes = \ME\SchoolManagement\Models\Classe::where('is_active', true)->get();
        $sections = \ME\SchoolManagement\Models\Section::where('is_active', true)->get();
        return view('school-management::teachers.classes', compact('assignments', 'teachers', 'classes', 'sections'));
    }

    public function classStore(Request $request)
    {
        $data = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id'   => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);
        $isClassTeacher = $request->boolean('is_class_teacher');

        if (TeacherClass::where($data)->exists()) {
            return back()->with('error', 'This class is already assigned to the teacher');
        }
        // Only one class teacher per class/section.
        if ($isClassTeacher) {
            TeacherClass::where('class_id', $data['class_id'])
                ->where('section_id', $data['section_id'] ?? null)
                ->update(['is_class_teacher' => false]);
        }
        TeacherClass::create($data + ['is_class_teacher' => $isClassTeacher]);
        return back()->with('success', 'Class assigned successfully');
    }

    public function classDestroy($id)
    {
        \ME\SchoolManagement\Models\TeacherClass::findOrFail($id)->delete();
        return back()->with('success', 'Class assignment removed');
    }

    // Salary
    public function salaryIndex(Request $request)
    {
        $query = \ME\SchoolManagement\Models\SalaryPayment::with('teacher')
            ->where('staff_type', 'teacher');
        if ($request->month) $query->where('month', $request->month);
        if ($request->year) $query->where('year', $request->year);
        $salaries = $query->orderBy('id', 'desc')->paginate(20);
        $teachers = Teacher::where('is_active', true)->orderBy('first_name')->get();
        return view('school-management::teachers.salary', compact('salaries', 'teachers'));
    }

    public function salaryStore(Request $request)
    {
        $request->validate([
            'teacher_id'   => 'required|exists:teachers,id',
            'month'        => 'required',
            'year'         => 'required|integer|min:2000|max:2100',
            'basic_salary' => 'required|numeric|min:0',
            'allowance'    => 'nullable|numeric|min:0',
            'deduction'    => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
        ]);

        $alreadyPaid = \ME\SchoolManagement\Models\SalaryPayment::where('staff_type', 'teacher')
            ->where('staff_id', $request->teacher_id)
            ->where('month', $request->month)->where('year', $request->year)->exists();
        if ($alreadyPaid) {
            return back()->withInput()->with('error', 'Salary for this teacher and month has already been paid.');
        }

        $allowance = $request->allowance ?? 0;
        $deduction = $request->deduction ?? 0;
        $grossSalary = $request->basic_salary + $allowance;

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $allowance, $deduction, $grossSalary) {
            $payment = \ME\SchoolManagement\Models\SalaryPayment::create([
                'staff_id' => $request->teacher_id,
                'staff_type' => 'teacher',
                'basic_salary' => $request->basic_salary,
                'allowance' => $allowance,
                'deduction' => $deduction,
                'gross_salary' => $grossSalary,
                'net_salary' => $grossSalary - $deduction,
                'month' => $request->month,
                'year' => $request->year,
                'payment_date' => $request->payment_date ?? now(),
                'note' => $request->note,
            ]);
            // Post to the expense ledger so reports count it once.
            \ME\SchoolManagement\Services\SalaryPoster::post($payment);
        });
        return back()->with('success', 'Salary processed and posted to expenses');
    }

    // Leave Types
    public function leaveTypeStore(Request $request)
    {
        LeaveType::create($request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name',
            'days' => 'required|integer|min:0',
        ]) + ['is_active' => true]);
        return back()->with('success', 'Leave type created');
    }

    // ── My Dashboard ──────────────────────────────────────────────────────

    public function myDashboard()
    {
        $teacherId = auth()->user()->teacher_id ?? null;

        if (! $teacherId) {
            return view('school-management::teachers.my-dashboard', ['teacherId' => null]);
        }

        $today   = now()->format('Y-m-d');
        $dayName = now()->format('l');

        $todaysPeriods = ClassRoutine::with(['class', 'section', 'subject', 'room'])
            ->where('teacher_id', $teacherId)
            ->where('day', $dayName)
            ->orderBy('start_time')
            ->get();

        // Distinct class/section combos this teacher is responsible for.
        // Built from plain string keys (not Eloquent Collection::merge/unique,
        // which key by the model's primary key — absent here since only
        // class_id/section_id were selected, which silently collapses rows).
        $pairKeys = collect();
        ClassRoutine::where('teacher_id', $teacherId)->select('class_id', 'section_id')->distinct()->get()
            ->each(fn($p) => $pairKeys->push($p->class_id . '|' . $p->section_id));
        TeacherClass::where('teacher_id', $teacherId)->select('class_id', 'section_id')->distinct()->get()
            ->each(fn($p) => $pairKeys->push($p->class_id . '|' . $p->section_id));
        $pairKeys = $pairKeys->unique();

        $totalClasses = $pairKeys->count();

        $studentIds = collect();
        foreach ($pairKeys as $pairKey) {
            [$pairClassId, $pairSectionId] = explode('|', $pairKey);
            $q = Student::where('class_id', $pairClassId)->where('is_active', true);
            if ($pairSectionId !== '') {
                $q->where('section_id', $pairSectionId);
            }
            $studentIds = $studentIds->merge($q->pluck('id'));
        }
        $totalStudents = $studentIds->unique()->count();

        $pendingTests      = ClassTest::where('teacher_id', $teacherId)->whereIn('status', ['scheduled', 'ongoing'])->count();
        $activeAssignments = Assignment::where('teacher_id', $teacherId)->where('status', 'active')->count();

        $recentNotices = Notice::where('is_active', true)
            ->whereIn('for', ['all', 'teacher'])
            ->whereDate('publish_date', '<=', today())
            ->where(fn($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->orderBy('publish_date', 'desc')
            ->limit(5)
            ->get();

        return view('school-management::teachers.my-dashboard', compact(
            'teacherId', 'todaysPeriods', 'totalClasses', 'totalStudents',
            'pendingTests', 'activeAssignments', 'recentNotices', 'today'
        ));
    }

    // ── My Classes (self-service attendance for the logged-in teacher) ──────

    public function myClasses(Request $request)
    {
        $teacherId = auth()->user()->teacher_id ?? null;

        $date    = $request->date ?: now()->format('Y-m-d');
        $dayName = \Carbon\Carbon::parse($date)->format('l'); // Saturday, Sunday, ...

        $periods = collect();

        if ($teacherId) {
            $periods = ClassRoutine::with(['class', 'section', 'subject', 'room'])
                ->where('teacher_id', $teacherId)
                ->where('day', $dayName)
                ->orderBy('start_time')
                ->get()
                ->map(function ($routine) use ($teacherId, $date) {
                    $routine->has_class_test = ClassTest::where('teacher_id', $teacherId)
                        ->where('class_id', $routine->class_id)
                        ->where('subject_id', $routine->subject_id)
                        ->whereDate('test_date', $date)
                        ->exists();

                    $routine->has_assignment_due = Assignment::where('teacher_id', $teacherId)
                        ->where('class_id', $routine->class_id)
                        ->where('subject_id', $routine->subject_id)
                        ->whereDate('due_date', $date)
                        ->exists();

                    return $routine;
                });
        }

        return view('school-management::teachers.my-classes', compact('periods', 'date', 'dayName', 'teacherId'));
    }

    private function assertTeacherOwnsClass($teacherId, $classId, $sectionId)
    {
        $ownsViaRoutine = ClassRoutine::where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->exists();

        $ownsViaClassTeacher = TeacherClass::where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->exists();

        abort_unless($ownsViaRoutine || $ownsViaClassTeacher, 403);
    }

    public function myAttendance(Request $request, $classId)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        abort_unless($teacherId, 403);

        $sectionId = $request->section_id;
        $this->assertTeacherOwnsClass($teacherId, $classId, $sectionId);

        $class   = Classe::findOrFail($classId);
        $section = $sectionId ? Section::find($sectionId) : null;

        $date = $request->date ?: now()->format('Y-m-d');

        $studentQuery = Student::where('class_id', $classId)->where('is_active', true);
        if ($sectionId) {
            $studentQuery->where('section_id', $sectionId);
        }
        $students = $studentQuery->orderBy('roll_number')->orderBy('first_name')->get();

        $attendance = StudentAttendance::whereIn('student_id', $students->pluck('id'))
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        return view('school-management::teachers.my-attendance', compact('class', 'section', 'sectionId', 'students', 'attendance', 'date'));
    }

    public function myAttendanceStore(Request $request, $classId)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        abort_unless($teacherId, 403);

        $sectionId = $request->section_id;
        $this->assertTeacherOwnsClass($teacherId, $classId, $sectionId);

        $request->validate([
            'date'     => 'required|date|before_or_equal:today',
            'status'   => 'required|array',
            'status.*' => 'in:present,absent,late,excused',
        ]);
        if ($closed = \ME\SchoolManagement\Services\SchoolCalendar::closedReason($request->date)) {
            return back()->withInput()->with('error', "Attendance can't be taken on " . sm_date($request->date) . " — {$closed}.");
        }

        $before = StudentAttendance::whereIn('student_id', array_keys($request->status))->whereDate('date', $request->date)->pluck('status', 'student_id');
        foreach ($request->status as $studentId => $status) {
            $student = Student::where('id', $studentId)->where('class_id', $classId)->first();
            if (! $student) continue;
            if ($status === 'absent' && ($before[$studentId] ?? null) !== 'absent') {
                \ME\SchoolManagement\Services\SchoolNotifier::absence($student, $request->date);
            }

            StudentAttendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $request->date],
                [
                    'status'     => $status,
                    'class_id'   => $student->class_id,
                    'section_id' => $student->section_id,
                ]
            );
        }

        return back()->with('success', 'Attendance saved for ' . date('d M Y', strtotime($request->date)));
    }

    // ── My Schedule ───────────────────────────────────────────────────────

    public function mySchedule()
    {
        $teacherId = auth()->user()->teacher_id ?? null;

        $routines = $teacherId
            ? ClassRoutine::with(['class', 'section', 'subject', 'room'])
                ->where('teacher_id', $teacherId)
                ->orderBy('start_time')
                ->get()
            : collect();

        $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $scheduleByDay = [];
        foreach ($days as $day) {
            $scheduleByDay[$day] = $routines->where('day', $day)->sortBy('start_time')->values();
        }

        return view('school-management::teachers.my-schedule', compact('days', 'scheduleByDay', 'teacherId'));
    }

    // ── My Class Tests ────────────────────────────────────────────────────

    /** Class/subject pairs the teacher teaches: explicit assignments plus their routine periods. */
    private function myTeachingPairs($teacherId)
    {
        $assigned = TeacherSubject::with(['classe', 'subject'])->where('teacher_id', $teacherId)->get()
            ->map(fn($p) => (object) ['classe' => $p->classe, 'subject' => $p->subject]);
        $routine = ClassRoutine::with(['class', 'subject'])->where('teacher_id', $teacherId)->get()
            ->map(fn($p) => (object) ['classe' => $p->class, 'subject' => $p->subject]);

        return $assigned->concat($routine)
            ->filter(fn($p) => $p->classe && $p->subject)
            ->unique(fn($p) => $p->classe->id . '|' . $p->subject->id)
            ->values();
    }

    public function myClassTestIndex()
    {
        $teacherId = auth()->user()->teacher_id ?? null;

        $classTests = $teacherId
            ? ClassTest::with(['classe', 'subject'])->where('teacher_id', $teacherId)->orderBy('test_date', 'desc')->paginate(20)
            : collect();

        $teachingPairs = $teacherId ? $this->myTeachingPairs($teacherId) : collect();

        return view('school-management::teachers.my-class-tests', compact('classTests', 'teachingPairs', 'teacherId'));
    }

    public function myClassTestStore(Request $request)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        abort_unless($teacherId, 403);

        $request->validate([
            'title'       => 'required|string|max:255',
            'class_id'    => 'required|exists:classes,id',
            'subject_id'  => 'required|exists:subjects,id',
            'test_date'   => 'required|date',
            'total_marks' => 'required|numeric|min:1',
            'pass_marks'  => 'required|numeric|min:0|lte:total_marks',
        ]);
        $this->assertTeachesSubject($teacherId, $request->class_id, $request->subject_id);

        ClassTest::create(array_merge(
            $request->only(['title', 'class_id', 'subject_id', 'test_date', 'total_marks', 'pass_marks', 'duration', 'description']),
            ['teacher_id' => $teacherId, 'is_active' => true]
        ));

        return back()->with('success', 'Class test created successfully');
    }

    public function myClassTestUpdate(Request $request, $id)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        $test = ClassTest::where('teacher_id', $teacherId)->findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'class_id'    => 'required|exists:classes,id',
            'subject_id'  => 'required|exists:subjects,id',
            'test_date'   => 'required|date',
            'total_marks' => 'required|numeric|min:1',
            'pass_marks'  => 'required|numeric|min:0|lte:total_marks',
        ]);
        $this->assertTeachesSubject($teacherId, $request->class_id, $request->subject_id);

        $test->update($request->only(['title', 'class_id', 'subject_id', 'test_date', 'total_marks', 'pass_marks', 'duration', 'description']));

        return back()->with('success', 'Class test updated successfully');
    }

    public function myClassTestDestroy($id)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        ClassTest::where('teacher_id', $teacherId)->findOrFail($id)->delete();
        return back()->with('success', 'Class test deleted successfully');
    }

    public function myClassTestMarksIndex($id)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        $test = ClassTest::with(['classe', 'subject'])->where('teacher_id', $teacherId)->findOrFail($id);

        $students = Student::where('class_id', $test->class_id)->where('is_active', true)->orderBy('first_name')->get();
        $results  = ClassTestResult::where('class_test_id', $id)->get()->keyBy('student_id');

        return view('school-management::teachers.my-class-test-marks', compact('test', 'students', 'results'));
    }

    public function myClassTestMarksStore(Request $request, $id)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        $test = ClassTest::where('teacher_id', $teacherId)->findOrFail($id);

        $request->validate([
            'status.*' => 'in:present,absent',
            'marks.*'  => 'nullable|numeric|min:0|max:' . (float) $test->total_marks,
        ], ['marks.*.max' => 'Marks cannot exceed the total of ' . (float) $test->total_marks . '.']);
        $classStudentIds = Student::where('class_id', $test->class_id)->pluck('id')->flip();

        foreach ($request->status ?? [] as $studentId => $statusVal) {
            if (!isset($classStudentIds[$studentId])) continue;
            $obtainedMarks = $request->marks[$studentId] ?? null;
            $grade         = null;
            $marks         = null;
            $dbStatus      = 'absent';

            if ($statusVal !== 'absent') {
                if ($obtainedMarks !== null && $obtainedMarks !== '') {
                    $marks    = (float) $obtainedMarks;
                    $dbStatus = $marks >= (float) $test->pass_marks ? 'pass' : 'fail';
                    $grade    = \ME\SchoolManagement\Services\GradeScale::letter($marks, (float) $test->total_marks, (float) $test->pass_marks);
                } else {
                    $dbStatus = 'fail';
                }
            }

            ClassTestResult::updateOrCreate(
                ['class_test_id' => $id, 'student_id' => $studentId],
                [
                    'obtained_marks' => $marks,
                    'grade'          => $grade,
                    'status'         => $dbStatus,
                    'remarks'        => $request->remarks[$studentId] ?? null,
                ]
            );
        }

        if (in_array($test->status, ['scheduled', 'ongoing'])) {
            $test->update(['status' => 'completed']);
        }

        return back()->with('success', 'Marks saved successfully.');
    }

    // ── My Assignments ────────────────────────────────────────────────────

    public function myAssignmentIndex()
    {
        $teacherId = auth()->user()->teacher_id ?? null;

        $assignments = $teacherId
            ? Assignment::with(['classe', 'subject'])->where('teacher_id', $teacherId)->orderBy('due_date', 'desc')->paginate(20)
            : collect();

        $teachingPairs = $teacherId ? $this->myTeachingPairs($teacherId) : collect();

        return view('school-management::teachers.my-assignments', compact('assignments', 'teachingPairs', 'teacherId'));
    }

    public function myAssignmentStore(Request $request)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        abort_unless($teacherId, 403);

        $request->validate([
            'title'         => 'required|string|max:255',
            'class_id'      => 'required|exists:classes,id',
            'subject_id'    => 'required|exists:subjects,id',
            'assigned_date' => 'required|date',
            'due_date'      => 'required|date|after_or_equal:assigned_date',
            'total_marks'   => 'nullable|numeric|min:0',
        ]);
        $this->assertTeachesSubject($teacherId, $request->class_id, $request->subject_id);

        Assignment::create(array_merge(
            $request->only(['title', 'description', 'class_id', 'subject_id', 'assigned_date', 'due_date', 'total_marks']),
            ['teacher_id' => $teacherId, 'is_active' => true, 'status' => 'active']
        ));

        return back()->with('success', 'Assignment created successfully');
    }

    public function myAssignmentUpdate(Request $request, $id)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        $assignment = Assignment::where('teacher_id', $teacherId)->findOrFail($id);

        $request->validate([
            'title'         => 'required|string|max:255',
            'class_id'      => 'required|exists:classes,id',
            'subject_id'    => 'required|exists:subjects,id',
            'assigned_date' => 'required|date',
            'due_date'      => 'required|date|after_or_equal:assigned_date',
            'total_marks'   => 'nullable|numeric|min:0',
        ]);
        $this->assertTeachesSubject($teacherId, $request->class_id, $request->subject_id);

        $assignment->update($request->only(['title', 'description', 'class_id', 'subject_id', 'assigned_date', 'due_date', 'total_marks']));

        return back()->with('success', 'Assignment updated successfully');
    }

    public function myAssignmentDestroy($id)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        Assignment::where('teacher_id', $teacherId)->findOrFail($id)->delete();
        return back()->with('success', 'Assignment deleted successfully');
    }

    public function myAssignmentSubmissionsIndex($id)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        $assignment = Assignment::with(['classe', 'subject'])->where('teacher_id', $teacherId)->findOrFail($id);

        $students = Student::where('class_id', $assignment->class_id)->where('is_active', true)->orderBy('first_name')->get();
        $submissions = AssignmentSubmission::where('assignment_id', $id)->get()->keyBy('student_id');

        return view('school-management::teachers.my-assignment-submissions', compact('assignment', 'students', 'submissions'));
    }

    public function myAssignmentSubmissionsStore(Request $request, $id)
    {
        $teacherId = auth()->user()->teacher_id ?? null;
        $assignment = Assignment::where('teacher_id', $teacherId)->findOrFail($id);

        $request->validate([
            'status.*' => 'in:submitted,late,not_submitted',
            'marks.*'  => 'nullable|numeric|min:0' . ($assignment->total_marks > 0 ? '|max:' . (float) $assignment->total_marks : ''),
        ]);
        $classStudentIds = Student::where('class_id', $assignment->class_id)->pluck('id')->flip();

        foreach ($request->status ?? [] as $studentId => $status) {
            if (!isset($classStudentIds[$studentId])) continue;
            $marks = $request->marks[$studentId] ?? null;
            $grade = null;

            if ($marks !== null && $marks !== '' && $assignment->total_marks > 0) {
                $grade = \ME\SchoolManagement\Services\GradeScale::letter((float) $marks, (float) $assignment->total_marks);
            }

            AssignmentSubmission::updateOrCreate(
                ['assignment_id' => $id, 'student_id' => $studentId],
                [
                    'status'         => $status,
                    'obtained_marks' => ($marks !== null && $marks !== '') ? (float)$marks : null,
                    'grade'          => $grade,
                    'submitted_at'   => in_array($status, ['submitted', 'late'])
                        ? ($request->submitted_at[$studentId] ?? now()->toDateString())
                        : null,
                    'remarks'        => $request->remarks[$studentId] ?? null,
                ]
            );
        }

        return back()->with('success', 'Submissions saved successfully.');
    }

    private function assertTeachesSubject($teacherId, $classId, $subjectId): void
    {
        $teaches = TeacherSubject::where('teacher_id', $teacherId)
                ->where('subject_id', $subjectId)->where('class_id', $classId)->exists()
            || ClassRoutine::where('teacher_id', $teacherId)
                ->where('subject_id', $subjectId)->where('class_id', $classId)->exists();
        abort_unless($teaches, 403, 'You do not teach this subject in this class.');
    }

    private function teacherRules($ignoreId = null): array
    {
        return [
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'gender'            => 'required|in:male,female,other,Male,Female,Other',
            'date_of_birth'     => 'required|date|before:today',
            'religion'          => 'required|string|max:100',
            'email'             => 'required|email|max:255|unique:teachers,email' . ($ignoreId ? ",$ignoreId" : ''),
            'phone'             => 'required|string|max:50',
            'present_address'   => 'required|string',
            'permanent_address' => 'required|string',
            'joining_date'      => 'required|date',
            'nid'               => 'nullable|string|max:50',
            'designation'       => 'nullable|string|max:255',
            'qualification'     => 'nullable|string|max:255',
            'experience'        => 'nullable|string|max:255',
            'salary'            => 'nullable|numeric|min:0',
        ];
    }

    private function newTeacherCode(): string
    {
        // Same format as existing records: TCH-<year>-0001
        $prefix = 'TCH-' . date('Y') . '-';
        $last = Teacher::withTrashed()->where('teacher_id', 'like', $prefix . '%')->orderByDesc('teacher_id')->value('teacher_id');
        $n = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;
        do {
            $code = $prefix . str_pad($n++, 4, '0', STR_PAD_LEFT);
        } while (Teacher::withTrashed()->where('teacher_id', $code)->exists());
        return $code;
    }

    // ── My Notices ────────────────────────────────────────────────────────

    public function myNotices()
    {
        $notices = Notice::where('is_active', true)
            ->whereIn('for', ['all', 'teacher'])
            ->whereDate('publish_date', '<=', today())
            ->where(fn($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            ->orderBy('publish_date', 'desc')
            ->paginate(20);

        return view('school-management::teachers.my-notices', compact('notices'));
    }
}
