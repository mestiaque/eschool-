<?php

namespace ME\SchoolManagement\Http\Controllers;

use ME\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\BookIssue;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\FeeCollection;
use ME\SchoolManagement\Models\FeeType;
use ME\SchoolManagement\Models\Guardian;
use ME\SchoolManagement\Models\Holiday;
use ME\SchoolManagement\Models\LeaveType;
use ME\SchoolManagement\Models\Result;
use ME\SchoolManagement\Models\Section;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentAttendance;
use ME\SchoolManagement\Models\StudentDiscount;
use ME\SchoolManagement\Models\StudentDocument;
use ME\SchoolManagement\Models\StudentEarlyExit;
use ME\SchoolManagement\Models\StudentLeave;
use ME\SchoolManagement\Models\StudentMark;
use ME\SchoolManagement\Models\StudentPromotion;
use ME\SchoolManagement\Models\StudentTransfer;
use ME\SchoolManagement\Models\Subject;
use ME\SchoolManagement\Models\SubjectGroup;
use ME\SchoolManagement\Models\TransportRoute;
use ME\SchoolManagement\Services\SchoolNotifier;
use ME\SchoolManagement\Services\SchoolCalendar;

class StudentController extends Controller
{
    // ── Student List / Index ───────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Student::with(['classe', 'section', 'academicYear']);

