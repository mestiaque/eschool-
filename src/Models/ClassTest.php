<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassTest extends Model
{
    protected $table = 'class_tests';

    protected $fillable = [
        'title', 'class_id', 'subject_id', 'teacher_id', 'academic_year_id',
        'test_date', 'total_marks', 'pass_marks', 'duration', 'description',
        'file', 'status', 'is_active'
    ];

    protected $casts = [
        'test_date' => 'date',
        'total_marks' => 'decimal:2',
        'pass_marks' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class)->withTrashed();
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(ClassTestResult::class);
    }
}
