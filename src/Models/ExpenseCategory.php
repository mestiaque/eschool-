<?php

namespace ME\SchoolManagement\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = ['name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
