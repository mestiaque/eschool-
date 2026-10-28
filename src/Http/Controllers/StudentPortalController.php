<?php

namespace ME\SchoolManagement\Http\Controllers;

use ME\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use ME\SchoolManagement\Models\BookIssue;
use ME\SchoolManagement\Models\Exam;
use ME\SchoolManagement\Models\FeeCollection;
use ME\SchoolManagement\Models\Notice;
use ME\SchoolManagement\Models\Result;
use ME\SchoolManagement\Models\SchoolSetting;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentAttendance;
use ME\SchoolManagement\Models\StudentDiscount;

class StudentPortalController extends Controller
{
    private function getSchoolName(): string
    {
        return SchoolSetting::where('key', 'school_name')->value('value') ?? config('app.name', 'School');
    }

    private function portalStudent(): ?Student
    {
        $id = session('portal_student_id');
        if (!$id) return null;
        // A deactivated (e.g. transferred) student loses portal access immediately.
        return Student::with(['classe', 'section', 'guardian'])->where('is_active', true)->find($id);
    }

    private function requirePortalStudent()
    {
        $student = $this->portalStudent();
        if (!$student) abort(redirect()->route('portal.login'));
        return $student;
    }

    public function login()
    {
        if (session('portal_student_id')) {
            return redirect()->route('portal.dashboard');
        }
        return view('school-management::portal.login', [
            'schoolName' => $this->getSchoolName(),
        ]);
    }

