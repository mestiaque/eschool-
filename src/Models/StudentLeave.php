<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLeave extends Model
{
    protected $fillable = ['student_id', 'leave_type_id', 'start_date', 'end_date', 'reason', 'status', 'approved_by', 'note'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];
    protected $appends = ['days'];

    /** School days, as the leave limits count them. */
    public function getDaysAttribute(): int
    {
        return \ME\SchoolManagement\Services\LeavePolicy::schoolDays($this);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\ME\Models\User::class, 'approved_by');
    }
}
