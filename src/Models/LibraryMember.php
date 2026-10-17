<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryMember extends Model
{
    protected $fillable = ['member_type', 'student_id', 'teacher_id', 'membership_date', 'expire_date', 'is_active'];
    protected $casts = ['membership_date' => 'date', 'expire_date' => 'date', 'is_active' => 'boolean'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class)->withTrashed();
    }
}
