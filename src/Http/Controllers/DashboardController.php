<?php

namespace ME\SchoolManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\Exam;
use ME\SchoolManagement\Models\Expense;
use ME\SchoolManagement\Models\ExpenseCategory;
use ME\SchoolManagement\Models\FeeCollection;
use ME\SchoolManagement\Models\FeePayment;
use ME\SchoolManagement\Models\Income;
use ME\SchoolManagement\Models\Notice;
use ME\SchoolManagement\Models\Result;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\Teacher;
use ME\SchoolManagement\Models\TeacherLeave;
use ME\SchoolManagement\Models\Book;
use ME\SchoolManagement\Models\BookCopy;
use ME\SchoolManagement\Models\BookIssue;
use ME\SchoolManagement\Models\Event;
use ME\SchoolManagement\Models\Holiday;
use ME\SchoolManagement\Models\Product;
use ME\SchoolManagement\Models\SalaryPayment;
use ME\SchoolManagement\Models\Section;
use ME\SchoolManagement\Models\Staff;
use ME\SchoolManagement\Models\StudentAttendance;
use ME\SchoolManagement\Models\StudentDocument;
use ME\SchoolManagement\Models\StudentEarlyExit;
use ME\SchoolManagement\Models\StudentLeave;
use ME\SchoolManagement\Models\StudentTransfer;
use ME\SchoolManagement\Models\TeacherAttendance;
use ME\SchoolManagement\Models\TransportRoute;
use ME\SchoolManagement\Models\Vehicle;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->check() && auth()->user()->teacher_id) {
            return redirect()->route('my.dashboard');
        }

        $academicYear = AcademicYear::where('is_current', true)->first();
        $year  = now()->year;
        $today = today();

        // ── KPI tiles ────────────────────────────────────────────────────
        $students        = Student::where('is_active', true)->count();
        $newAdmissions   = Student::where('is_active', true)->whereYear('admission_date', $year)->count();
        $teachers        = Teacher::where('is_active', true)->count();
        $staffCount      = Staff::where('is_active', true)->count();
        $classes         = Classe::where('is_active', true)->count();
        $sections        = Section::where('is_active', true)->count();

        // Today's attendance, or the most recent day it was taken.
        $attDate  = StudentAttendance::whereDate('date', '<=', $today)->max('date');
        $attDay   = $attDate ? StudentAttendance::whereDate('date', $attDate) : null;
        $attMarked  = $attDay ? (clone $attDay)->count() : 0;
        $attPresent = $attDay ? (clone $attDay)->whereIn('status', ['present', 'late'])->count() : 0;
        $attendance = [
            'date'    => $attDate ? \Carbon\Carbon::parse($attDate) : null,
            'isToday' => $attDate && \Carbon\Carbon::parse($attDate)->isSameDay($today),
            'marked'  => $attMarked,
            'present' => $attPresent,
            'absent'  => $attMarked - $attPresent,
            'rate'    => $attMarked ? round($attPresent / $attMarked * 100, 1) : null,
        ];

        $feeThisMonth = (float) FeePayment::whereYear('date', $year)->whereMonth('date', now()->month)->sum('amount');
        $feeLastMonth = (float) FeePayment::whereYear('date', now()->subMonthNoOverflow()->year)
            ->whereMonth('date', now()->subMonthNoOverflow()->month)->sum('amount');
        $todayFee     = (float) FeePayment::whereDate('date', $today)->sum('amount');

        $totalDueFee      = (float) FeeCollection::where('status', '!=', 'paid')->sum('due_amount');
        $studentsWithDues = FeeCollection::where('status', '!=', 'paid')->where('due_amount', '>', 0)->distinct()->count('student_id');

        // ── Income vs expense by month (this year) ───────────────────────
        $byMonth = fn($query, $dateCol, $sumCol) => $query->whereYear($dateCol, $year)
            ->selectRaw("MONTH($dateCol) as m, SUM($sumCol) as total")
            ->groupBy(DB::raw("MONTH($dateCol)"))->pluck('total', 'm');
        $fees     = $byMonth(FeePayment::query(), 'date', 'amount');
        $other    = $byMonth(Income::query(), 'date', 'amount');
        $expense  = $byMonth(Expense::query(), 'date', 'amount');

        $months = range(1, 12);
        $finance = [
            'labels'  => array_map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)), $months),
            'income'  => array_map(fn($m) => round(($fees[$m] ?? 0) + ($other[$m] ?? 0), 2), $months),
            'expense' => array_map(fn($m) => round($expense[$m] ?? 0, 2), $months),
        ];
        $yearIncome  = array_sum($finance['income']);
        $yearExpense = array_sum($finance['expense']);

        // ── Fee billing for the current academic year ────────────────────
        $billing = FeeCollection::when($academicYear, fn($q) => $q->where('academic_year_id', $academicYear->id))
            ->selectRaw('SUM(amount + fine - discount) as billed, SUM(paid_amount) as collected, SUM(due_amount) as due, COUNT(*) as invoices')
            ->first();
        $feeSummary = [
            'billed'    => (float) $billing->billed,
            'collected' => (float) $billing->collected,
            'due'       => (float) $billing->due,
            'invoices'  => (int) $billing->invoices,
            'rate'      => $billing->billed > 0 ? round($billing->collected / $billing->billed * 100, 1) : null,
            'overdue'   => FeeCollection::where('status', '!=', 'paid')->whereNotNull('due_date')->whereDate('due_date', '<', $today)->count(),
        ];

        // ── Attendance trend: last 15 days attendance was taken ──────────
        $attTrend = StudentAttendance::whereDate('date', '<=', $today)
            ->selectRaw("DATE(date) as d, COUNT(*) as total, SUM(status IN ('present','late')) as present")
            ->groupBy(DB::raw('DATE(date)'))->orderByDesc('d')->limit(15)->get()->reverse()->values();
        $attendanceTrend = [
            'labels' => $attTrend->map(fn($r) => \Carbon\Carbon::parse($r->d)->format('d M'))->all(),
            'rate'   => $attTrend->map(fn($r) => $r->total ? round($r->present / $r->total * 100, 1) : 0)->all(),
            'counts' => $attTrend->map(fn($r) => (int) $r->present . ' of ' . (int) $r->total)->all(),
        ];

        // ── Students by class and gender ─────────────────────────────────
        $classRows = Classe::where('is_active', true)->orderBy('numeric_name')->get(['id', 'name']);
        $genderCounts = Student::where('is_active', true)
            ->selectRaw('class_id, LOWER(gender) as g, COUNT(*) as c')
            ->groupBy('class_id', DB::raw('LOWER(gender)'))->get()
            ->groupBy('class_id');
        $enrolment = [
            'labels' => $classRows->pluck('name')->all(),
            'male'   => $classRows->map(fn($c) => (int) optional($genderCounts->get($c->id))->where('g', 'male')->sum('c'))->all(),
            'female' => $classRows->map(fn($c) => (int) optional($genderCounts->get($c->id))->where('g', 'female')->sum('c'))->all(),
            'other'  => $classRows->map(fn($c) => (int) optional($genderCounts->get($c->id))->whereNotIn('g', ['male', 'female'])->sum('c'))->all(),
        ];

        // ── Spending by category (this year), salaries included ──────────
        $spend = Expense::whereYear('date', $year)
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->selectRaw('expense_categories.name as name, SUM(expenses.amount) as total')
            ->groupBy('expense_categories.name')->pluck('total', 'name');
        $spend = $spend->map(fn($v) => round((float) $v, 2))->sortDesc();
        // Keep the chart readable: top 7, the rest folded into "Other".
        if ($spend->count() > 8) {
            $spend = $spend->take(7)->put('Other', round($spend->slice(7)->sum(), 2));
        }
        $spending = ['labels' => $spend->keys()->all(), 'values' => $spend->values()->all()];

        // ── Latest published-or-generated exam results ──────────────────
        $latestExamId = Result::orderByDesc('updated_at')->value('exam_id');
        $examSummary = null;
        if ($latestExamId) {
            $res   = Result::where('exam_id', $latestExamId)->get(['grade', 'gpa']);
            $order = ['A+', 'A', 'A-', 'B', 'C', 'D', 'F'];
            $dist  = $res->groupBy('grade')->map->count();
            $grades = collect($order)->merge($dist->keys()->diff($order)->filter())->values();
            $examSummary = [
                'exam'     => Exam::find($latestExamId),
                'total'    => $res->count(),
                'passRate' => $res->count() ? round($res->where('gpa', '>', 0)->count() / $res->count() * 100, 1) : 0,
                'avgGpa'   => round((float) $res->where('gpa', '>', 0)->avg('gpa'), 2),
                'labels'   => $grades->all(),
                'values'   => $grades->map(fn($g) => (int) ($dist[$g] ?? 0))->all(),
            ];
        }

        // ── Things waiting for someone ───────────────────────────────────
        $pending = collect([
            ['label' => 'Teacher leave requests', 'count' => TeacherLeave::where('status', 'pending')->count(),       'route' => 'teachers.leaves',      'icon' => 'fa-calendar-minus'],
            ['label' => 'Student leave requests', 'count' => StudentLeave::where('status', 'pending')->count(),       'route' => 'students.leaves',      'icon' => 'fa-user-clock'],
            ['label' => 'Early exit requests',    'count' => StudentEarlyExit::where('status', 'pending')->count(),   'route' => 'students.early-exits', 'icon' => 'fa-door-open'],
            ['label' => 'Transfer requests',      'count' => StudentTransfer::where('status', 'pending')->count(),    'route' => 'students.transfer',    'icon' => 'fa-exchange-alt'],
            ['label' => 'Overdue fee invoices',   'count' => $feeSummary['overdue'],                                  'route' => 'fees.collection',      'icon' => 'fa-file-invoice-dollar'],
            ['label' => 'Overdue library books',  'count' => BookIssue::where('status', 'issued')->whereDate('due_date', '<', $today)->count(), 'route' => 'library.issues', 'icon' => 'fa-book'],
            ['label' => 'Low-stock items',        'count' => Product::where('is_active', true)->whereColumn('quantity', '<=', 'minimum_stock')->where('minimum_stock', '>', 0)->count(), 'route' => 'inventory.products', 'icon' => 'fa-boxes'],
            ['label' => 'Routine periods without a teacher', 'count' => \ME\SchoolManagement\Models\ClassRoutine::whereNull('teacher_id')->count(), 'route' => 'academic.routines', 'icon' => 'fa-chalkboard-teacher'],
            ['label' => 'Unverified documents',   'count' => StudentDocument::where('is_verified', false)->count(),   'route' => 'students.documents',   'icon' => 'fa-file-alt'],
        ]);

        // ── Coming up: exams, events, holidays ───────────────────────────
        $upcoming = collect()
            ->concat(Exam::where('is_active', true)->whereDate('end_date', '>=', $today)->orderBy('start_date')->limit(5)->get()
                ->map(fn($e) => ['type' => 'Exam', 'icon' => 'fa-file-alt', 'title' => $e->name, 'date' => $e->start_date, 'end' => $e->end_date, 'route' => route('exams.index')]))
            ->concat(Event::where('is_active', true)->whereDate('start_date', '>=', $today)->orderBy('start_date')->limit(5)->get()
                ->map(fn($e) => ['type' => 'Event', 'icon' => 'fa-calendar-day', 'title' => $e->title, 'date' => $e->start_date, 'end' => $e->end_date, 'route' => route('events.index')]))
            ->concat(Holiday::where('is_active', true)->whereDate('date', '>=', $today)->orderBy('date')->limit(5)->get()
                ->map(fn($h) => ['type' => 'Holiday', 'icon' => 'fa-umbrella-beach', 'title' => $h->title, 'date' => $h->date, 'end' => null, 'route' => route('holidays.index')]))
            ->sortBy(fn($i) => $i['date'])->take(6)->values();

        $notices = Notice::where('is_active', true)
            ->whereDate('publish_date', '<=', $today)
            ->where(fn($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today))
            ->orderByDesc('publish_date')->limit(5)->get();

        $recentPayments = FeePayment::with('feeCollection.student', 'feeCollection.feeType')
            ->orderByDesc('date')->orderByDesc('id')->limit(6)->get();

        // ── Module snapshot ──────────────────────────────────────────────
        $modules = [
            'library' => [
                'books'   => Book::count(),
                'copies'  => BookCopy::count(),
                'issued'  => BookCopy::where('status', 'issued')->count(),
                'available' => BookCopy::where('status', 'available')->count(),
            ],
            'transport' => [
                'routes'   => TransportRoute::where('is_active', true)->count(),
                'vehicles' => Vehicle::where('is_active', true)->count(),
            ],
            'inventory' => [
                'products' => Product::where('is_active', true)->count(),
                'value'    => (float) Product::where('is_active', true)->selectRaw('SUM(quantity * COALESCE(price,0)) as v')->value('v'),
            ],
            'teacherAttendance' => [
                'present' => TeacherAttendance::whereDate('date', $today)->whereIn('status', ['present', 'late'])->count(),
                'marked'  => TeacherAttendance::whereDate('date', $today)->count(),
            ],
        ];

        return view('school-management::dashboard.index', compact(
            'academicYear', 'students', 'newAdmissions', 'teachers', 'staffCount', 'classes', 'sections',
            'attendance', 'feeThisMonth', 'feeLastMonth', 'todayFee', 'totalDueFee', 'studentsWithDues',
            'finance', 'yearIncome', 'yearExpense', 'feeSummary', 'attendanceTrend', 'enrolment',
            'spending', 'examSummary', 'pending', 'upcoming', 'notices', 'recentPayments', 'modules'
        ));
    }

    // Reports
    public function studentReport(Request $request)
    {
        $query = Student::with(['classe', 'section']);
        if ($request->class_id) $query->where('class_id', $request->class_id);
        if ($request->gender) $query->where('gender', $request->gender);
        $students = $query->orderBy('first_name')->get();
        $classes = Classe::where('is_active', true)->get();
        return view('school-management::reports.students', compact('students', 'classes'));
    }

    public function studentReportPrint(Request $request)
    {
        $query = Student::with(['classe', 'section']);
        if ($request->class_id) $query->where('class_id', $request->class_id);
        if ($request->gender) $query->where('gender', $request->gender);
        $students = $query->orderBy('first_name')->get();
        $classes = Classe::where('is_active', true)->get();
        return view('school-management::reports.students-print', compact('students', 'classes'));
    }

    public function feeReport(Request $request)
    {
        $query = FeeCollection::with(['student.classe', 'feeType']);
        if ($request->class_id) $query->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
        if ($request->status) $query->where('status', $request->status);
        if ($request->from_date) $query->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('created_at', '<=', $request->to_date);

        $collections = $query->get();
        $classes = Classe::where('is_active', true)->get();
        return view('school-management::reports.fee', compact('collections', 'classes'));
    }

    public function feeReportPrint(Request $request)
    {
        $query = FeeCollection::with(['student.classe', 'feeType']);
        if ($request->class_id) $query->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
        if ($request->status) $query->where('status', $request->status);
        if ($request->from_date) $query->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('created_at', '<=', $request->to_date);

        $collections = $query->get();
        $classes = Classe::where('is_active', true)->get();
        return view('school-management::reports.fee-print', compact('collections', 'classes'));
    }

    public function expenseReport(Request $request)
    {
        $query = Expense::with('expenseCategory');
        if ($request->category_id) $query->where('expense_category_id', $request->category_id);
        if ($request->from_date) $query->whereDate('date', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('date', '<=', $request->to_date);

        $expenses = $query->get();
        $categories = ExpenseCategory::where('is_active', true)->get();
        return view('school-management::reports.expenses', compact('expenses', 'categories'));
    }

    public function expenseReportPrint(Request $request)
    {
        $query = Expense::with('expenseCategory');
        if ($request->category_id) $query->where('expense_category_id', $request->category_id);
        if ($request->from_date) $query->whereDate('date', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('date', '<=', $request->to_date);

        $expenses = $query->get();
        $categories = ExpenseCategory::where('is_active', true)->get();
        return view('school-management::reports.expenses-print', compact('expenses', 'categories'));
    }

    public function attendanceReport(Request $request)
    {
        $classes = Classe::where('is_active', true)->get();
        $students = collect();
        if ($request->class_id) {
            $students = Student::with(['attendances' => function($q) use ($request) {
                if ($request->month) $q->whereMonth('date', $request->month);
                if ($request->year) $q->whereYear('date', $request->year);
            }])->where('class_id', $request->class_id)->get();
        }
        return view('school-management::reports.attendance', compact('students', 'classes'));
    }

    public function examReport(Request $request)
    {
        $classes = Classe::where('is_active', true)->get();
        $exams = Exam::where('is_active', true)->orderBy('id', 'desc')->get();
        $results = collect();
        if ($request->exam_id && $request->class_id) {
            $results = Result::with(['student', 'exam', 'classe'])
                ->where('exam_id', $request->exam_id)
                ->where('class_id', $request->class_id)
                ->get();
        }
        return view('school-management::reports.exam', compact('results', 'classes', 'exams'));
    }

    public function financialReport()
    {
        $totalIncome = Income::sum('amount');
        $totalExpense = Expense::sum('amount');
        $totalFeeCollection = FeePayment::sum('amount');
        // Salary payments are posted to expenses, so they are shown but not added again.
        $totalSalary = \ME\SchoolManagement\Models\SalaryPayment::sum('net_salary');
        $profit = ($totalIncome + $totalFeeCollection) - $totalExpense;

        $monthlyIncome = FeePayment::whereYear('date', now()->year)
            ->selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->groupBy(DB::raw('MONTH(date)'))->pluck('total', 'month');
        $monthlyExpenses = Expense::whereYear('date', now()->year)
            ->selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->groupBy(DB::raw('MONTH(date)'))->pluck('total', 'month');

        return view('school-management::reports.financial', compact(
            'totalIncome', 'totalExpense', 'totalFeeCollection', 'totalSalary', 'profit',
            'monthlyIncome', 'monthlyExpenses'
        ));
    }
}
