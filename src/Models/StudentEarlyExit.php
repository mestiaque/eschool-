<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEarlyExit extends Model
{
    protected $fillable = ['student_id', 'exit_date', 'exit_time', 'reason', 'picked_up_by', 'status', 'approved_by', 'note'];
    protected $casts = ['exit_date' => 'date'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\ME\Models\User::class, 'approved_by');
    }
}
