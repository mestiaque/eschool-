<?php

namespace ME\SchoolManagement\Http\Controllers;

use Illuminate\Http\Request;
use ME\Http\Controllers\Controller;
use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\ClassTest;
use ME\SchoolManagement\Models\ClassTestResult;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\Subject;
use ME\SchoolManagement\Models\Teacher;

class ClassTextController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassTest::with(['classe', 'subject', 'teacher', 'academicYear']);

        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $classTests = $query->orderBy('test_date', 'desc')->paginate(20);
        $classes = Classe::where('is_active', true)->get();
        $subjects = Subject::where('is_active', true)->get();
        $teachers = Teacher::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        return view('school-management::academic.class-tests', compact(
            'classTests', 'classes', 'subjects', 'teachers', 'academicYears'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateTest($request);

        $data['academic_year_id'] = AcademicYear::where('is_current', true)->value('id');
        ClassTest::create($data);
        return back()->with('success', 'Class Test created successfully');
    }

    public function update(Request $request, $id)
    {
        $data = $this->validateTest($request);

        ClassTest::findOrFail($id)->update($data);
        return back()->with('success', 'Class Test updated successfully');
    }

    public function destroy($id)
    {
        ClassTest::findOrFail($id)->delete();
        return back()->with('success', 'Class Test deleted successfully');
    }

    public function marksIndex($id)
    {
        $test     = ClassTest::with(['classe', 'subject', 'academicYear'])->findOrFail($id);
        $students = Student::where('class_id', $test->class_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();
        $results  = ClassTestResult::where('class_test_id', $id)
            ->get()
            ->keyBy('student_id');

        return view('school-management::academic.class-test-marks', compact('test', 'students', 'results'));
    }

    public function marksStore(Request $request, $id)
    {
        $test = ClassTest::findOrFail($id);

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

    private function validateTest(Request $request): array
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'class_id'    => 'required|exists:classes,id',
            'subject_id'  => 'required|exists:subjects,id',
            'teacher_id'  => 'nullable|exists:teachers,id',
            'test_date'   => 'required|date',
            'total_marks' => 'required|numeric|min:1',
            'pass_marks'  => 'required|numeric|min:0|lte:total_marks',
            'duration'    => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'status'      => 'nullable|in:scheduled,ongoing,completed,cancelled',
        ]);
        $data['status'] = $data['status'] ?? 'scheduled';
        return $data;
    }
}
