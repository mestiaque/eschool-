<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLeave extends Model
{
    protected $fillable = ['student_id', 'leave_type_id', 'start_date', 'end_date', 'reason', 'status', 'approved_by', 'note'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];
    protected $appends = ['days'];

    public function getDaysAttribute()
    {
        if (!$this->start_date || !$this->end_date) return 0;
        return $this->start_date->diffInDays($this->end_date) + 1;
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
