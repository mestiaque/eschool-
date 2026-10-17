<?php

namespace ME\SchoolManagement\Http\Controllers;

use ME\Http\Controllers\Controller;
use ME\SchoolManagement\Http\Controllers\Concerns\GuardsDeletes;
use Illuminate\Http\Request;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\SchoolTour;
use ME\SchoolManagement\Models\Staff;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\Teacher;
use ME\SchoolManagement\Models\TourExpense;
use ME\SchoolManagement\Models\TourFeeCategory;
use ME\SchoolManagement\Models\TourRegistration;

class TourController extends Controller
{
    use GuardsDeletes;

    public function index()
    {
        $tours = SchoolTour::withCount('registrations')
            ->withSum('expenses', 'amount')
            ->orderBy('start_date', 'desc')
            ->paginate(20);
        return view('school-management::academic.tours.index', compact('tours'));
    }

    public function store(Request $request)
    {
        SchoolTour::create($this->validateTour($request));
        return back()->with('success', 'Tour added successfully');
    }

    public function update(Request $request, $id)
    {
        SchoolTour::findOrFail($id)->update($this->validateTour($request));
        return back()->with('success', 'Tour updated successfully');
    }

    public function destroy($id)
    {
        $tour = SchoolTour::findOrFail($id);
        return $this->deleteUnlessUsed($tour, [
            'paid registrations' => TourRegistration::where('tour_id', $id)->where('paid_amount', '>', 0)->count(),
            'recorded expenses'  => $this->uses('tour_expenses', 'tour_id', $id),
        ], $tour->title, 'Tour deleted successfully');
    }

    public function show($id)
    {
        $tour = SchoolTour::with([
            'registrations.student.classe',
            'registrations.student.section',
            'registrations.teacher',
            'registrations.staff',
            'registrations.feeCategory',
            'expenses',
            'feeCategories.classe',
        ])->findOrFail($id);

        $classes  = Classe::where('is_active', true)->orderBy('name')->get();
        $registeredStudentIds = $tour->registrations->where('participant_type','student')->pluck('student_id')->toArray();
        $registeredTeacherIds = $tour->registrations->where('participant_type','teacher')->pluck('teacher_id')->toArray();
        $registeredStaffIds   = $tour->registrations->where('participant_type','staff')->pluck('staff_id')->toArray();

        $expenseCategories = [
            'bus_fare'      => 'বাস ভাড়া (Bus Fare)',
            'venue_rent'    => 'ভেন্যু ভাড়া (Venue Rent)',
            'food'          => 'খাবার (Food & Refreshment)',
            'accommodation' => 'আবাসন (Accommodation)',
            'entry_fee'     => 'প্রবেশ ফি (Entry Fee)',
            'guide_fee'     => 'গাইড ফি (Guide Fee)',
            'fuel'          => 'জ্বালানি (Fuel)',
            'medical'       => 'চিকিৎসা (Medical)',
            'other'         => 'অন্যান্য (Other)',
        ];

        $expenseByCategory = $tour->expenses
            ->groupBy('category')
            ->map(fn($items) => $items->sum('amount'));

        $staffDesignations = Staff::designations();

        return view('school-management::academic.tours.show', compact(
            'tour', 'classes',
            'registeredStudentIds', 'registeredTeacherIds', 'registeredStaffIds',
            'expenseCategories', 'expenseByCategory', 'staffDesignations'
        ));
    }

    // ── Fee Categories ─────────────────────────────────────────────────────

    public function storeFeeCategory(Request $request, $id)
    {
        SchoolTour::findOrFail($id);
        $request->validate([
            'category_label' => 'required|string|max:255',
            'fee'            => 'required|numeric|min:0',
            'class_id'       => 'nullable|exists:classes,id',
        ]);
        $max = TourFeeCategory::where('tour_id', $id)->max('serial') ?? 0;
        TourFeeCategory::create([
            'tour_id'        => $id,
            'category_type'  => $request->category_type ?? 'custom',
            'category_label' => $request->category_label,
            'class_id'       => $request->class_id ?: null,
            'fee'            => $request->fee,
            'serial'         => $max + 1,
        ]);
        return back()->with('success', 'Fee category added.');
    }

    public function updateFeeCategory(Request $request, $id, $catId)
    {
        $request->validate([
            'category_label' => 'required|string|max:255',
            'fee'            => 'required|numeric|min:0',
            'serial'         => 'nullable|integer|min:0',
        ]);
        TourFeeCategory::where('tour_id', $id)->findOrFail($catId)->update(
            array_filter($request->only(['category_type','category_label','class_id','fee','serial']), fn($v) => $v !== null)
        );
        return back()->with('success', 'Fee category updated.');
    }

    public function destroyFeeCategory($id, $catId)
    {
        TourFeeCategory::where('tour_id', $id)->findOrFail($catId)->delete();
        return back()->with('success', 'Fee category deleted.');
    }

    public function getFeeCategories($id)
    {
        $cats = TourFeeCategory::where('tour_id', $id)->orderBy('serial')->get();
        return response()->json($cats);
    }

    // ── Registrations ──────────────────────────────────────────────────────

