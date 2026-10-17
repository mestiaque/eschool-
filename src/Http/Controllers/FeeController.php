<?php

namespace ME\SchoolManagement\Http\Controllers;
use ME\Http\Controllers\Controller;
use ME\SchoolManagement\Http\Controllers\Concerns\GuardsDeletes;

use Illuminate\Http\Request;
use ME\SchoolManagement\Models\FeeType;
use ME\SchoolManagement\Models\FeeStructure;
use ME\SchoolManagement\Models\FeeCollection;
use ME\SchoolManagement\Models\FeePayment;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\Classe;
use ME\SchoolManagement\Models\AcademicYear;
use ME\SchoolManagement\Models\Invoice;
use ME\SchoolManagement\Models\StudentDiscount;
use ME\SchoolManagement\Services\SchoolNotifier;

class FeeController extends Controller
{
    use GuardsDeletes;

    // Fee Types
    public function feeTypeIndex()
    {
        $feeTypes = FeeType::orderBy('id', 'desc')->paginate(20);
        return view('school-management::fees.types', compact('feeTypes'));
    }

    public function feeTypeStore(Request $request)
    {
        FeeType::create($this->validateFeeType($request));
        return back()->with('success', 'Fee type created');
    }

    public function feeTypeUpdate(Request $request, $id)
    {
        FeeType::findOrFail($id)->update($this->validateFeeType($request));
        return back()->with('success', 'Fee type updated');
    }

    public function feeTypeDestroy($id)
    {
        $type = FeeType::findOrFail($id);
        return $this->deleteUnlessUsed($type, [
            'fee invoices'   => $this->uses('fee_collections', 'fee_type_id', $id),
            'fee structures' => $this->uses('fee_structures', 'fee_type_id', $id),
            'discounts'      => $this->uses('student_discounts', 'fee_type_id', $id),
        ], $type->name, 'Fee type deleted');
    }

    // Fee Structure
    public function feeStructureIndex()
    {
        $structures = FeeStructure::with(['feeType', 'class', 'academicYear'])->get();
        $feeTypes = FeeType::where('is_active', true)->get();
        $classes = Classe::where('is_active', true)->get();
        $academicYears = AcademicYear::all();
        return view('school-management::fees.structure', compact('structures', 'feeTypes', 'classes', 'academicYears'));
    }

    public function feeStructureStore(Request $request)
    {
        $data = $request->validate([
            'fee_type_id'      => 'required|exists:fee_types,id',
            'class_id'         => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'amount'           => 'required|numeric|min:0',
        ]);
        FeeStructure::updateOrCreate(
            collect($data)->only(['fee_type_id', 'class_id', 'academic_year_id'])->all(),
            ['amount' => $data['amount'], 'is_active' => true]
        );
        return back()->with('success', 'Fee structure created');
    }

    public function feeStructureDestroy($id)
    {
        $structure = FeeStructure::findOrFail($id);
        $this->logDelete($structure, 'Fee structure #' . $id, fn () => $structure->delete());
        return back()->with('success', 'Fee structure deleted');
    }

