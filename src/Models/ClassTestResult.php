<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassTestResult extends Model
{
    protected $table = 'class_test_results';

    protected $fillable = [
        'class_test_id', 'student_id', 'obtained_marks', 'grade', 'status', 'remarks',
    ];

    protected $casts = [
        'obtained_marks' => 'decimal:2',
    ];

    public function classTest(): BelongsTo
    {
        return $this->belongsTo(ClassTest::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }
}
