<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPromotion extends Model
{
    protected $fillable = ['student_id', 'from_class_id', 'to_class_id', 'from_section_id', 'to_section_id', 'from_academic_year_id', 'to_academic_year_id', 'from_roll', 'to_roll', 'is_promoted'];

    protected $casts = ['is_promoted' => 'boolean'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'from_class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'to_class_id');
    }

    public function fromSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'from_section_id');
    }

    public function toSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'to_section_id');
    }

    public function fromAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'from_academic_year_id');
    }

    public function toAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'to_academic_year_id');
    }
}
