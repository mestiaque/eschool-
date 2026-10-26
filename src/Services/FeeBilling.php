<?php

namespace ME\SchoolManagement\Services;

use Illuminate\Support\Str;
use ME\SchoolManagement\Models\FeeCollection;
use ME\SchoolManagement\Models\FeeType;
use ME\SchoolManagement\Models\Student;
use ME\SchoolManagement\Models\StudentDiscount;

/** Shared invoice helpers: numbering, discounts and transport billing. */
class FeeBilling
{
    /** Unique invoice number; invoice_no has a unique index. */
    public static function newInvoiceNo(): string
    {
        do {
            $no = 'INV-' . date('ymd') . '-' . strtoupper(Str::random(6));
        } while (FeeCollection::where('invoice_no', $no)->exists());
        return $no;
    }

    /** Active discount for a student: fee-type-specific first, then a general one. */
    public static function discountFor(int $studentId, int $feeTypeId, float $amount): float
    {
        $d = StudentDiscount::where('student_id', $studentId)
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('fee_type_id', $feeTypeId)->orWhereNull('fee_type_id'))
            ->orderByRaw('fee_type_id IS NULL')
            ->first();
        if (!$d) return 0;
        $value = $d->discount_type === 'percent' ? $amount * $d->discount_amount / 100 : (float) $d->discount_amount;
        return round(min($value, $amount), 2);
    }

    /** The fee type used for transport invoices (created on first use). */
    public static function transportFeeType(): FeeType
    {
        return FeeType::where('type', 'transport')->where('is_active', true)->orderBy('id')->first()
            ?? FeeType::create(['name' => 'Transport Fee', 'type' => 'transport', 'amount' => 0, 'is_active' => true]);
    }

    /**
     * Bill one month of transport for students on a route: route fee plus their
     * stop's extra fee, less any discount. Skips students already billed for that
     * month and academic year. Returns how many invoices were created.
     */
    public static function generateTransport(int $month, ?int $classId = null, ?int $academicYearId = null,
                                             ?string $dueDate = null, ?FeeType $feeType = null): int
    {
        $feeType ??= self::transportFeeType();
        $students = Student::with(['transportRoute', 'routeStop'])
            ->where('is_active', true)->whereNotNull('transport_route_id')
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->get();

        $count = 0;
        foreach ($students as $student) {
            $amount = $student->transport_fee;
            if ($amount <= 0 || !$student->academic_year_id) continue;
            $exists = FeeCollection::where('student_id', $student->id)->where('fee_type_id', $feeType->id)
                ->where('academic_year_id', $student->academic_year_id)->where('month', (string) $month)->exists();
            if ($exists) continue;

            $discount = self::discountFor($student->id, $feeType->id, $amount);
            FeeCollection::create([
                'student_id'       => $student->id,
                'fee_type_id'      => $feeType->id,
                'academic_year_id' => $student->academic_year_id,
                'class_id'         => $student->class_id,
                'month'            => (string) $month,
                'year'             => now()->year,
                'amount'           => $amount,
                'fine'             => 0,
                'discount'         => $discount,
                'paid_amount'      => 0,
                'due_amount'       => max(0, $amount - $discount),
                'due_date'         => $dueDate,
                'status'           => $amount - $discount <= 0 ? 'paid' : 'unpaid',
                'invoice_no'       => self::newInvoiceNo(),
                'note'             => 'Transport: ' . $student->transportRoute->route_name
                    . ($student->routeStop ? ' / ' . $student->routeStop->stop_name : ''),
            ]);
            $count++;
        }
        return $count;
    }
}
