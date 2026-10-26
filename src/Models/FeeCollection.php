<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeCollection extends Model
{
    protected $fillable = [
        'student_id',
        'fee_type_id',
        'academic_year_id',
        'class_id',
        'month',
        'year',
        'amount',
        'fine',
        'discount',
        'paid_amount',
        'due_amount',
        'due_date',
        'payment_date',
        'status',
        'invoice_no',
        'payment_method',
        'transaction_id',
        'note',
        'collected_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fine' => 'decimal:2',
        'discount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** Month number 1–12, whether it was saved as "3" or as "March". */
    public function getMonthNumberAttribute(): ?int
    {
        $m = $this->month;
        if ($m === null || $m === '') return null;
        if (is_numeric($m)) return ((int) $m >= 1 && (int) $m <= 12) ? (int) $m : null;
        $t = strtotime("1 {$m} 2000");
        return $t ? (int) date('n', $t) : null;
    }

    /** Month for display, e.g. "March" ($format 'F') or "Mar 2026" ('M Y'). */
    public function monthLabel(string $format = 'F'): ?string
    {
        $n = $this->month_number;
        return $n ? date($format, mktime(0, 0, 0, $n, 1, (int) ($this->year ?: date('Y')))) : null;
    }
}