        if ($request->class_id)         $query->where('class_id', $request->class_id);
        if ($request->section_id)       $query->where('section_id', $request->section_id);
        if ($request->academic_year_id) $query->where('academic_year_id', $request->academic_year_id);
        if ($request->status !== null && $request->status !== '') $query->where('is_active', $request->status);
        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('first_name',  'like', "%$s%")
                ->orWhere('last_name',   'like', "%$s%")
                ->orWhere('student_id',  'like', "%$s%")
                ->orWhere('phone',       'like', "%$s%")
                ->orWhere('roll_number', 'like', "%$s%"));
        }

        $students      = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();
        $classes       = Classe::where('is_active', true)->get();
        $sections      = Section::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $feeTypes      = FeeType::where('is_active', true)->get();
        $subjectGroups = SubjectGroup::where('is_active', true)->orderBy('name')->get();
        $transportRoutes = TransportRoute::with('stops')->where('is_active', true)->orderBy('route_name')->get();

        return view('school-management::students.index',
            compact('students', 'classes', 'sections', 'academicYears', 'feeTypes', 'subjectGroups', 'transportRoutes'));
    }

    public function admission()
    {
        return redirect()->route('students.index', ['admit' => 1]);
    }

    // ── Store (Admit) ──────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'student_id'     => 'nullable|string|max:100|unique:students,student_id',
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'gender'         => 'required|in:male,female,other',
            'date_of_birth'  => 'required|date',
            'class_id'       => 'required|exists:classes,id',
            'admission_date' => 'nullable|date',
            'guardian_phone' => 'nullable|string',
            'religion'       => 'nullable|string',
            'phone'          => 'nullable|string',
            'present_address'   => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'section_id'        => 'nullable|exists:sections,id',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
            'email'             => 'nullable|email|max:255',
            'photo'             => 'nullable|image|max:2048',
            'documents.*'       => 'file|max:5120',
            'waiver_amount'     => 'nullable|numeric|min:0',
            'portal_pin'        => 'nullable|digits_between:4,10',
            'transport_route_id' => 'nullable|exists:transport_routes,id',
            'route_stop_id'      => ['nullable', \Illuminate\Validation\Rule::exists('route_stops', 'id')->where('route_id', $request->transport_route_id)],
        ], ['route_stop_id.exists' => 'The pick-up stop is not on the chosen route.']);

        // Use the given Student ID (for entering an already-existing student), otherwise auto-generate one.
        if ($request->filled('student_id')) {
            $sid = $request->student_id;
        } else {
            // Continue from the highest existing number (a count breaks after deletions).
            $prefix = 'STU-' . date('Y') . '-';
            $last = Student::withTrashed()->where('student_id', 'like', $prefix . '%')->orderByDesc('student_id')->value('student_id');
            $n    = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;
            do {
                $sid = $prefix . str_pad($n++, 4, '0', STR_PAD_LEFT);
            } while (Student::withTrashed()->where('student_id', $sid)->exists());
        }

        // Handle photo upload
        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo')->store('students/photos', 'public');
        }

        $student = Student::create([
            'student_id'          => $sid,
            'first_name'          => $request->first_name,
            'last_name'           => $request->last_name,
            'gender'              => $request->gender,
            'date_of_birth'       => $request->date_of_birth,
            'birth_certificate_no'=> $request->birth_certificate_no,
            'religion'            => $request->religion,
            'blood_group'         => $request->blood_group,
            'nationality'         => $request->nationality ?? 'Bangladeshi',
            'photo'               => $photo,
            'email'               => $request->email,
            'phone'               => $request->phone,
            'present_address'     => $request->present_address,
            'permanent_address'   => $request->permanent_address,
            'class_id'            => $request->class_id,
            'section_id'          => $request->section_id ?: null,
            'subject_group_id'    => $request->subject_group_id ?: null,
            'optional_subject_id' => $request->optional_subject_id ?: null,
            'academic_year_id'    => $request->academic_year_id ?: AcademicYear::where('is_current', true)->value('id'),
            'shift'               => $request->shift,
            'transport_route_id'  => $request->transport_route_id ?: null,
            'route_stop_id'       => $request->transport_route_id ? ($request->route_stop_id ?: null) : null,
            'roll_number'         => $request->roll_number,
            'registration_number' => $request->registration_number,
            'previous_school'     => $request->previous_school,
            'previous_class'      => $request->previous_class,
            'admission_date'      => $request->admission_date,
            'is_active'           => true,
            'portal_pin'          => $pin = ($request->portal_pin ?: (string) random_int(100000, 999999)),
        ]);

        // Guardian
        Guardian::create([
            'student_id'         => $student->id,
            'father_name'        => $request->father_name,
            'father_phone'       => $request->father_phone,
            'mother_name'        => $request->mother_name,
            'mother_phone'       => $request->mother_phone,
            'guardian_name'      => $request->guardian_name,
            'guardian_relation'  => $request->guardian_relation,
            'guardian_phone'     => $request->guardian_phone,
            'guardian_email'     => $request->guardian_email,
        ]);

        // Fee Waiver / Discount
        if ($request->filled('waiver_amount') && (float)$request->waiver_amount > 0) {
            StudentDiscount::create([
                'student_id'       => $student->id,
                'fee_type_id'      => $request->waiver_fee_type_id ?: null,
                'discount_amount'  => $request->waiver_amount,
                'discount_type'    => $request->waiver_type ?? 'fixed',
                'is_active'        => true,
            ]);
        }

        // Documents upload
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $i => $file) {
                $path = $file->store('students/documents', 'public');
                StudentDocument::create([
                    'student_id'  => $student->id,
                    'title'       => $request->doc_titles[$i] ?? $file->getClientOriginalName(),
                    'type'        => $request->doc_types[$i] ?? 'other',
                    'file'        => $path,
                    'description' => $request->doc_descriptions[$i] ?? null,
                ]);
            }
        }

        $sent = $this->sendPin($student, $pin);

        // The PIN is stored hashed, so this message is the only time it is shown.
        return back()->with('success', "Student admitted. ID: $sid — Portal PIN: $pin (shown once"
            . ($sent ? '; sent to guardian by SMS' : '; note it down') . ')');
    }

    // ── Show ───────────────────────────────────────────────────────────────

    public function show($id)
    {
        $student = Student::with([
            'classe', 'section', 'guardian', 'academicYear',
            'documents', 'discounts.feeType',
            'feeCollections.feeType',
            'attendances',
            'results.exam',
            'marks.subject', 'marks.exam',
        ])->findOrFail($id);

        $classes       = Classe::where('is_active', true)->get();
        $sections      = Section::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $feeTypes      = FeeType::where('is_active', true)->get();
        $subjectGroups = SubjectGroup::where('is_active', true)->orderBy('name')->get();
        $docTypes      = StudentDocument::types();
        $transportRoutes = TransportRoute::with('stops')->where('is_active', true)->orderBy('route_name')->get();

        $libraryIssues = BookIssue::with('book', 'copy')
            ->where('student_id', $id)
            ->orderBy('issue_date', 'desc')
            ->get();

        return view('school-management::students.show',
            compact('student', 'classes', 'sections', 'academicYears', 'feeTypes', 'subjectGroups', 'docTypes', 'libraryIssues', 'transportRoutes'));
    }

    // ── Update ─────────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'gender'           => 'required|in:male,female,other',
            'date_of_birth'    => 'required|date',
            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'nullable|exists:sections,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'email'            => 'nullable|email|max:255',
            'photo'            => 'nullable|image|max:2048',
            'admission_date'   => 'nullable|date',
            'transport_route_id' => 'nullable|exists:transport_routes,id',
            'route_stop_id'      => ['nullable', \Illuminate\Validation\Rule::exists('route_stops', 'id')->where('route_id', $request->transport_route_id)],
        ], ['route_stop_id.exists' => 'The pick-up stop is not on the chosen route.']);

        $student = Student::findOrFail($id);

        $photo = $student->photo;
        if ($request->hasFile('photo')) {
            if ($photo) Storage::disk('public')->delete($photo);
            $photo = $request->file('photo')->store('students/photos', 'public');
        }

        $student->update([
            'first_name'           => $request->first_name,
            'last_name'            => $request->last_name,
            'gender'               => $request->gender,
            'date_of_birth'        => $request->date_of_birth,
            'birth_certificate_no' => $request->birth_certificate_no,
            'religion'             => $request->religion,
            'blood_group'          => $request->blood_group,
            'nationality'          => $request->nationality ?: ($student->nationality ?: 'Bangladeshi'),
            'photo'                => $photo,
            'email'                => $request->email,
            'phone'                => $request->phone,
            'present_address'      => $request->present_address,
            'permanent_address'    => $request->permanent_address,
            'class_id'             => $request->class_id,
            'section_id'           => $request->section_id ?: null,
            'subject_group_id'     => $request->subject_group_id ?: null,
            'optional_subject_id'  => $request->optional_subject_id ?: null,
            'academic_year_id'     => $request->academic_year_id ?: null,
            'shift'                => $request->shift,
            'transport_route_id'   => $request->transport_route_id ?: null,
            'route_stop_id'        => $request->transport_route_id ? ($request->route_stop_id ?: null) : null,
            'roll_number'          => $request->roll_number,
            'registration_number'  => $request->registration_number,
            'previous_school'      => $request->previous_school,
            'previous_class'       => $request->previous_class,
            'admission_date'       => $request->admission_date,
            'is_active'            => $request->has('is_active') ? $request->boolean('is_active') : $student->is_active,
        ]);

        // Guardian update
        $guardianData = [
            'father_name'       => $request->father_name,
            'father_phone'      => $request->father_phone,
            'mother_name'       => $request->mother_name,
            'mother_phone'      => $request->mother_phone,
            'guardian_name'     => $request->guardian_name,
            'guardian_relation' => $request->guardian_relation,
            'guardian_phone'    => $request->guardian_phone,
            'guardian_email'    => $request->guardian_email,
        ];
        if ($student->guardian) {
            $student->guardian->update($guardianData);
        } else {
            Guardian::create(array_merge($guardianData, ['student_id' => $student->id]));
        }

        return back()->with('success', 'Student updated successfully.');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        if (function_exists('me_change_log')) {
            me_change_log("Student moved to Recycle Bin: {$student->full_name} ({$student->student_id})")->watch($student)->delete(fn () => $student->delete());
        } else {
            $student->delete();
        }
        return redirect()->route('students.index')->with('success', 'Student moved to the Recycle Bin. It can be restored from Settings → Recycle Bin.');
    }

    // ── Documents ──────────────────────────────────────────────────────────

    public function documentStore(Request $request, $studentId)
    {
        $request->validate([
            'file'  => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png,webp,doc,docx',
            'type'  => 'required|string|max:50',
            'title' => 'required|string|max:200',
        ]);

        $student = Student::findOrFail($studentId);
        $path    = $request->file('file')->store('students/documents', 'public');

        StudentDocument::create([
            'student_id'  => $student->id,
            'title'       => $request->title,
            'type'        => $request->type,
            'file'        => $path,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    // Route is /{studentId}/documents/{docId}: parameters arrive positionally.
    public function documentVerify($studentId, $docId)
    {
        StudentDocument::where('student_id', $studentId)->findOrFail($docId)->update([
            'is_verified' => true,
            'verified_by' => auth()->user()->name ?? 'Admin',
        ]);
        return back()->with('success', 'Document verified.');
    }

    public function documentDestroy($studentId, $docId)
    {
        $doc = StudentDocument::where('student_id', $studentId)->findOrFail($docId);
        Storage::disk('public')->delete($doc->file);
        $doc->delete();
        return back()->with('success', 'Document deleted.');
    }

    // ── Portal PIN ─────────────────────────────────────────────────────────

    public function setPortalPin(Request $request, $id)
    {
        $request->validate(['portal_pin' => 'nullable|digits_between:4,10']);
        $student = Student::findOrFail($id);
        // Blank = generate a random 6-digit PIN.
        $pin = $request->portal_pin ?: (string) random_int(100000, 999999);
        $student->update(['portal_pin' => $pin]);
        $sent = $this->sendPin($student, $pin, $request->boolean('send_sms'));

        return back()->with('success', "Portal PIN set to $pin (shown once"
            . ($sent ? '; sent to guardian by SMS' : '') . ').');
    }

    private function sendPin(Student $student, string $pin, bool $force = false): bool
    {
        if (!$force && !SchoolNotifier::enabled('notify_new_pin')) return false;
        $url = route('portal.login');
        return SchoolNotifier::sms(SchoolNotifier::guardianPhone($student),
            "Student portal login for {$student->full_name}: ID {$student->student_id}, PIN {$pin}. {$url}\n- " . SchoolNotifier::schoolName());
    }

    // ── Attendance ─────────────────────────────────────────────────────────

    public function attendanceIndex(Request $request)
    {
        $year  = (int) ($request->year  ?? date('Y'));
        $month = (int) ($request->month ?? date('m'));

        $classes       = Classe::where('is_active', true)->get();
        $sections      = collect();
        $students      = collect();
        $attendanceMap = [];
        $daysInMonth   = 0;
        $holidays      = collect();
        $weekendDays   = SchoolCalendar::weekendDays();

        if ($request->class_id) {
            $sections = Section::where('class_id', $request->class_id)->where('is_active', true)->get();

            $studentQuery = Student::with('section')->where('class_id', $request->class_id)->where('is_active', true);
            if ($request->section_id) $studentQuery->where('section_id', $request->section_id);
            $students = $studentQuery->orderBy('roll_number')->orderBy('first_name')->get();

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $startDate   = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $endDate     = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-$daysInMonth";

            $attendances = StudentAttendance::whereIn('student_id', $students->pluck('id'))
                ->whereBetween('date', [$startDate, $endDate])->get();

            foreach ($attendances as $att) {
                $attendanceMap[$att->student_id][(int) date('j', strtotime($att->date))] = [
                    'status' => $att->status, 'note' => $att->note, 'id' => $att->id,
                ];
            }

            // Holidays and extra school days, keyed by day of month.
            $holidays = collect(SchoolCalendar::exceptions($startDate, $endDate))
                ->keyBy(fn($h) => (int) date('j', strtotime($h->date)));
        }

        return view('school-management::students.attendance', compact(
            'classes', 'sections', 'students', 'attendanceMap',
            'year', 'month', 'daysInMonth', 'holidays', 'weekendDays'
        ));
    }

    public function attendancePrint(Request $request)
    {
        $year  = (int) ($request->year  ?? date('Y'));
        $month = (int) ($request->month ?? date('m'));

        $classes       = Classe::where('is_active', true)->get();
        $sections      = collect();
        $students      = collect();
        $attendanceMap = [];
        $daysInMonth   = 0;
        $holidays      = collect();
        $weekendDays   = SchoolCalendar::weekendDays();

        if ($request->class_id) {
            $sections = Section::where('class_id', $request->class_id)->where('is_active', true)->get();

            $studentQuery = Student::with('section')->where('class_id', $request->class_id)->where('is_active', true);
            if ($request->section_id) $studentQuery->where('section_id', $request->section_id);
            $students = $studentQuery->orderBy('roll_number')->orderBy('first_name')->get();

            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $startDate   = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $endDate     = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-$daysInMonth";

            $attendances = StudentAttendance::whereIn('student_id', $students->pluck('id'))
                ->whereBetween('date', [$startDate, $endDate])->get();

            foreach ($attendances as $att) {
                $attendanceMap[$att->student_id][(int) date('j', strtotime($att->date))] = [
                    'status' => $att->status, 'note' => $att->note, 'id' => $att->id,
                ];
            }

            // Holidays and extra school days, keyed by day of month.
            $holidays = collect(SchoolCalendar::exceptions($startDate, $endDate))
                ->keyBy(fn($h) => (int) date('j', strtotime($h->date)));
        }

        return view('school-management::students.attendance-print', compact(
            'classes', 'sections', 'students', 'attendanceMap',
            'year', 'month', 'daysInMonth', 'holidays', 'weekendDays'
        ));
    }

    public function attendanceStore(Request $request)
    {
        $request->validate([
            'date'          => 'required|date|before_or_equal:today',
            'class_id'      => 'required|exists:classes,id',
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'status.*'      => 'nullable|in:present,absent,late,excused',
        ], ['student_ids.required' => 'Select at least one student.']);
        if ($closed = SchoolCalendar::closedReason($request->date)) {
            return back()->withInput()->with('error', "Attendance can't be taken on " . sm_date($request->date) . " — {$closed}.");
        }

        $before = StudentAttendance::whereIn('student_id', $request->student_ids)->whereDate('date', $request->date)->pluck('status', 'student_id');
        foreach ($request->student_ids as $studentId) {
            if (($request->status[$studentId] ?? 'present') === 'absent' && ($before[$studentId] ?? null) !== 'absent'
                && $absentee = Student::with('guardian')->find($studentId)) {
                SchoolNotifier::absence($absentee, $request->date);
            }
            StudentAttendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $request->date],
                [
                    'status'     => $request->status[$studentId] ?? 'present',
                    'class_id'   => $request->class_id,
                    'section_id' => $request->section_id,
                ]
            );
        }
        return back()->with('success', 'Attendance saved for ' . date('d M Y', strtotime($request->date)));
    }

    public function attendanceCellUpdate(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'date'       => 'required|date|before_or_equal:today',
            'status'     => 'required|in:present,absent,late,excused',
        ]);
        if ($closed = SchoolCalendar::closedReason($request->date)) {
            return response()->json(['success' => false, 'message' => "No attendance on " . sm_date($request->date) . " — {$closed}."], 422);
        }
        $student = Student::findOrFail($request->student_id);
        $before = StudentAttendance::where('student_id', $student->id)->whereDate('date', $request->date)->value('status');
        $att = StudentAttendance::updateOrCreate(
            ['student_id' => $request->student_id, 'date' => $request->date],
            [
                'status'     => $request->status,
                'note'       => $request->note,
                'class_id'   => $student->class_id,
                'section_id' => $student->section_id,
            ]
        );
        if ($att->status === 'absent' && $before !== 'absent') SchoolNotifier::absence($student, $request->date);
        return response()->json(['success' => true, 'status' => $att->status, 'id' => $att->id]);
    }

    // ── Promotions ─────────────────────────────────────────────────────────

    public function promotionIndex(Request $request)
    {
        $classes       = Classe::where('is_active', true)->get();
        $sections      = Section::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $students      = collect();

        if ($request->filled('from_class') && $request->filled('from_year')) {
            $students = Student::where('class_id', $request->from_class)
                ->where('academic_year_id', $request->from_year)
                ->where('is_active', true)
                ->orderBy('roll_number')->orderBy('first_name')->get();
        }

        return view('school-management::students.promotion',
            compact('classes', 'sections', 'academicYears', 'students'));
    }

    public function getPromotionStudents(Request $request)
    {
        return Student::where('class_id', $request->from_class)
            ->where('academic_year_id', $request->from_year)
            ->where('is_active', true)->get();
    }

    public function promotionStore(Request $request)
    {
        $request->validate([
            'from_class' => 'required|exists:classes,id',
            'from_year'  => 'required|exists:academic_years,id',
            'to_class'   => 'required|exists:classes,id',
            'to_year'    => 'required|exists:academic_years,id',
            'to_sections.*'  => 'nullable|exists:sections,id',
            'mark_status.*'  => 'nullable|in:promoted,detained',
        ]);
        if ($request->from_class == $request->to_class && $request->from_year == $request->to_year) {
            return back()->with('error', 'Target class and year must differ from the current ones.');
        }

        if (empty($request->student_ids)) {
            return back()->with('error', 'No students selected for promotion.');
        }

        $promoted = 0;
        $kept = 0;
        \Illuminate\Support\Facades\DB::beginTransaction();
        foreach ($request->student_ids as $studentId) {
            // Only students who are actually in the source class/year.
            $student = Student::where('class_id', $request->from_class)
                ->where('academic_year_id', $request->from_year)->find($studentId);
            if (!$student) continue;

            $markStatus = $request->mark_status[$studentId]   ?? 'promoted';
            $detained   = $markStatus === 'detained';
            // A detained student repeats the same class (and keeps its section,
            // since the section picker lists the target class's sections).
            $toClass    = $detained ? $student->class_id : $request->to_class;
            $toSection  = $detained ? $student->section_id : ($request->to_sections[$studentId] ?? $student->section_id);
            $newRoll    = ($request->roll_numbers[$studentId] ?? null) ?: $student->roll_number;

            StudentPromotion::updateOrCreate(
                ['student_id' => $studentId, 'from_academic_year_id' => $request->from_year],
                [
                    'from_class_id'       => $request->from_class,
                    'to_class_id'         => $toClass,
                    'from_section_id'     => $student->section_id,
                    'to_section_id'       => $toSection,
                    'to_academic_year_id' => $request->to_year,
                    'from_roll'           => $student->roll_number,
                    'to_roll'             => $newRoll,
                    'is_promoted'         => $markStatus === 'promoted',
                ]
            );

            // Both move to the new academic year; otherwise a detained student
            // drops out of next year's class lists and monthly fee generation.
            $student->update([
                'class_id'         => $toClass,
                'section_id'       => $toSection,
                'academic_year_id' => $request->to_year,
                'roll_number'      => $newRoll,
            ]);
            $detained ? $kept++ : $promoted++;
        }

        \Illuminate\Support\Facades\DB::commit();

        return back()->with('success', "$promoted student(s) promoted"
            . ($kept ? ", $kept detained in the same class for the new year" : '') . '.');
    }

    // ── Transfer ───────────────────────────────────────────────────────────

    public function transferIndex(Request $request)
    {
        $transfers = StudentTransfer::with('student')->orderBy('id', 'desc')->paginate(20);
        return view('school-management::students.transfer', compact('transfers'));
    }

    public function transferStore(Request $request)
    {
        $data = $request->validate([
            'student_id'    => 'required|exists:students,id',
            'from_school'   => 'nullable|string|max:255',
            'to_school'     => 'required|string|max:255',
            'transfer_date' => 'required|date',
            'reason'        => 'nullable|string',
        ]);
        if (StudentTransfer::where('student_id', $data['student_id'])->where('status', 'pending')->exists()) {
            return back()->with('error', 'This student already has a pending transfer.');
        }
        StudentTransfer::create($data + ['status' => 'pending']);
        return back()->with('success', 'Transfer request created');
    }

    public function transferApprove($id)
    {
        $transfer = StudentTransfer::where('status', 'pending')->findOrFail($id);
        \Illuminate\Support\Facades\DB::transaction(function () use ($transfer) {
            $transfer->update(['status' => 'approved']);
            $transfer->student?->update(['is_active' => false]);
        });
        return back()->with('success', 'Transfer approved; student marked inactive');
    }

    public function transferReject($id)
    {
        StudentTransfer::where('status', 'pending')->findOrFail($id)->update(['status' => 'rejected']);
        return back()->with('success', 'Transfer rejected');
    }

    // ── ID Card ────────────────────────────────────────────────────────────

    public function idCardIndex()
    {
        $students = Student::with(['classe', 'section'])->orderBy('id', 'desc')->paginate(20);
        return view('school-management::students.idcards', compact('students'));
    }

    public function idCard($id)
    {
        $student = Student::with('classe', 'section', 'guardian')->findOrFail($id);
        return view('school-management::students.idcard', compact('student'));
    }

    // ── Documents Index (standalone) ───────────────────────────────────────

    public function documentsIndex(Request $request)
    {
        $query = StudentDocument::with('student');
        if ($request->student_id) $query->where('student_id', $request->student_id);
        $documents = $query->orderBy('id', 'desc')->paginate(20);
        return view('school-management::students.documents', compact('documents'));
    }

    // ── Leave ──────────────────────────────────────────────────────────────

    public function leaveIndex()
    {
        $leaves     = StudentLeave::with(['student', 'leaveType'])->orderBy('id', 'desc')->paginate(20);
        $leaveTypes = LeaveType::where('is_active', true)->get();
        return view('school-management::students.leaves', compact('leaves', 'leaveTypes'));
    }

    public function leaveStore(Request $request)
    {
        $request->validate([
            'student_id'    => 'required|exists:students,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string',
        ]);
        StudentLeave::create([
            'student_id'    => $request->student_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'reason'        => $request->reason,
            'status'        => 'pending',
        ]);
        return back()->with('success', 'Leave application submitted');
    }

    public function leaveApprove($id)
    {
        StudentLeave::where('status', 'pending')->findOrFail($id)->update(['status' => 'approved', 'approved_by' => auth()->id()]);
        return back()->with('success', 'Leave approved');
    }

    public function leaveReject($id)
    {
        StudentLeave::where('status', 'pending')->findOrFail($id)->update(['status' => 'rejected', 'approved_by' => auth()->id()]);
        return back()->with('success', 'Leave rejected');
    }

    // ── Early Exit ─────────────────────────────────────────────────────────

    public function earlyExitIndex()
    {
        $exits    = StudentEarlyExit::with('student')->orderBy('id', 'desc')->paginate(20);
        return view('school-management::students.early-exits', compact('exits'));
    }

    public function earlyExitStore(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'exit_date'  => 'required|date',
            'exit_time'  => 'required',
            'reason'     => 'required|string',
        ]);
        StudentEarlyExit::create([
            'student_id'    => $request->student_id,
            'exit_date'     => $request->exit_date,
            'exit_time'     => $request->exit_time,
            'reason'        => $request->reason,
            'picked_up_by'  => $request->picked_up_by,
            'status'        => 'pending',
        ]);
        return back()->with('success', 'Early exit request submitted');
    }

    public function earlyExitApprove($id)
    {
        StudentEarlyExit::findOrFail($id)->update(['status' => 'approved', 'approved_by' => auth()->id()]);
        return back()->with('success', 'Early exit approved');
    }

    public function earlyExitReject($id)
    {
        StudentEarlyExit::findOrFail($id)->update(['status' => 'rejected', 'approved_by' => auth()->id()]);
        return back()->with('success', 'Early exit rejected');
    }

    public function earlyExitDestroy($id)
    {
        StudentEarlyExit::findOrFail($id)->delete();
        return back()->with('success', 'Early exit record deleted');
    }

    // ── AJAX helpers ───────────────────────────────────────────────────────

    /** Student search for the searchable picker (select2 format, 20 per page). */
    public function search(Request $request)
    {
        $q = trim((string) $request->q);
        $page = max(1, (int) $request->page);
        $query = Student::with('classe')->where('is_active', true)
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('first_name', 'like', "%$q%")
                ->orWhere('last_name', 'like', "%$q%")
                ->orWhere('student_id', 'like', "%$q%")
                ->orWhere('roll_number', $q)
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%$q%"])))
            ->orderBy('first_name');
        $total = (clone $query)->count();
        $rows  = $query->skip(($page - 1) * 20)->take(20)->get();

        return response()->json([
            'results'    => $rows->map(fn ($st) => ['id' => $st->id, 'text' => "{$st->full_name} ({$st->student_id}) – " . ($st->classe->name ?? '')]),
            'pagination' => ['more' => $page * 20 < $total],
        ]);
    }

    public function getStudent($id)
    {
        return Student::with('classe', 'section', 'guardian')->findOrFail($id);
    }
}