    // Fee Collection
    public function collectionIndex(Request $request)
    {
        $query = FeeCollection::with(['student.classe', 'feeType', 'class']);
        if ($request->class_id) $query->where('class_id', $request->class_id);
        if ($request->status)   $query->where('status', $request->status);
        if ($request->month)    $query->where('month', $request->month);
        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('invoice_no', 'like', "%$s%")
                ->orWhereHas('student', fn($q2) => $q2->where('first_name', 'like', "%$s%")
                    ->orWhere('last_name', 'like', "%$s%")
                    ->orWhere('student_id', 'like', "%$s%")));
        }

        $collections = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();
        $classes     = Classe::where('is_active', true)->get();
        return view('school-management::fees.collection', compact('collections', 'classes'));
    }

    public function createCollection()
    {
        $feeTypes      = FeeType::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        return view('school-management::fees.create-collection', compact('feeTypes', 'academicYears'));
    }

    public function storeCollection(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|exists:students,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'amount'      => 'required|numeric|min:0',
        ]);

        $amount     = (float) $request->amount;
        $fine       = (float) ($request->fine ?? 0);
        // Blank discount = apply the student's saved discount for this fee type.
        $discount   = $request->filled('discount')
            ? (float) $request->discount
            : $this->discountFor((int) $request->student_id, (int) $request->fee_type_id, $amount);
        $paidAmount = (float) ($request->paid_amount ?? 0);
        $dueAmount  = max(0, $amount + $fine - $discount - $paidAmount);

        if ($paidAmount >= $amount + $fine - $discount) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        } else {
            $status = 'unpaid';
        }

        // Pull class_id from the student
        $student = Student::findOrFail($request->student_id);

        $collection = FeeCollection::create([
            'student_id'        => $request->student_id,
            'fee_type_id'       => $request->fee_type_id,
            'class_id'          => $student->class_id,
            'academic_year_id'  => $request->academic_year_id ?: $student->academic_year_id ?: $this->currentAcademicYearId(),
            'month'             => $request->month ?: null,
            'year'              => $request->year ?: date('Y'),
            'amount'            => $amount,
            'fine'              => $fine,
            'discount'          => $discount,
            'paid_amount'       => $paidAmount,
            'due_amount'        => $dueAmount,
            'due_date'          => $request->due_date ?: null,
            'payment_date'      => $paidAmount > 0 ? ($request->payment_date ?? date('Y-m-d')) : null,
            'payment_method'    => $request->payment_method ?: null,
            'status'            => $status,
            'invoice_no'        => $this->newInvoiceNo(),
            'note'              => $request->note,
        ]);

        // Record the up-front payment so it shows in payment history.
        if ($paidAmount > 0) {
            FeePayment::create([
                'fee_collection_id' => $collection->id,
                'amount'            => $paidAmount,
                'date'              => $collection->payment_date ?? date('Y-m-d'),
                'payment_method'    => $collection->payment_method,
            ]);
        }

        if ($paidAmount > 0) SchoolNotifier::paymentReceipt($collection->load('student.guardian', 'feeType'), $paidAmount);

        return redirect()->route('fees.collection')->with('success', 'Fee invoice created successfully.');
    }

    public function payFee(Request $request, $id)
    {
        $collection = FeeCollection::findOrFail($id);

        $payable = $collection->amount + $collection->fine - $collection->discount;
        $request->validate([
            'paid_amount'  => 'required|numeric|min:0.01|max:' . max(0.01, $payable - $collection->paid_amount),
            'payment_date' => 'nullable|date',
        ], ['paid_amount.max' => 'Payment cannot exceed the due amount.']);

        $paid = $collection->paid_amount + $request->paid_amount;
        $due = $collection->amount + $collection->fine - $collection->discount - $paid;

        $status = 'unpaid';
        if ($paid >= $collection->amount + $collection->fine - $collection->discount) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        }

        $log = function_exists('me_change_log')
            ? me_change_log("Fee payment {$request->paid_amount} on {$collection->invoice_no}", 'fee.payment')->watch($collection) : null;
        $collection->update([
            'paid_amount' => $paid,
            'due_amount' => $due > 0 ? $due : 0,
            'status' => $status,
            'payment_date' => $request->payment_date ?? date('Y-m-d')
        ]);

        FeePayment::create([
            'fee_collection_id' => $collection->id,
            'amount' => $request->paid_amount,
            'date' => $request->payment_date ?? date('Y-m-d'),
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id
        ]);

        $log?->save();
        SchoolNotifier::paymentReceipt($collection->fresh(['student.guardian', 'feeType']), (float) $request->paid_amount);

        return back()->with('success', 'Payment received successfully');
    }

    /** SMS / e-mail each guardian with a student's total unpaid fees (filters match the collection page). */
    public function sendReminders(Request $request)
    {
        $dues = FeeCollection::where('status', '!=', 'paid')->where('due_amount', '>', 0)
            ->when($request->class_id, fn ($q) => $q->where('class_id', $request->class_id))
            ->when($request->month, fn ($q) => $q->where('month', $request->month))
            ->selectRaw('student_id, SUM(due_amount) as due, COUNT(*) as invoices')
            ->groupBy('student_id')->get();

        $students = Student::with('guardian')->whereIn('id', $dues->pluck('student_id'))->where('is_active', true)->get()->keyBy('id');
        $reachable = $students->filter(fn ($st) => SchoolNotifier::guardianPhone($st) || SchoolNotifier::guardianEmail($st));
        if ($reachable->isEmpty()) {
            return back()->with('error', 'No unpaid invoices with a guardian phone or e-mail for this selection.');
        }

        SchoolNotifier::later(function () use ($dues, $reachable) {
            foreach ($dues as $row) {
                if (!$student = $reachable->get($row->student_id)) continue;
                SchoolNotifier::guardian($student,
                    "Fee reminder: {$student->full_name} has {$row->invoices} unpaid invoice(s), total due "
                    . sm_money($row->due, 2) . '. Please pay at the school office.',
                    'Fee reminder');
            }
        });

        $skipped = $students->count() - $reachable->count();
        return back()->with('success', "Reminders are being sent to {$reachable->count()} guardian(s)"
            . ($skipped ? "; {$skipped} have no phone or e-mail" : '') . '. Check the SMS log for delivery.');
    }

    // Generate Monthly Fee
    public function generateMonthlyFee(Request $request)
    {
        $request->validate([
            'class_id'         => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'fee_type_id'      => 'required|exists:fee_types,id',
            'month'            => 'required|integer|min:1|max:12',
            'due_date'         => 'nullable|date',
        ]);

        $students = Student::where('class_id', $request->class_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('is_active', true)
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No active students found for the selected class and academic year.');
        }

        $structures = FeeStructure::where('class_id', $request->class_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('fee_type_id', $request->fee_type_id)
            ->get();

        if ($structures->isEmpty()) {
            return back()->with('error', 'No fee structure defined for the selected class, year and fee type.');
        }

        $count = 0;
        foreach ($students as $student) {
            foreach ($structures as $structure) {
                // Skip if already generated for this student/month/fee_type
                $exists = FeeCollection::where('student_id', $student->id)
                    ->where('fee_type_id', $structure->fee_type_id)
                    ->where('academic_year_id', $request->academic_year_id)
                    ->where('month', $request->month)
                    ->exists();

                if (!$exists) {
                    $discount = $this->discountFor($student->id, $structure->fee_type_id, (float) $structure->amount);
                    FeeCollection::create([
                        'student_id' => $student->id,
                        'fee_type_id' => $structure->fee_type_id,
                        'academic_year_id' => $request->academic_year_id,
                        'class_id' => $request->class_id,
                        'month' => $request->month,
                        'year' => now()->year,
                        'amount' => $structure->amount,
                        'fine' => 0,
                        'discount' => $discount,
                        'paid_amount' => 0,
                        'due_amount' => max(0, $structure->amount - $discount),
                        'due_date' => $request->due_date,
                        'status' => $structure->amount - $discount <= 0 ? 'paid' : 'unpaid',
                        'invoice_no' => $this->newInvoiceNo(),
                    ]);
                    $count++;
                }
            }
        }

        return back()->with('success', "Monthly fee generated for {$count} entries.");
    }

    // Discounts
    public function discountIndex()
    {
        $discounts = StudentDiscount::with(['student.classe', 'feeType'])->orderBy('id','desc')->get();
        $feeTypes  = FeeType::where('is_active', true)->get();
        return view('school-management::fees.discounts', compact('discounts', 'feeTypes'));
    }

    public function discountStore(Request $request)
    {
        $data = $request->validate([
            'student_id'      => 'required|exists:students,id',
            'fee_type_id'     => 'nullable|exists:fee_types,id',
            'discount_amount' => 'required|numeric|min:0' . ($request->discount_type === 'percent' ? '|max:100' : ''),
            'discount_type'   => 'nullable|in:fixed,percent',
        ]);
        $data['discount_type'] = $data['discount_type'] ?? 'fixed';
        $data['is_active']     = $request->has('is_active') ? $request->boolean('is_active') : true;
        StudentDiscount::create($data);
        return back()->with('success', 'Discount applied');
    }

    public function discountDestroy($id)
    {
        $discount = StudentDiscount::findOrFail($id);
        $this->logDelete($discount, 'Discount #' . $id, fn () => $discount->delete());
        return back()->with('success', 'Discount removed');
    }

    // Monthly fee index
    public function monthlyIndex(Request $request)
    {
        $query = FeeCollection::with(['student.classe', 'feeType']);
        if ($request->class_id) $query->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
        if ($request->month) $query->where('month', $request->month);
        if ($request->status) $query->where('status', $request->status);

        $collections = $query->orderBy('id', 'desc')->paginate(20);
        $classes = Classe::where('is_active', true)->get();
        $feeTypes = FeeType::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        return view('school-management::fees.monthly', compact('collections', 'classes', 'feeTypes', 'academicYears'));
    }

    // Invoice index
    public function invoiceIndex(Request $request)
    {
        $query = FeeCollection::with(['student', 'feeType']);
        if ($request->status) $query->where('status', $request->status);
        if ($request->from_date) $query->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('created_at', '<=', $request->to_date);
        if ($request->search) {
            $s = $request->search;
            // Grouped so the OR doesn't bypass the status/date filters above.
            $query->where(fn($q) => $q->where('invoice_no', 'like', "%$s%")
                ->orWhereHas('student', fn($q2) => $q2->where('first_name', 'like', "%$s%")
                    ->orWhere('last_name', 'like', "%$s%")));
        }

        $collections = $query->orderBy('id', 'desc')->paginate(20);
        return view('school-management::fees.invoice', compact('collections'));
    }

    // Payment History
    public function paymentHistory(Request $request, $student_id = null)
    {
        $payments = FeePayment::with(['feeCollection.student'])->orderBy('id', 'desc');

        if ($student_id) {
            $payments->whereHas('feeCollection', function ($q) use ($student_id) {
                $q->where('student_id', $student_id);
            });
        }

        if ($request->invoice_no) {
            $payments->whereHas('feeCollection', function ($q) use ($request) {
                $q->where('invoice_no', 'like', '%' . $request->invoice_no . '%');
            });
        }

        if ($request->from_date) {
            $payments->whereDate('date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $payments->whereDate('date', '<=', $request->to_date);
        }

        $payments = $payments->paginate(25)->withQueryString();

        return view('school-management::fees.payment-history', compact('payments'));
    }

    // Reports
    public function collectionReport(Request $request)
    {
        $query = FeeCollection::with(['student', 'feeType']);
        if ($request->from_date) $query->whereDate('payment_date', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('payment_date', '<=', $request->to_date);

        $collections = $query->get();
        $total = $collections->sum('paid_amount');

        return view('school-management::fees.report', compact('collections', 'total'));
    }

    public function collectionReportPrint(Request $request)
    {
        $query = FeeCollection::with(['student', 'feeType']);
        if ($request->from_date) $query->whereDate('payment_date', '>=', $request->from_date);
        if ($request->to_date) $query->whereDate('payment_date', '<=', $request->to_date);

        $collections = $query->get();
        $total = $collections->sum('paid_amount');

        return view('school-management::fees.report-print', compact('collections', 'total'));
    }

    private function validateFeeType(Request $request): array
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|string|max:255',
            'amount'      => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        $data['amount']    = $data['amount'] ?? 0;
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        return $data;
    }

    /** Unique invoice number; invoice_no has a unique index. */
    private function newInvoiceNo(): string
    {
        do {
            $no = 'INV-' . date('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (FeeCollection::where('invoice_no', $no)->exists());
        return $no;
    }

    /** Active discount for a student: fee-type-specific first, then a general one. */
    private function discountFor(int $studentId, int $feeTypeId, float $amount): float
    {
        $d = StudentDiscount::where('student_id', $studentId)
            ->where('is_active', true)
            ->where(fn($q) => $q->where('fee_type_id', $feeTypeId)->orWhereNull('fee_type_id'))
            ->orderByRaw('fee_type_id IS NULL')
            ->first();
        if (!$d) return 0;
        $value = $d->discount_type === 'percent' ? $amount * $d->discount_amount / 100 : (float) $d->discount_amount;
        return round(min($value, $amount), 2);
    }

    private function currentAcademicYearId()
    {
        return AcademicYear::where('is_current', true)->value('id')
            ?? AcademicYear::orderBy('id', 'desc')->value('id');
    }
}
