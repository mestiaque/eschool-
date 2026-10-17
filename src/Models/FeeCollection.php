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
}
