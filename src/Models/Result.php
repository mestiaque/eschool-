<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $fillable = ['student_id', 'exam_id', 'class_id', 'section_id', 'total_mark', 'obtained_mark', 'gpa', 'grade', 'position', 'section_position', 'is_published', 'note'];
    protected $casts = ['total_mark' => 'decimal:2', 'obtained_mark' => 'decimal:2', 'gpa' => 'decimal:2', 'is_published' => 'boolean'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
