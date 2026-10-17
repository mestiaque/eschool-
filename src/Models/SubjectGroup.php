<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectGroup extends Model
{
    protected $table = 'subject_groups';

    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'group_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'subject_group_id');
    }
}
