<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'teacher_id', 'first_name', 'last_name', 'gender', 'date_of_birth',
        'religion', 'blood_group', 'nationality', 'photo', 'email', 'phone',
        'present_address', 'permanent_address', 'nid', 'joining_date',
        'designation', 'qualification', 'experience', 'salary', 'bank_name',
        'bank_account_no', 'bank_branch', 'facebook_url', 'linkedin_url',
        'twitter_url', 'is_active'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'salary' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function subjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(TeacherLeave::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
