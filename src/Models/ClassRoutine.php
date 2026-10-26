<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassRoutine extends Model
{
    protected $fillable = ['class_id', 'section_id', 'subject_id', 'teacher_id', 'title', 'day', 'start_time', 'end_time', 'room_id'];

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class)->withTrashed();
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /** Bangladesh school week order. */
    public const WEEK = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    /** Order by day of the school week (Saturday first) instead of alphabetically. */
    public function scopeOrderByWeekday($query)
    {
        $cases = collect(self::WEEK)->map(fn ($d, $i) => "WHEN '{$d}' THEN {$i}")->implode(' ');
        return $query->orderByRaw("CASE day {$cases} ELSE 7 END");
    }
}
