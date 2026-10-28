<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSchedule extends Model
{
    /** Mark parts that can carry their own full and pass mark (Bangladesh: CQ, MCQ, practical pass separately). */
    public const PARTS = ['written' => 'Written (CQ)', 'mcq' => 'MCQ', 'practical' => 'Practical'];

    protected $fillable = ['exam_id', 'class_id', 'subject_id', 'date', 'start_time', 'end_time', 'room', 'full_mark', 'pass_mark',
        'written_full', 'written_pass', 'mcq_full', 'mcq_pass', 'practical_full', 'practical_pass'];
    protected $casts = ['date' => 'date', 'full_mark' => 'decimal:2', 'pass_mark' => 'decimal:2'];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