    public function storeRegistration(Request $request, $id)
    {
        $tour = SchoolTour::findOrFail($id);
        $request->validate([
            'participant_type'     => 'nullable|in:student,teacher,staff',
            'fee'                  => 'nullable|numeric|min:0',
            'tour_fee_category_id' => ['nullable', \Illuminate\Validation\Rule::exists('tour_fee_categories', 'id')->where('tour_id', $tour->id)],
            'student_ids.*'        => 'exists:students,id',
            'teacher_ids.*'        => 'exists:teachers,id',
            'staff_ids.*'          => 'exists:staff,id',
        ]);
        $type = $request->participant_type ?? 'student';
        // A chosen fee category sets the fee unless one was typed in.
        $fee  = $request->filled('fee') ? $request->fee
            : (float) TourFeeCategory::whereKey($request->tour_fee_category_id)->value('fee');
        $added = 0;

        $base = [
            'registration_date'    => now()->toDateString(),
            'fee'                  => $fee,
            'payment_status'       => 'unpaid',
            'paid_amount'          => 0,
            'is_approved'          => false,
            'tour_fee_category_id' => $request->tour_fee_category_id ?: null,
            'participant_type'     => $type,
        ];

        if ($type === 'student') {
            $request->validate(['student_ids' => 'required|array']);
            foreach ($request->student_ids as $studentId) {
                $reg = TourRegistration::firstOrCreate(
                    ['tour_id' => $tour->id, 'student_id' => $studentId],
                    array_merge($base, ['student_id' => $studentId])
                );
                if ($reg->wasRecentlyCreated) $added++;
            }
        } elseif ($type === 'teacher') {
            $request->validate(['teacher_ids' => 'required|array']);
            foreach ($request->teacher_ids as $teacherId) {
                $reg = TourRegistration::firstOrCreate(
                    ['tour_id' => $tour->id, 'teacher_id' => $teacherId],
                    array_merge($base, ['teacher_id' => $teacherId])
                );
                if ($reg->wasRecentlyCreated) $added++;
            }
        } elseif ($type === 'staff') {
            $request->validate(['staff_ids' => 'required|array']);
            foreach ($request->staff_ids as $staffId) {
                $reg = TourRegistration::firstOrCreate(
                    ['tour_id' => $tour->id, 'staff_id' => $staffId],
                    array_merge($base, ['staff_id' => $staffId])
                );
                if ($reg->wasRecentlyCreated) $added++;
            }
        }

        return back()->with('success', "$added participant(s) registered.");
    }

    public function updateRegistration(Request $request, $id, $regId)
    {
        $reg = TourRegistration::where('tour_id', $id)->findOrFail($regId);
        $data = $request->validate([
            'fee'                  => 'required|numeric|min:0',
            'paid_amount'          => 'nullable|numeric|min:0|lte:fee',
            'notes'                => 'nullable|string',
            'tour_fee_category_id' => ['nullable', \Illuminate\Validation\Rule::exists('tour_fee_categories', 'id')->where('tour_id', $id)],
        ], ['paid_amount.lte' => 'Paid amount cannot exceed the fee.']);
        $data['paid_amount'] = (float) ($data['paid_amount'] ?? 0);
        // Derive the status from the amounts so they can't disagree.
        $data['payment_status'] = $data['paid_amount'] <= 0 ? 'unpaid'
            : ($data['paid_amount'] >= (float) $data['fee'] ? 'paid' : 'partial');
        $data['is_approved'] = $request->boolean('is_approved');
        $reg->update($data);
        return back()->with('success', 'Registration updated.');
    }

    public function destroyRegistration($id, $regId)
    {
        TourRegistration::where('tour_id', $id)->findOrFail($regId)->delete();
        return back()->with('success', 'Participant removed from tour.');
    }

    // ── Expenses ───────────────────────────────────────────────────────────

    public function storeExpense(Request $request, $id)
    {
        SchoolTour::findOrFail($id);
        $request->validate([
            'category'     => 'required|string|max:50',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
        ]);
        TourExpense::create(array_merge(
            $request->only(['category','description','amount','expense_date','paid_by','notes']),
            ['tour_id' => $id]
        ));
        return back()->with('success', 'Expense recorded.');
    }

    public function destroyExpense($id, $expId)
    {
        TourExpense::where('tour_id', $id)->findOrFail($expId)->delete();
        return back()->with('success', 'Expense deleted.');
    }

    // ── Badges ─────────────────────────────────────────────────────────────

    public function badge($id, $participantId)
    {
        $tour         = SchoolTour::findOrFail($id);
        // $participantId is the registration id (matching on student/teacher/staff
        // ids too could return someone else's registration).
        $registration = TourRegistration::where('tour_id', $id)
            ->whereKey($participantId)
            ->with(['student.classe','student.section','teacher','staff'])
            ->firstOrFail();
        return view('school-management::academic.tours.badge', compact('tour', 'registration'));
    }

    public function allBadges($id)
    {
        $tour = SchoolTour::findOrFail($id);
        $registrations = TourRegistration::where('tour_id', $id)
            ->with(['student.classe','student.section','teacher','staff'])
            ->get();
        return view('school-management::academic.tours.badges', compact('tour', 'registrations'));
    }

    private function validateTour(Request $request): array
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'destination'   => 'required|string|max:255',
            'description'   => 'nullable|string',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'budget'        => 'nullable|numeric|min:0',
            'organizer'     => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'status'        => 'nullable|in:planning,approved,ongoing,completed,cancelled',
        ]);
        $data['budget'] = $data['budget'] ?? 0;
        $data['status'] = $data['status'] ?? 'planning';
        return $data;
    }
}
