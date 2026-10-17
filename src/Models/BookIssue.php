<?php

namespace ME\SchoolManagement\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookIssue extends Model
{
    protected $fillable = [
        'book_id', 'book_copy_id', 'member_type',
        'student_id', 'teacher_id', 'staff_id',
        'issue_date', 'due_date', 'return_date',
        'status', 'fine_amount', 'fine_paid', 'fine_status',
        'is_lost', 'note', 'return_note',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'return_date' => 'date',
        'fine_amount' => 'decimal:2',
        'fine_paid'   => 'decimal:2',
        'is_lost'     => 'boolean',
    ];

    public function book(): BelongsTo    { return $this->belongsTo(Book::class); }
    public function copy(): BelongsTo    { return $this->belongsTo(BookCopy::class, 'book_copy_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class)->withTrashed(); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class)->withTrashed(); }
    public function staff(): BelongsTo   { return $this->belongsTo(Staff::class)->withTrashed(); }

    public function getMemberNameAttribute(): string
    {
        return match($this->member_type) {
            'teacher' => trim(optional($this->teacher)->first_name . ' ' . optional($this->teacher)->last_name),
            'staff'   => trim(optional($this->staff)->first_name . ' ' . optional($this->staff)->last_name),
            default   => trim(optional($this->student)->first_name . ' ' . optional($this->student)->last_name),
        };
    }

    public function getOverdueDaysAttribute(): int
    {
        if ($this->status !== 'issued') return 0;
        $due   = $this->due_date;
        $today = Carbon::today();
        return $due && $today->gt($due) ? (int) $today->diffInDays($due) : 0;
    }

    public function getCalculatedFineAttribute(): float
    {
        if ((float)$this->fine_amount > 0) return (float) $this->fine_amount;
        $days = $this->overdue_days;
        if ($days <= 0) return 0.0;
        return LibrarySetting::get()->calculateFine($days);
    }

    public function getFineBalanceAttribute(): float
    {
        return max(0.0, (float)$this->fine_amount - (float)$this->fine_paid);
    }
}
