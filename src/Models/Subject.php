<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'type',
        'group_id',
        'code',
        'class_id',
        'teacher_id',
        'pass_mark',
        'full_mark',
        'credit_hour',
        'is_active'
    ];

    protected $casts = [
        'pass_mark' => 'decimal:2',
        'full_mark' => 'decimal:2',
        'credit_hour' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function subjectGroup(): BelongsTo
    {
        return $this->belongsTo(SubjectGroup::class, 'group_id');
    }

    public function classRoutines(): HasMany
    {
        return $this->hasMany(ClassRoutine::class);
    }
}
