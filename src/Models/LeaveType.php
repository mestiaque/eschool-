<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    public const APPLIES_TO = ['teacher' => 'Teachers', 'student' => 'Students', 'both' => 'Teachers & Students'];

    protected $fillable = ['name', 'days', 'applies_to', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    /** Active types a teacher or a student can take. */
    public function scopeFor($query, string $who)
    {
        return $query->where('is_active', true)->whereIn('applies_to', [$who, 'both'])->orderBy('name');
    }

    public function appliesTo(string $who): bool
    {
        return in_array($this->applies_to, [$who, 'both'], true);
    }
}
