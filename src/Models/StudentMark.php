<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMark extends Model
{
    protected $fillable = ['student_id', 'exam_id', 'subject_id', 'class_id', 'written', 'mcq', 'practical', 'ca', 'total_mark', 'grade_id', 'grade_point', 'is_absent', 'note'];
    protected $casts = ['written' => 'decimal:2', 'mcq' => 'decimal:2', 'practical' => 'decimal:2', 'ca' => 'decimal:2', 'total_mark' => 'decimal:2', 'grade_point' => 'decimal:2', 'is_absent' => 'boolean'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /** Letter grade: the configured grade, or the default scale's letter for this grade point. */
    public function getGradeNameAttribute(): ?string
    {
        if ($this->grade_id && $this->grade) return $this->grade->name;
        if ($this->grade_point === null) return null;
        $gp = (float) $this->grade_point;
        foreach ([5 => 'A+', 4 => 'A', '3.5' => 'A-', 3 => 'B', 2 => 'C', 1 => 'D'] as $min => $name) {
            if ($gp >= (float) $min) return $name;
        }
        return 'F';
    }
}
