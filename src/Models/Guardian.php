<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guardian extends Model
{
    protected $fillable = [
        'student_id', 'father_name', 'father_phone', 'father_nid', 'father_occupation', 'father_photo',
        'mother_name', 'mother_phone', 'mother_nid', 'mother_occupation', 'mother_photo',
        'guardian_name', 'guardian_relation', 'guardian_phone', 'guardian_nid', 'guardian_address', 'guardian_photo'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }
}
