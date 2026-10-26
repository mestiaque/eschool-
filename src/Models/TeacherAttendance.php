<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendance extends Model
{
    protected $fillable = ['teacher_id', 'date', 'status', 'in_time', 'out_time', 'note'];
    protected $casts = ['date' => 'date'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class)->withTrashed();
    }
}
