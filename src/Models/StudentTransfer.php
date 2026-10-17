<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransfer extends Model
{
    protected $fillable = ['student_id', 'from_school', 'to_school', 'transfer_date', 'reason', 'certificate_file', 'status'];
    protected $casts = ['transfer_date' => 'date'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }
}
