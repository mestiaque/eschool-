<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['name', 'grade_point', 'mark_from', 'mark_to', 'description', 'is_active'];
    protected $casts = ['grade_point' => 'decimal:2', 'mark_from' => 'decimal:2', 'mark_to' => 'decimal:2', 'is_active' => 'boolean'];
}
