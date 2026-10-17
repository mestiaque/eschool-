<?php

namespace ME\SchoolManagement\Http\Controllers;

use Illuminate\Http\Request;
use ME\Http\Controllers\Controller;
use ME\SchoolManagement\Models\Assignment;
use ME\SchoolManagement\Models\AssignmentSubmission;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\Subject;
use ME\SchoolManagement\Models\Teacher;
use ME\SchoolManagement\Models\AcademicYear;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Assignment::with(['classe', 'subject', 'teacher', 'academicYear']);

        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $assignments = $query->orderBy('due_date', 'desc')->paginate(20);
        $classes = Classe::where('is_active', true)->get();
        $subjects = Subject::where('is_active', true)->get();
        $teachers = Teacher::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        return view('school-management::academic.assignments.index', compact(
            'assignments', 'classes', 'subjects', 'teachers', 'academicYears'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateAssignment($request);

        $data['academic_year_id'] = AcademicYear::where('is_current', true)->value('id');
        Assignment::create($data);
        return back()->with('success', 'Assignment created successfully');
    }

    public function update(Request $request, $id)
    {
        $data = $this->validateAssignment($request);

        Assignment::findOrFail($id)->update($data);
        return back()->with('success', 'Assignment updated successfully');
    }

    public function destroy($id)
    {
        Assignment::findOrFail($id)->delete();
        return back()->with('success', 'Assignment deleted successfully');
    }

    public function submissionsIndex($id)
    {
        $assignment  = Assignment::with(['classe', 'subject', 'academicYear'])->findOrFail($id);
        $students    = Student::where('class_id', $assignment->class_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();
        $submissions = AssignmentSubmission::where('assignment_id', $id)
            ->get()
            ->keyBy('student_id');

        return view('school-management::academic.assignment-submissions', compact('assignment', 'students', 'submissions'));
    }

    public function submissionsStore(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);

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
                $pct   = ((float)$marks / (float)$assignment->total_marks) * 100;
                $grade = $pct >= 80 ? 'A+' : ($pct >= 70 ? 'A' : ($pct >= 60 ? 'B' : ($pct >= 50 ? 'C' : ($pct >= 40 ? 'D' : 'F'))));
            }

            AssignmentSubmission::updateOrCreate(
                ['assignment_id' => $id, 'student_id' => $studentId],
                [
                    'status'       => $status,
                    'obtained_marks' => ($marks !== null && $marks !== '') ? (float)$marks : null,
                    'grade'        => $grade,
                    'submitted_at' => in_array($status, ['submitted', 'late'])
                        ? ($request->submitted_at[$studentId] ?? now()->toDateString())
                        : null,
                    'remarks'      => $request->remarks[$studentId] ?? null,
                ]
            );
        }

        return back()->with('success', 'Submissions saved successfully.');
    }

    private function validateAssignment(Request $request): array
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'class_id'      => 'required|exists:classes,id',
            'subject_id'    => 'required|exists:subjects,id',
            'teacher_id'    => 'nullable|exists:teachers,id',
            'assigned_date' => 'required|date',
            'due_date'      => 'required|date|after_or_equal:assigned_date',
            'total_marks'   => 'nullable|numeric|min:0',
            'status'        => 'nullable|in:active,closed,cancelled',
        ]);
        $data['total_marks'] = $data['total_marks'] ?? 100;
        $data['status']      = $data['status'] ?? 'active';
        return $data;
    }
}