    public function loginPost(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string',
            'portal_pin' => 'required|string',
        ]);

        // PINs are short, so throttle guesses per student ID and per IP.
        $key = 'portal-login:' . strtolower(trim($request->student_id)) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $mins = ceil(RateLimiter::availableIn($key) / 60);
            return back()->with('error', "Too many attempts. Try again in {$mins} minute(s).")->withInput($request->only('student_id'));
        }

        $student = Student::where('student_id', trim($request->student_id))
            ->where('is_active',   true)
            ->first();

        if (!$student || !$student->portal_pin || !\Illuminate\Support\Facades\Hash::check(trim($request->portal_pin), $student->portal_pin)) {
            RateLimiter::hit($key, 15 * 60);
            return back()->with('error', 'Invalid Student ID or PIN.')->withInput($request->only('student_id'));
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        session(['portal_student_id' => $student->id]);
        return redirect()->route('portal.dashboard');
    }

    public function logout(Request $request)
    {
        session()->forget('portal_student_id');
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }

    public function dashboard()
    {
        $portalStudent = $this->portalStudent();
        if (!$portalStudent) return redirect()->route('portal.login');

        $dueFees = FeeCollection::where('student_id', $portalStudent->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('due_amount');

        $latestResult = Result::where('student_id', $portalStudent->id)
            ->with('exam.academicYear', 'exam.examType')
            ->where('is_published', true)
            ->latest()
            ->first();

        $currentBooksQuery = BookIssue::where('student_id', $portalStudent->id)
            ->where('status', 'issued')
            ->with('book', 'copy');

        $activeIssues  = $currentBooksQuery->count();
        $currentBooks  = $currentBooksQuery->get();

        $attTotal   = StudentAttendance::where('student_id', $portalStudent->id)->count();
        // Late still counts as attended (same rule as the admin dashboard).
        $attPresent = StudentAttendance::where('student_id', $portalStudent->id)->whereIn('status', ['present', 'late'])->count();
        $attendancePct = $attTotal > 0 ? round(($attPresent / $attTotal) * 100) : 0;

        $recentNotices = Notice::where('is_active', true)
            ->whereDate('publish_date', '<=', today())
            ->where(fn($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            // The notice form saves 'student'; 'students' kept for older rows.
            ->where(fn($q) => $q->whereIn('for', ['all', 'student', 'students'])
                ->orWhere('class_id', $portalStudent->class_id))
            ->latest('publish_date')
            ->take(5)
            ->get();

        return view('school-management::portal.dashboard', compact(
            'portalStudent', 'dueFees', 'latestResult',
            'activeIssues', 'currentBooks', 'attendancePct', 'recentNotices'
        ));
    }

    public function fees()
    {
        $portalStudent = $this->portalStudent();
        if (!$portalStudent) return redirect()->route('portal.login');

        $fees = FeeCollection::where('student_id', $portalStudent->id)
            ->with('feeType', 'academicYear')
            ->orderByDesc('year')->orderByDesc('month')
            ->paginate(20);

        $totalPaid   = FeeCollection::where('student_id', $portalStudent->id)->sum('paid_amount');
        $totalDue    = FeeCollection::where('student_id', $portalStudent->id)->sum('due_amount');
        $discounts   = StudentDiscount::where('student_id', $portalStudent->id)
            ->where('is_active', true)->with('feeType')->get();
        // Actual taka waived on invoices (discount_amount may be a percentage).
        $totalWaived = FeeCollection::where('student_id', $portalStudent->id)->sum('discount');

        return view('school-management::portal.fees', compact(
            'portalStudent', 'fees', 'totalPaid', 'totalDue', 'totalWaived', 'discounts'
        ));
    }

    public function results()
    {
        $portalStudent = $this->portalStudent();
        if (!$portalStudent) return redirect()->route('portal.login');

        $results = Result::where('student_id', $portalStudent->id)
            ->with('exam.examType', 'exam.academicYear')
            ->where('is_published', true)
            ->orderByDesc('id')
            ->get();

        return view('school-management::portal.results', compact('portalStudent', 'results'));
    }

    public function marksheet($exam_id)
    {
        $portalStudent = $this->portalStudent();
        if (!$portalStudent) return redirect()->route('portal.login');

        $result = Result::where('student_id', $portalStudent->id)
            ->where('exam_id', $exam_id)
            ->where('is_published', true)
            ->first();

        if (!$result) {
            return redirect()->route('portal.results')->with('error', 'Marksheet not yet published.');
        }

        $exam    = Exam::with('academicYear', 'examType')->findOrFail($exam_id);
        $student = $portalStudent->load('classe', 'section', 'guardian');
        $sheet   = \ME\SchoolManagement\Services\ResultSheet::marksheet($student, $exam);

        return view('school-management::portal.marksheet', compact('student', 'exam', 'sheet'));
    }

    public function library()
    {
        $portalStudent = $this->portalStudent();
        if (!$portalStudent) return redirect()->route('portal.login');

        $activeIssues = BookIssue::where('student_id', $portalStudent->id)
            ->where('status', 'issued')
            ->with('book', 'copy')
            ->orderByDesc('issue_date')
            ->get();

        $history = BookIssue::where('student_id', $portalStudent->id)
            ->where('status', '!=', 'issued')
            ->with('book', 'copy')
            ->orderByDesc('issue_date')
            ->paginate(20);

        return view('school-management::portal.library', compact('portalStudent', 'activeIssues', 'history'));
    }

    public function notices()
    {
        $portalStudent = $this->portalStudent();
        if (!$portalStudent) return redirect()->route('portal.login');

        $notices = Notice::where('is_active', true)
            ->whereDate('publish_date', '<=', today())
            ->where(fn($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
            // The notice form saves 'student'; 'students' kept for older rows.
            ->where(fn($q) => $q->whereIn('for', ['all', 'student', 'students'])
                ->orWhere('class_id', $portalStudent->class_id))
            ->orderByDesc('publish_date')
            ->paginate(15);

        return view('school-management::portal.notices', compact('portalStudent', 'notices'));
    }

    public function attendance()
    {
        $portalStudent = $this->portalStudent();
        if (!$portalStudent) return redirect()->route('portal.login');

        $selectedYear   = (int) request('year', date('Y'));
        $availableYears = range(date('Y'), max(date('Y') - 5, 2020));

        $totalDays   = StudentAttendance::where('student_id', $portalStudent->id)
            ->whereYear('date', $selectedYear)->count();
        $presentDays = StudentAttendance::where('student_id', $portalStudent->id)
            ->whereYear('date', $selectedYear)->where('status', 'present')->count();
        $absentDays  = StudentAttendance::where('student_id', $portalStudent->id)
            ->whereYear('date', $selectedYear)->where('status', 'absent')->count();
        $lateDays    = StudentAttendance::where('student_id', $portalStudent->id)
            ->whereYear('date', $selectedYear)->where('status', 'late')->count();
        // Late still counts as attended (same rule as the admin dashboard).
        $attendancePct = $totalDays > 0 ? round((($presentDays + $lateDays) / $totalDays) * 100) : 0;

        $monthly = StudentAttendance::where('student_id', $portalStudent->id)
            ->whereYear('date', $selectedYear)
            ->selectRaw('MONTH(date) as month, COUNT(*) as total,
                SUM(status="present") as present,
                SUM(status="absent") as absent,
                SUM(status="late") as late')
            ->groupByRaw('MONTH(date)')
            ->orderBy('month')
            ->get();

        return view('school-management::portal.attendance', compact(
            'portalStudent', 'totalDays', 'presentDays', 'absentDays',
            'attendancePct', 'monthly', 'selectedYear', 'availableYears'
        ));
    }
}
