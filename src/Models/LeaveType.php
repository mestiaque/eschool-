<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = ['name', 'days', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
