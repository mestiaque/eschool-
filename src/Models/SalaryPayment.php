<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryPayment extends Model
{
    protected $fillable = ['staff_id', 'staff_type', 'basic_salary', 'allowance', 'deduction', 'gross_salary', 'net_salary', 'month', 'year', 'payment_date', 'payment_method', 'transaction_id', 'note'];
    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'deduction' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date'
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'staff_id')->withTrashed();
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id')->withTrashed();
    }

    public function expense(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Expense::class);
    }

    /** Name of the teacher or staff member paid (staff_id points at either table). */
    public function getPayeeNameAttribute(): string
    {
        $person = $this->staff_type === 'teacher' ? $this->teacher : $this->staffMember;
        return $person ? trim($person->first_name . ' ' . $person->last_name) : ucfirst($this->staff_type) . ' #' . $this->staff_id;
    }

    public function getMonthNameAttribute(): string
    {
        return is_numeric($this->month) ? date('F', mktime(0, 0, 0, (int) $this->month, 1)) : (string) $this->month;
    }
}
