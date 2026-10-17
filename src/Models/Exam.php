<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = ['name', 'exam_type_id', 'academic_year_id', 'start_date', 'end_date', 'notice', 'is_published', 'is_active'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'is_published' => 'boolean', 'is_active' => 'boolean'];

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
